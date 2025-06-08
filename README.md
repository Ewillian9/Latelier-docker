# L'atelier - Site vitrine pour des œuvres d'art maison

## Technologies utilisées
- **Docker/Caddy**
- **PHP8/PostSQL17/Symfony7**
- **TailwindCSS4/Twig/Turbo/Mercure**

## Fonctionnalités (complémentaires)

### Administration
- Modération des commentaires ---OK
- et des utilisateurs avec strikes

### Artists
- Gestion de ses œuvres (ajout, modification, suppression) ---OK
- Ajout de titre, images, description et mots-clés ---OK
- Generation par IA de titre, description, mots-clés ---LATER (Gemma 3 14/27b)

### Utilisateurs (inscription requise)
- Création de compte avec confirmation par email ---OK
- Communication avec l'artiste via une messagerie instantanée ---PARTIAL
- Commande d'une œuvre et suivi du statut (validation et avancement via la messagerie instantanée) ---SOON
- Notification par email une fois l'œuvre terminée
- Commentaires instantanés sous les œuvres avec edition et suppression ---OK

### Visiteurs (sans inscription)
- Consultation des œuvres ---OK
- Recherche par titre, description et mots-clés ---OK
- Mode clair/sombre ---OK
- Support multilingues ---OK

## Getting Started

1. If not already done, [install Docker Compose](https://docs.docker.com/compose/install/) (v2.10+)
2. Run `docker compose build --pull --no-cache` to build fresh images
3. Run `docker compose up --wait` to set up and start a fresh Symfony project
4. Run docker compose exec php bin/console tailwind:build --watch
6. Run docker compose exec php bin/console doctrine:database:drop --force
        docker compose exec php bin/console doctrine:database:create
        docker compose exec php bin/console doctrine:migrations:migrate --no-interaction
        docker compose exec php bin/console doctrine:fixtures:load --no-interaction
4. Open `https://localhost` in your favorite web browser and [accept the auto-generated TLS certificate](https://stackoverflow.com/a/15076602/1352334)
5. Run `docker compose down --remove-orphans` to stop the Docker containers.

## Features

* Production, development and CI ready
* Just 1 service by default
* Blazing-fast performance thanks to [the worker mode of FrankenPHP](https://github.com/dunglas/frankenphp/blob/main/docs/worker.md) (automatically enabled in prod mode)
* [Installation of extra Docker Compose services](docs/extra-services.md) with Symfony Flex
* Automatic HTTPS (in dev and prod)
* HTTP/3 and [Early Hints](https://symfony.com/blog/new-in-symfony-6-3-early-hints) support
* Real-time messaging thanks to a built-in [Mercure hub](https://symfony.com/doc/current/mercure.html)

## License

Symfony Docker is available under the MIT License.

## Credits

Created by [Kévin Dunglas](https://dunglas.dev), co-maintained by [Maxime Helias](https://twitter.com/maxhelias) and sponsored by [Les-Tilleuls.coop](https://les-tilleuls.coop).
