# Déploiement LeKlub

Ce document décrit une stratégie de déploiement réaliste pour LeKlub dans le cadre du titre CDA.

Il ne documente pas une production déjà en place. Il explique comment préparer proprement le projet, quels secrets créer, quelles commandes exécuter, quelles limites conserver en tête et quelles vérifications faire avant une mise en ligne.

## 1. Périmètre

Le périmètre déployable concerne :

- API Symfony REST ;
- MySQL ;
- Nginx ou reverse proxy HTTP(S) ;
- serveur WebSocket ;
- stockage local des avatars ;
- SMTP de production ;
- application mobile Expo ;
- GitHub Actions comme CI.

Hors périmètre de cette étape :

- aucun serveur cloud réel ;
- aucun domaine réel ;
- aucun certificat TLS réel ;
- aucun SMTP production réel ;
- aucun stockage cloud type S3 ;
- aucun pipeline CD automatique ;
- aucune refonte Docker production.

## 2. Environnements

### Local / Dev

Usage : développement, tests iPhone Expo Go, démonstration locale.

Services :

- Docker Compose ;
- Symfony en `APP_ENV=dev` ;
- MySQL local Docker ;
- Nginx local sur `BACKEND_PORT` ;
- WebSocket local en `ws://` ;
- Mailpit pour recevoir les emails de reset password ;
- fixtures de démonstration autorisées.

Commande :

```bash
docker compose --env-file .env up -d
```

Healthcheck :

```bash
curl http://localhost:8080/api/health
```

### Test / CI

Usage : GitHub Actions.

La CI crée un `.env` temporaire avec des valeurs factices sûres, génère des clés JWT temporaires, lance MySQL en service GitHub Actions, puis exécute :

- `composer validate --strict` ;
- `php bin/console lint:container` ;
- `php bin/console doctrine:schema:validate --skip-sync` ;
- `php bin/phpunit` ;
- `npm ci` ;
- `npx tsc --noEmit`.

La CI ne déploie rien.

### Pré-production

Usage : validation avant production.

La pré-production doit être la plus proche possible de la production :

- `APP_ENV=prod` ;
- `APP_DEBUG=0` ;
- base MySQL dédiée ;
- clés JWT dédiées ;
- secrets dédiés ;
- domaine ou sous-domaine de test ;
- HTTPS obligatoire si accessible publiquement ;
- `wss://` pour WebSocket ;
- SMTP de test ou sandbox mail ;
- pas de fixtures de démonstration.

### Production Cible

Usage : mise en ligne publique.

Pré-requis :

- reverse proxy HTTPS ;
- certificats TLS ;
- base MySQL sauvegardée ;
- volume persistant pour avatars ;
- SMTP réel ;
- secrets robustes ;
- token football-data.org régénéré ;
- logs et supervision minimum ;
- stratégie de rollback.

## 3. Architecture Cible

```text
Mobile Expo / build mobile
        |
        | HTTPS / WSS
        v
Reverse proxy / Nginx public
        |
        +--> API Symfony PHP-FPM
        |
        +--> WebSocket LeKlub
        |
        +--> uploads avatars publics
        |
        v
MySQL
```

En local, Docker Compose fournit tous les services. En production, le reverse proxy doit gérer TLS et `wss://`.

## 4. Variables D'environnement

Les variables réelles doivent être stockées hors Git.

### Application

| Variable | Usage | Production |
| --- | --- | --- |
| `APP_ENV` | environnement Symfony | `prod` |
| `APP_SECRET` | secret Symfony | valeur longue aléatoire |
| `APP_DEBUG` | debug Symfony | `0` |
| `LOCK_DSN` | verrouillage Symfony RateLimiter | `flock` ou service adapté |

### Base De Données

| Variable | Usage |
| --- | --- |
| `MYSQL_DATABASE` | nom base locale Docker |
| `MYSQL_USER` | utilisateur MySQL local |
| `MYSQL_PASSWORD` | mot de passe MySQL local |
| `MYSQL_ROOT_PASSWORD` | mot de passe root local |
| `DATABASE_URL` | DSN Doctrine utilisé par Symfony |

En production, `DATABASE_URL` doit pointer vers une base dédiée, avec un utilisateur non root.

### JWT

| Variable | Usage |
| --- | --- |
| `JWT_SECRET_KEY` | chemin clé privée |
| `JWT_PUBLIC_KEY` | chemin clé publique |
| `JWT_PASSPHRASE` | passphrase clé privée |

Les fichiers `.pem` doivent être générés sur l'environnement cible et ne jamais être commités.

Commande de génération :

```bash
php bin/console lexik:jwt:generate-keypair --skip-if-exists
```

Après génération, vérifier que les fichiers sont présents dans `backend/config/jwt/` ou dans le chemin configuré.

### Auth / Sécurité

| Variable | Usage | Valeur recommandée |
| --- | --- | --- |
| `PASSWORD_RESET_LOG_TOKEN` | log du token reset en dev | `false` |

Cette variable ne doit jamais être activée en test, CI, pré-production ou production.

