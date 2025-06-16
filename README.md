# L'atelier - Site vitrine pour des œuvres d'art maison

## Technologies utilisées
- **Docker/Caddy**
- **PHP8/PostSQL17/Symfony7**
- **TailwindCSS4/Twig/Turbo/Mercure**

## Fonctionnalités (complémentaires)

### Administration
- Modération des commentaires ---OK
- Modération des utilisateurs avec strikes
- Modération des Oeuvres ---OK

### Artists
- Gestion de ses œuvres (ajout, modification, suppression) ---OK
- Ajout de titre, images, description et mots-clés ---OK
- Generation par IA de titre, description, mots-clés ---LATER (Gemma 3 14/27b)
- Voir ses oeuvres ---OK

### Utilisateurs (inscription requise)
- Création de compte avec confirmation par email ---OK (CHECK IT)
- Communication avec l'artiste via une messagerie instantanée ---OK
- Commande d'une œuvre et suivi du statut (en attente, en cours et terminé) ---OK
- Notification par email une fois l'œuvre terminée ---TO DO
- Commentaires instantanés sous les œuvres avec edition et suppression ---OK
- Voir ses conversation, commentaire et commandes ---OK

### Visiteurs (sans inscription)
- Consultation des œuvres ---OK
- Recherche par titre, description et mots-clés, nombre de likes ---OK (LIKES TO DO)
- Mode clair/sombre ---OK
- Support multilingues ---OK
- Acceder au profil publique d'un artist ---OK

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
