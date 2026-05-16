#!/usr/bin/env bash
# sync-local-to-prod.sh
# Produit un dump de la base locale avec les URLs prod, pret a importer
# manuellement via phpMyAdmin OVH.
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
cd "$PROJECT_ROOT"

# ---------- Chargement config ----------
load_env_file() {
  local env_file="$1"
  [[ -f "$env_file" ]] || return 0
  local keys='MYSQL_DATABASE|MYSQL_ROOT_PASSWORD|PROD_URL|LOCAL_URL'
  while IFS='=' read -r key value; do
    [[ -z "$key" || "$key" == \#* ]] && continue
    if [[ -z "${!key+x}" ]]; then export "$key=$value"; fi
  done < <(grep -E "^($keys)=" "$env_file" || true)
}

load_env_file "$PROJECT_ROOT/.env.sync"
load_env_file "$PROJECT_ROOT/.env"

# ---------- Valeurs par defaut ----------
: "${LOCAL_DUMP_DIR:=./.backups}"
: "${PROD_URL:=https://vivre-saint-brieuc.bzh}"
: "${LOCAL_URL:=http://localhost:8080}"
: "${MYSQL_ROOT_PASSWORD:=root_password_change_me}"
: "${MYSQL_DATABASE:=wordpress_local}"

# ---------- Prerequis ----------
command -v docker >/dev/null 2>&1 || { echo "Erreur: docker est requis" >&2; exit 1; }

docker compose ps --quiet db 2>/dev/null | grep -q . || {
  echo "Erreur: les conteneurs Docker ne sont pas demarres. Lancer: docker compose up -d" >&2
  exit 1
}

mkdir -p "$LOCAL_DUMP_DIR"

DUMP_NAME="to-prod-$(date +%F-%H%M%S)"
DUMP_FILE="$LOCAL_DUMP_DIR/$DUMP_NAME.sql.gz"

# ---------- WP-CLI dans Docker ----------
ensure_wpcli() {
  docker compose exec -T wordpress php -r '
    if (!file_exists("/tmp/wp-cli.phar")) {
      echo "Telechargement WP-CLI... ";
      copy("https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar","/tmp/wp-cli.phar");
      echo "OK\n";
    } else { echo "WP-CLI OK\n"; }
  '
}

run_wpcli() {
  docker compose exec -T wordpress \
    php /tmp/wp-cli.phar --allow-root --path=/var/www/html "$@"
}

# ---------- Main ----------
printf '1/4 Preparation WP-CLI...\n'
ensure_wpcli

printf '2/4 Remplacement URLs locale -> prod dans la base Docker...\n'
run_wpcli search-replace "$LOCAL_URL" "$PROD_URL" \
  --all-tables-with-prefix --precise --recurse-objects --skip-columns=guid

printf '3/4 Export de la base (URLs prod incluses)...\n'
docker compose exec -T db mysqldump \
  -u root -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE" \
  2>/dev/null | gzip -c > "$DUMP_FILE"
echo "  -> $DUMP_FILE"

printf '4/4 Restauration des URLs locales dans la base Docker...\n'
run_wpcli search-replace "$PROD_URL" "$LOCAL_URL" \
  --all-tables-with-prefix --precise --recurse-objects --skip-columns=guid

echo ""
echo "Dump pret. A importer sur la prod :"
echo "  1. Aller sur https://phpmyadmin.ovh.net"
echo "  2. Selectionner votre base de donnees"
echo "  3. Onglet Import > choisir le fichier : $DUMP_FILE"
echo ""
echo "Termine."