### Mail

| Variable | Usage |
| --- | --- |
| `MAILER_DSN` | transport Symfony Mailer |
| `MAIL_FROM_ADDRESS` | expéditeur |
| `MAIL_FROM_NAME` | nom expéditeur |

Local :

```text
MAILER_DSN=smtp://mailpit:1025
```

Production :

```text
MAILER_DSN=smtp://user:password@smtp.example.com:587
```

La valeur production doit être stockée comme secret, jamais dans Git.

### Football-data.org

| Variable | Usage |
| --- | --- |
| `FOOTBALL_DATA_API_BASE_URL` | URL API football-data.org |
| `FOOTBALL_DATA_API_TOKEN` | token API |
| `FOOTBALL_DATA_API_TIMEOUT` | timeout HTTP |

Le token utilisé localement doit être considéré comme local/dev. Avant toute mise en ligne, créer ou régénérer un token dédié production.

### WebSocket

| Variable | Usage |
| --- | --- |
| `WEBSOCKET_PORT` | port local Docker |
| `WEBSOCKET_URL` | URL WebSocket côté backend/local |
| `EXPO_PUBLIC_WEBSOCKET_URL` | URL WebSocket côté mobile |

Local :

```text
ws://localhost:8081
```

Production :

```text
wss://api.example.com/ws
```

Le WebSocket de production doit être chiffré via TLS.

### Mobile Expo

Les variables `EXPO_PUBLIC_*` sont publiques dans le bundle mobile.

Elles ne doivent contenir aucun secret.

| Variable | Usage |
| --- | --- |
| `EXPO_PUBLIC_API_BASE_URL` | URL publique de l'API |
| `EXPO_PUBLIC_WEBSOCKET_URL` | URL publique du WebSocket |

Production :

```text
EXPO_PUBLIC_API_BASE_URL=https://api.example.com/api
EXPO_PUBLIC_WEBSOCKET_URL=wss://api.example.com/ws
```

## 5. Secrets Production À Créer

Secrets obligatoires :

- `APP_SECRET` ;
- `JWT_PASSPHRASE` ;
- fichiers JWT privés/publics ;
- `DATABASE_URL` production ;
- mot de passe MySQL ;
- `FOOTBALL_DATA_API_TOKEN` ;
- `MAILER_DSN` SMTP production ;
- éventuels secrets du fournisseur d'hébergement.

Règles :

- aucun secret dans Git ;
- aucun secret dans `mobile/.env` si préfixé `EXPO_PUBLIC_*` ;
- rotation des secrets avant mise en ligne ;
- valeurs différentes entre local, CI, pré-production et production.

## 6. Base De Données

### Migrations

Avant déploiement :

```bash
php bin/console doctrine:migrations:status --env=prod
```

Pendant déploiement :

```bash
php bin/console doctrine:migrations:migrate --no-interaction --env=prod
```

La commande doit être exécutée après sauvegarde de la base.

### Fixtures

Les fixtures sont réservées à `dev` et `test`.

```bash
php bin/console doctrine:fixtures:load --env=dev
```

Attention : `doctrine:fixtures:load` purge la base cible. Cette commande ne doit jamais être exécutée en production.

### Sauvegardes

Avant chaque déploiement :

```bash
mysqldump --single-transaction --routines --triggers leklub > backup-leklub.sql
```

À prévoir en production :

- sauvegarde automatique quotidienne ;
- conservation de plusieurs jours ;
- test régulier de restauration.

## 7. Uploads Avatars

Les avatars sont stockés localement dans :

```text
backend/public/uploads/avatars/
```

Ce dossier est ignoré par Git.

En production, il doit être :

- persistant entre redémarrages ;
- sauvegardé avec la base ou selon une stratégie dédiée ;
- servi en statique par Nginx ;
- protégé par validation backend avant écriture.

Limites MVP :

- pas de stockage cloud ;
- pas de re-encodage serveur ;
- pas de scan antivirus ;
- validation actuelle par taille, extension, MIME et nom aléatoire.

## 8. Nginx / Reverse Proxy / HTTPS

Le Nginx local sert l'API Symfony et les uploads.

Headers déjà configurés :

- `X-Content-Type-Options: nosniff` ;
- `X-Frame-Options: DENY` ;
- `Referrer-Policy: no-referrer` ;
- `Permissions-Policy`.

En production, ajouter :

- HTTPS obligatoire ;
- redirection HTTP vers HTTPS ;
- HSTS uniquement après validation HTTPS ;
- configuration `wss://` pour WebSocket ;
- logs d'accès et d'erreur ;
- taille maximale des uploads cohérente avec la limite backend.

## 9. WebSocket

Le WebSocket sert uniquement à notifier l'arrivée d'un nouveau Message privé.

Le contenu réel des Messages privés est récupéré via REST après notification.

Local :

```text
ws://localhost:8081
```

Production cible :

```text
wss://api.example.com/ws
```

Limites MVP :

