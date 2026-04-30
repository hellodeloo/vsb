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

## Import de base SQL

Importer un dump SQL dans la base locale:

```bash
docker compose exec -T db mysql -u root -p"${MYSQL_ROOT_PASSWORD:-root_password_change_me}" "${MYSQL_DATABASE:-wordpress_local}" < chemin/vers/dump.sql
```

Exemple:

```bash
docker compose exec -T db mysql -u root -proot_password_change_me wordpress_local < ./dump.sql
```

## Export de base SQL

```bash
docker compose exec -T db mysqldump -u root -p"${MYSQL_ROOT_PASSWORD:-root_password_change_me}" "${MYSQL_DATABASE:-wordpress_local}" > ./dump-local.sql
```

## Reset base locale

Supprimer la base locale completement puis redemarrer:

```bash
docker compose down -v
docker compose up -d
```

## Changement d'URL (si besoin)

Si le dump contient encore l'URL de prod, ajuster:

```bash
docker compose exec -T db mysql -u root -proot_password_change_me wordpress_local -e "UPDATE mod917_options SET option_value='http://localhost:8080' WHERE option_name IN ('siteurl','home');"
```

## Theme enfant Twenty Twenty-Five

Theme cree:

- `wp-content/themes/vivre-saint-brieuc-child/style.css`
- `wp-content/themes/vivre-saint-brieuc-child/functions.php`
- `wp-content/themes/vivre-saint-brieuc-child/theme.json`

Activer dans l'admin WordPress: Apparence > Themes > **Vivre Saint-Brieuc Child**

## Depannage rapide

- WordPress erreur DB: verifier `docker compose ps` puis les variables dans `.env`
- Port deja utilise: changer `WP_PORT`, `MYSQL_PORT` ou `PHPMYADMIN_PORT` dans `.env`
- Rebuild propre:

```bash
docker compose down -v
docker compose up -d --force-recreate
```
