# DogAPI

API REST développée avec Symfony et API Platform. Elle récupère des données sur les chiens depuis [Dog API v2](https://dogapi.dog/) et les stocke dans une base SQLite.

## Fonctionnalités

- Gestion des races, groupes de races, anecdotes et traits de tempérament
- Documentation interactive et tests des routes avec Swagger
- Import des données depuis Dog API avec la commande `app:import-dogs`

## Technologies

- PHP 8.4 ou supérieur
- Symfony 8.1
- API Platform 5
- Doctrine ORM
- SQLite

## Installation

```bash
git clone https://github.com/carls-xyz/DogAPI.git
cd DogAPI
composer install
symfony console doctrine:migrations:migrate
