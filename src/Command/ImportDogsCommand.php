<?php

namespace App\Command;

use App\Entity\Breed;
use App\Entity\BreedGroup;
use App\Entity\Fact;
use App\Entity\TemperamentTrait;
use App\Repository\BreedGroupRepository;
use App\Repository\BreedRepository;
use App\Repository\FactRepository;
use App\Repository\TemperamentTraitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

// Import ETL : Dog API (https://dogapi.dog) -> catalogue de races de chiens.
// Les règles appliquées sont celles de l'onglet « Règles ETL » du mapping.
//   symfony console app:import-dogs
#[AsCommand(
    name: 'app:import-dogs',
    description: 'Importe les groupes, races, traits et anecdotes depuis Dog API',
)]
class ImportDogsCommand
{
    private const BASE_URL = 'https://dogapi.dog/api/v2';

    // Entrées déjà rencontrées pendant CET import (pas forcément encore en base)
    private array $groupesVus = [];
    private array $racesVues = [];
    private array $traitsVus = [];
    private array $faitsVus = [];

    private array $bilan = [];
    private array $ignores = [];

    public function __construct(
        private HttpClientInterface $client,
        private EntityManagerInterface $em,
        private BreedGroupRepository $groupes,
        private BreedRepository $races,
        private TemperamentTraitRepository $traits,
        private FactRepository $faits,
    ) {
    }

    public function __invoke(SymfonyStyle $io): int
    {
        $this->bilan = array_fill_keys(['groupes', 'races', 'traits', 'faits'], ['créés' => 0, 'mis à jour' => 0, 'ignorés' => 0]);

        // E1 - EXTRAIRE : groupes d'abord (les races en dépendent), puis races, toutes pages
        foreach ($this->extrairePages(self::BASE_URL.'/groups') as $donnee) {
            $this->importerGroupe($donnee);
        }
        foreach ($this->extrairePages(self::BASE_URL.'/breeds') as $donnee) {
            $this->importerRace($donnee);
        }

        // E2 - EXTRAIRE : 5 anecdotes aléatoires par lancement
        $faits = $this->client->request('GET', self::BASE_URL.'/facts', ['query' => ['limit' => 5]])->toArray();
        foreach ($faits['data'] ?? [] as $donnee) {
            $this->importerFait($donnee);
        }

        // L - CHARGER : un seul flush à la fin (groupes, races + traits, faits)
        $this->em->flush();

        // L - bilan
        $lignes = [];
        foreach ($this->bilan as $type => $compteurs) {
            $lignes[] = [$type, ...array_values($compteurs)];
        }
        $io->table(['', 'créés', 'mis à jour', 'ignorés'], $lignes);
        foreach ($this->ignores as $raison) {
            $io->warning($raison);
        }

        return Command::SUCCESS;
    }

    // E1 - suit links.next tant qu'il est présent
    private function extrairePages(string $url): iterable
    {
        while ($url !== null) {
            $page = $this->client->request('GET', $url)->toArray();
            yield from $page['data'] ?? [];
            $url = $page['links']['next'] ?? null;
        }
    }

    private function importerGroupe(array $donnee): void
    {
        $uuid = $donnee['id'] ?? null;
        $nom = $this->normaliserNom($donnee['attributes']['name'] ?? '');

        // T1 - Filtrer
        if (!$this->uuidValide($uuid) || $nom === '') {
            $this->ignorer('groupes', "Groupe ignoré (UUID invalide ou nom vide) : ".json_encode($uuid));

            return;
        }

        // T2 - Dédoublonner : lot en cours, puis base
        $groupe = $this->groupesVus[$uuid] ?? $this->groupes->findOneBy(['dogApiId' => $uuid]);
        $this->compter('groupes', $groupe);
        $groupe ??= (new BreedGroup())->setDogApiId($uuid);

        // T5 - Tronquer
        $groupe->setName(mb_substr($nom, 0, 255));

        $this->em->persist($groupe);
        $this->groupesVus[$uuid] = $groupe;
    }