- pas de Redis ;
- pas de cluster multi-instance ;
- pas de reconnexion avancée serveur ;
- pas de contrôle d'origine production encore configuré.

Ces limites sont acceptables pour une démonstration CDA locale, mais doivent être traitées avant une application publique.

## 10. Mailpit Dev Et SMTP Production

Mailpit est réservé au local/dev.

URL locale :

```text
http://localhost:8025
```

En production :

- remplacer `MAILER_DSN` par un SMTP réel ;
- ne jamais activer `PASSWORD_RESET_LOG_TOKEN` ;
- tester forgot/reset password avec une adresse réelle de pré-production ;
- surveiller les erreurs d'envoi.

## 11. Mobile Expo

Développement :

```bash
cd mobile
npm run start
```

Ce lancement reste local et n'est pas dockerisé.

Build production à prévoir :

- configurer les URLs `https://` et `wss://` ;
- vérifier que les variables `EXPO_PUBLIC_*` ne contiennent aucun secret ;
- tester login, refresh token, reset password, upload avatar et WebSocket ;
- générer un build Expo/EAS ou équivalent selon la stratégie de distribution.

Cette étape ne crée pas de build production réel.

## 12. CI/CD Actuelle

GitHub Actions valide :

- backend Composer ;
- container Symfony ;
- mapping Doctrine ;
- tests PHPUnit ;
- TypeScript mobile.

Limites :

- pas de déploiement automatique ;
- pas de build mobile production ;
- pas de scan Docker image ;
- pas de environnement pré-production provisionné ;
- pas de secrets production GitHub configurés.

Cette CI est suffisante pour une branche `develop` stable et défendable CDA, mais pas pour une mise en production automatisée complète.

## 13. Procédure Pré-production

Checklist :

- [ ] créer une base dédiée ;
- [ ] créer secrets pré-production ;
- [ ] générer clés JWT ;
- [ ] configurer SMTP de test ;
- [ ] configurer `APP_ENV=prod` et `APP_DEBUG=0` ;
- [ ] configurer HTTPS ;
- [ ] configurer WebSocket en `wss://` ;
- [ ] exécuter migrations Doctrine ;
- [ ] vérifier dossier uploads persistant ;
- [ ] vérifier `/api/health` ;
- [ ] tester login/register ;
- [ ] tester refresh/logout ;
- [ ] tester forgot/reset password ;
- [ ] tester upload avatar ;
- [ ] tester feed ;
- [ ] tester messagerie et WebSocket ;
- [ ] tester admin ;
- [ ] vérifier logs.

## 14. Procédure Déploiement Manuel Réaliste

1. Récupérer la version Git validée.
2. Installer les dépendances backend :

```bash
composer install --no-dev --optimize-autoloader
```

3. Configurer les variables d'environnement réelles.
4. Générer ou déposer les clés JWT.
5. Sauvegarder la base existante.
6. Exécuter les migrations Doctrine.
7. Vérifier les permissions du dossier uploads.
8. Redémarrer PHP/Nginx/WebSocket.
9. Vérifier `/api/health`.
10. Lancer les tests manuels critiques.

## 15. Rollback Simple

En cas d'échec :

1. revenir au tag ou commit précédent ;
2. restaurer la sauvegarde MySQL si une migration a modifié le schéma ;
3. restaurer le dossier uploads si nécessaire ;
4. redémarrer les services ;
5. vérifier `/api/health` ;
6. vérifier login et accès admin.

Le rollback doit être testé en pré-production avant une vraie mise en ligne.

## 16. Logs Et Surveillance

À prévoir :

- logs PHP/Symfony ;
- logs Nginx ;
- logs WebSocket ;
- erreurs SMTP ;
- erreurs football-data.org ;
- erreurs upload avatar ;
- actions admin critiques.

Le MVP ne contient pas encore de monitoring production centralisé.

## 17. Checklist Déploiement

- [ ] branche `main` stable ;
- [ ] CI verte ;
- [ ] `composer audit` OK ;
- [ ] `npm audit --omit=dev --audit-level=high` OK ;
- [ ] secrets production créés ;
- [ ] `.env` production hors Git ;
- [ ] clés JWT générées ;
- [ ] base sauvegardée ;
- [ ] migrations prêtes ;
- [ ] SMTP configuré ;
- [ ] token football-data.org régénéré ;
- [ ] HTTPS configuré ;
- [ ] WebSocket en `wss://` ;
- [ ] uploads persistants ;
- [ ] fixtures non exécutées ;
- [ ] tests critiques validés.

## 18. Limites MVP Production

LeKlub reste un MVP. Avant publication réelle, il faudra renforcer :

- CORS production ;
- WebSocket production ;
- monitoring ;
- sauvegardes automatisées ;
- stockage avatars plus robuste ;
- scan/re-encodage des uploads ;
- stratégie de secrets ;
- build mobile production ;
- pipeline CD ;
- observabilité sécurité.

Ces limites sont assumées pour le CDA : l'objectif est de montrer une préparation sérieuse au déploiement, pas de simuler une production non maîtrisée.
