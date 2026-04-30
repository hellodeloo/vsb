# VSB

Setup rapide pour lancer le site en local avec Docker.

## Quick start

```bash
cp .env.example .env
docker compose up -d
```

Acces:
- WordPress: http://localhost:8080
- phpMyAdmin: http://localhost:8081

## Commandes utiles

```bash
docker compose ps
docker compose logs -f
docker compose stop
docker compose down
```

## Guide complet

Voir le guide detaille: [README-local.md](README-local.md)
