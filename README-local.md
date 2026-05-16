# Environnement local WordPress (Docker)

Ce projet peut tourner en local avec Docker (WordPress + MySQL + phpMyAdmin).

## Prerequis

- Docker Desktop installe et demarre
- Commandes disponibles: `docker` et `docker compose`

## Services

- WordPress: http://localhost:8080
- phpMyAdmin: http://localhost:8081
- MySQL (host): localhost:3307

## Configuration initiale

1. Copier les variables d'environnement:

```bash
cp .env.example .env
```

2. (Optionnel) Modifier `.env` pour changer ports/mots de passe.

## Demarrage

```bash
docker compose up -d
```

Verifier l'etat:

```bash
docker compose ps
```

Voir les logs:

```bash
docker compose logs -f
```

## Arret

Arreter les conteneurs:

```bash
docker compose stop
```

Arreter et supprimer les conteneurs:

```bash
docker compose down
```

Arreter, supprimer conteneurs + reseaux + volumes (attention: supprime les donnees MySQL locales):

```bash
docker compose down -v
```

## Script CLI local -> prod

Produit un dump de la base locale avec les URLs prod, pret a importer manuellement via phpMyAdmin OVH.

Configuration (une seule fois) :

```bash
cp .env.sync.example .env.sync
# .env.sync est ignore par Git, ne pas commiter
```

Lancement :

```bash
./.scripts/sync-local-to-prod.sh
```

Le script :
1. remplace les URLs locales par les URLs prod dans la base Docker (via WP-CLI)
2. exporte le dump `.sql.gz` dans `./backups/`
3. restaure les URLs locales dans la base Docker
4. affiche le chemin du fichier a importer dans phpMyAdmin OVH

Import prod -> local : exporter la base depuis phpMyAdmin OVH, placer le `.sql.gz` dans `./backups/`, puis importer manuellement :

```bash
gunzip -c ./backups/votre-dump.sql.gz | docker compose exec -T db mysql \
  -u root -p"${MYSQL_ROOT_PASSWORD}" "${MYSQL_DATABASE}"
```

Puis remplacer les URLs prod par les URLs locales :

```bash
docker compose exec -T wordpress \
  php /tmp/wp-cli.phar --allow-root --path=/var/www/html \
  search-replace "https://vivre-saint-brieuc.bzh" "http://localhost:8080" \
  --all-tables-with-prefix --precise --recurse-objects --skip-columns=guid
```


## Reset base locale

Supprimer la base locale completement puis redemarrer:

```bash
docker compose down -v
docker compose up -d
```

## Depannage rapide

- WordPress erreur DB: verifier `docker compose ps` puis les variables dans `.env`
- Port deja utilise: changer `WP_PORT`, `MYSQL_PORT` ou `PHPMYADMIN_PORT` dans `.env`
- Rebuild propre:

```bash
docker compose down -v
docker compose up -d --force-recreate
```