    private function importerRace(array $donnee): void
    {
        $uuid = $donnee['id'] ?? null;
        $attributs = $donnee['attributes'] ?? [];
        $nom = $this->normaliserNom($attributs['name'] ?? '');

        // T1 - Filtrer
        if (!$this->uuidValide($uuid) || $nom === '') {
            $this->ignorer('races', "Race ignorée (UUID invalide ou nom vide) : ".json_encode($uuid));

            return;
        }

        // T4 - Relier au groupe : obligatoire, sinon la race est ignorée
        $uuidGroupe = $donnee['relationships']['group']['data']['id'] ?? null;
        $groupe = $uuidGroupe === null ? null
            : ($this->groupesVus[$uuidGroupe] ?? $this->groupes->findOneBy(['dogApiId' => $uuidGroupe]));
        if ($groupe === null) {
            $this->ignorer('races', "Race « $nom » ignorée : groupe introuvable (".json_encode($uuidGroupe).')');

            return;
        }

        // T2 - Dédoublonner
        $race = $this->racesVues[$uuid] ?? $this->races->findOneBy(['dogApiId' => $uuid]);
        $this->compter('races', $race);
        $race ??= (new Breed())->setDogApiId($uuid);

        // T - mapping (T5 : nom tronqué à 255)
        $race
            ->setName(mb_substr($nom, 0, 255))
            ->setDescription($this->texteOuNull($attributs['description'] ?? null))
            ->setLifeMin($attributs['life']['min'] ?? null)
            ->setLifeMax($attributs['life']['max'] ?? null)
            ->setEnergy($attributs['traits']['energy'] ?? null)
            ->setBarking($attributs['traits']['barking'] ?? null)
            ->setTrainability($attributs['traits']['trainability'] ?? null)
            ->setBreedGroup($groupe);

        // T3 + T4 - traits normalisés (minuscules, sans vides ni doublons), liste qui peut rester vide
        $noms = [];
        foreach ($attributs['traits']['temperament'] ?? [] as $brut) {
            $trait = mb_substr(mb_strtolower($this->normaliserNom((string) $brut)), 0, 100);
            if ($trait !== '') {
                $noms[$trait] = true;
            }
        }
        foreach ($race->getTraits()->toArray() as $existant) {
            if (!isset($noms[$existant->getName()])) {
                $race->removeTrait($existant);
            }
        }
        foreach (array_keys($noms) as $trait) {
            $race->addTrait($this->trouverOuCreerTrait($trait));
        }

        $this->em->persist($race);
        $this->racesVues[$uuid] = $race;
    }

    private function importerFait(array $donnee): void
    {
        $uuid = $donnee['id'] ?? null;
        $texte = $this->texteOuNull($donnee['attributes']['body'] ?? null);

        // T1 - Filtrer
        if (!$this->uuidValide($uuid) || $texte === null) {
            $this->ignorer('faits', "Anecdote ignorée (UUID invalide ou texte vide) : ".json_encode($uuid));

            return;
        }

        // T2 - Dédoublonner
        $fait = $this->faitsVus[$uuid] ?? $this->faits->findOneBy(['dogApiId' => $uuid]);
        $this->compter('faits', $fait);
        $fait ??= (new Fact())->setDogApiId($uuid);
        $fait->setFact($texte);

        $this->em->persist($fait);
        $this->faitsVus[$uuid] = $fait;
    }

    // T4 - un trait = une seule ligne, retrouvé ou créé
    private function trouverOuCreerTrait(string $nom): TemperamentTrait
    {
        if (isset($this->traitsVus[$nom])) {
            return $this->traitsVus[$nom];
        }
        $trait = $this->traits->findOneBy(['name' => $nom]);
        if ($trait === null) {
            $trait = (new TemperamentTrait())->setName($nom);
            $this->em->persist($trait);
            ++$this->bilan['traits']['créés'];
        }

        return $this->traitsVus[$nom] = $trait;
    }

    // T3 - Normaliser : espaces retirés en début et fin
    private function normaliserNom(mixed $nom): string
    {
        return is_string($nom) ? trim($nom) : '';
    }

    private function texteOuNull(mixed $texte): ?string
    {
        $texte = is_string($texte) ? trim($texte) : '';

        return $texte === '' ? null : $texte;
    }

    private function uuidValide(mixed $uuid): bool
    {
        return is_string($uuid)
            && preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $uuid) === 1;
    }

    private function compter(string $type, ?object $existant): void
    {
        ++$this->bilan[$type][$existant === null ? 'créés' : 'mis à jour'];
    }

    private function ignorer(string $type, string $raison): void
    {
        ++$this->bilan[$type]['ignorés'];
        $this->ignores[] = $raison;
    }
}
