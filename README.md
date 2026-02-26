# CesiZen

Application web de bien-être développée avec Symfony 8, destinée aux étudiants et personnels du réseau CESI.

## Stack technique

| Couche | Technologie |
|---|---|
| Langage | PHP 8.4 |
| Framework | Symfony 8.0 |
| Base de données | MariaDB (latest) |
| Serveur web | Caddy (alpine) |
| CSS | Tailwind CSS (via AssetMapper) |
| Tests | PHPUnit 13 |
| Conteneurisation | Docker / Docker Compose |

## Prérequis

- [Docker](https://docs.docker.com/get-docker/) >= 24
- [Docker Compose](https://docs.docker.com/compose/) >= 2
- `make`

## Installation

### 1. Cloner le dépôt

```bash
git clone <url-du-repo> cesizen
cd cesizen
```

### 2. Configurer les variables d'environnement

```bash
cp .env .env.dev
```

Renseignez dans `.env.dev` les valeurs adaptées à votre environnement local :

```dotenv
APP_SECRET=<une-chaine-secrete>
DB_ROOT_PASSWORD=root
DB_NAME=cesizen
DB_USER=cesizen
DB_PASSWORD=cesizen
```

### 3. Lancer l'installation complète

```bash
make install
```

Cette commande effectue dans l'ordre :
1. Construction des images Docker
2. Démarrage des conteneurs
3. Installation des dépendances PHP (`composer install`)
4. Compilation du thème Tailwind
5. Initialisation de la base de données (drop → create → migrations)
6. Chargement des fixtures

L'application est ensuite accessible sur [http://localhost](http://localhost).

## Commandes disponibles

```bash
make help
```

### Application

| Commande | Description |
|---|---|
| `make install` | Installation complète du projet |
| `make app.database` | Réinitialise la BDD (drop, create, migrate) |
| `make app.fixtures` | Charge les fixtures de données |
| `make app.cache` | Vide le cache Symfony |
| `make app.test` | Lance la suite de tests PHPUnit |
| `make app.build.theme` | Compile le thème Tailwind |
| `make app.watch.theme` | Lance le watch Tailwind (dev) |

### Docker

| Commande | Description |
|---|---|
| `make docker.build` | Construit les images Docker |
| `make docker.up` | Démarre les conteneurs en arrière-plan |
| `make docker.down` | Arrête et supprime les conteneurs |
| `make docker.shell.php` | Ouvre un terminal dans le conteneur PHP |
| `make docker.logs.php` | Logs du conteneur PHP |
| `make docker.logs.db` | Logs du conteneur MariaDB |
| `make docker.logs.caddy` | Logs du conteneur Caddy |

## Structure du projet

```
.
├── .docker/
│   ├── caddy/          # Configuration Caddyfile
│   └── php/            # Dockerfile PHP 8.4-fpm + Xdebug
├── assets/             # JS / CSS sources
├── config/             # Configuration Symfony
├── migrations/         # Migrations Doctrine
├── public/             # Point d'entrée web
├── src/
│   ├── Controller/
│   ├── Entity/
│   └── Repository/
├── templates/          # Templates Twig
├── tests/              # Tests PHPUnit
├── translations/       # Fichiers de traduction
├── compose.yaml        # Docker Compose
├── Makefile
└── .env                # Variables d'environnement par défaut
```

## Tests

```bash
make app.test
```

Les tests utilisent PHPUnit 13 et s'exécutent dans l'environnement `test` défini dans `.env.test`.

## Développement

Pour travailler en mode watch sur le CSS :

```bash
make app.watch.theme
```

Pour accéder à la console Symfony depuis le conteneur :

```bash
make docker.shell.php
# puis : php bin/console <commande>
```
