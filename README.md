# LeKlub

LeKlub est une application mobile MVP autour du football, développée dans le cadre d'un titre professionnel CDA.

Le projet est reconstruit proprement à partir d'un ancien projet utilisé uniquement comme référence technique.

## Objectifs Du MVP

- Authentification sécurisée avec JWT
- Gestion des utilisateurs et profils
- Feed utilisateur simple
- Conversations privées et Messages privés
- Réception de nouveaux Messages privés en temps réel via WebSocket
- Données football via API externe
- Interface mobile React Native Expo
- Backend Symfony API REST
- Base de données MySQL
- Docker pour l'environnement de développement
- CI/CD simple avec GitHub Actions

## État Actuel Du MVP

Le backend Symfony est opérationnel pour les fonctionnalités suivantes :

- inscription et connexion JWT
- consultation et modification du profil utilisateur
- création, affichage et suppression logique de Posts
- modification et suppression logique de ses propres Posts côté utilisateur
- ajout, modification et suppression logique de ses propres Commentaires
- ajout, modification et retrait de Réactions `like` / `dislike`
- modération simple par `ROLE_ADMIN`
- création de Conversations privées
- envoi, consultation et marquage comme lus des Messages privés
- suppression de Messages privés pour soi uniquement
- notification WebSocket simple lors d'un nouveau Message privé
- données football en lecture seule : résultats, matchs à venir, classement et buteurs
- Back Office Admin MVP : synthèse, liste utilisateurs sécurisée, modération des Posts et Commentaires

Vérifications actuelles :

- `composer validate --strict` : OK
- `lint:container` : OK
- `doctrine:schema:validate` : OK
- `phpunit` : OK
- `npx tsc --noEmit` : OK
- `composer audit` : OK
- `npm audit --omit=dev --audit-level=high` : vulnérabilités hautes absentes, vulnérabilités modérées Expo/Metro documentées
- tests manuels des endpoints API : OK
- test WebSocket authentifié : OK

## Choix MVP

LeKlub privilégie une version plus petite mais stable, propre et défendable devant un jury CDA.

Choix assumés :

- pas de refresh token dans cette première version
- pas de vérification d'email
- pas de reset password
- pas de système de signalements utilisateurs
- pas d'images dans les Posts
- pas de Réactions sur les Commentaires
- pas de groupes de conversation
- pas de pièces jointes dans les Messages privés
- pas de suppression de Message privé pour tout le monde
- pas de microservice, Redis, Mercure ou architecture distribuée pour le temps réel
- pas de persistance des données football tant que le besoin n'est pas démontré
- pas de suppression physique utilisateur, bannissement ou gestion avancée des rôles dans l'admin MVP
- pas de lecture des Messages privés dans l'admin

Ces choix réduisent la complexité et permettent de démontrer clairement l'architecture, la sécurité, Docker, les tests, l'API REST et le temps réel.

## Stack Technique

### Backend

- Symfony
- PHP
- Doctrine ORM
- MySQL
- LexikJWTAuthenticationBundle
- Ratchet pour le WebSocket MVP

### Frontend Mobile

- React Native
- Expo
- TypeScript
- React Navigation
- Axios
- Expo Secure Store

### Infrastructure

- Docker
- GitHub Actions

## Architecture

Le backend suit une architecture en couches simple :

- Controller / API : réception des requêtes HTTP et réponses JSON
- Application / Use Case : orchestration des cas d'utilisation
- Domain : règles métier principales
- Infrastructure : Doctrine, API externe, WebSocket, services techniques

Le frontend est organisé par écrans, services API, navigation, hooks, composants et types.

## Structure Du Projet

```text
leklub/
  backend/
  mobile/
  docker/
  docs/
  .github/workflows/
```

## Lancement En Développement

Commande :

```bash
docker compose --env-file .env.example up -d
```

API de vérification :

```bash
curl http://localhost:8080/api/health
```

WebSocket local :

```text
ws://localhost:8081
```

## Variables D'environnement

Voir `.env.example`.

Les secrets réels ne doivent jamais être versionnés.

## Stratégie Git

Branches principales :

- `main` : version stable et démontrable
- `develop` : branche d'intégration
- `feature/*` : développement d'une fonctionnalité

Exemples :

```bash
feature/auth
feature/feed
feature/messaging
feature/football-api
feature/docker-ci
```

## CI/CD

Une pipeline GitHub Actions simple vérifie :

- installation des dépendances backend
- `composer validate --strict`
- validation du container Symfony
- validation du mapping Doctrine
- tests backend
- installation des dépendances mobile
- vérification TypeScript mobile

Il n'y a pas de déploiement automatique dans le MVP.

## Documentation

- `docs/architecture.md`
- `docs/security.md`
- `docs/api.md`
- `docs/roadmap.md`

## Sécurité

Principes prévus :

- validation stricte des entrées
- mots de passe hashés
- JWT côté API
- contrôle d'accès backend
- rôles `ROLE_USER` et `ROLE_ADMIN`
- secrets hors repository
- stockage sécurisé du token côté mobile
- logs sans données sensibles

## Backend Actuel

Le backend Symfony est disponible dans `backend/`.

Commandes utiles :

```bash
docker compose --env-file .env.example run --rm php php bin/console lint:container
docker compose --env-file .env.example run --rm php php bin/phpunit
docker compose --env-file .env.example run --rm php php bin/console doctrine:migrations:migrate --no-interaction
docker compose --env-file .env.example logs -f websocket
```

Endpoints disponibles :

```text
GET    /api/health
POST   /api/auth/register
POST   /api/auth/login
GET    /api/me
PATCH  /api/me/profile
GET    /api/users
GET    /api/feed
POST   /api/feed
GET    /api/feed/{id}
DELETE /api/feed/{id}
PATCH  /api/feed/{id}
GET    /api/feed/{postId}/comments
POST   /api/feed/{postId}/comments
PATCH  /api/feed/comments/{id}
DELETE /api/feed/comments/{id}
PUT    /api/feed/{postId}/reaction
DELETE /api/feed/{postId}/reaction
GET    /api/conversations
POST   /api/conversations
GET    /api/conversations/{id}/messages
POST   /api/conversations/{id}/messages
DELETE /api/conversations/{id}/messages/{messageId}
PATCH  /api/conversations/{id}/read
GET    /api/football/competitions
GET    /api/football/competitions/{code}/results
GET    /api/football/competitions/{code}/upcoming
GET    /api/football/competitions/{code}/standings
GET    /api/football/competitions/{code}/scorers
GET    /api/admin/overview
GET    /api/admin/users
GET    /api/admin/posts
DELETE /api/admin/posts/{id}
GET    /api/admin/comments
DELETE /api/admin/comments/{id}
```

Le feed MVP gère les Posts texte, les Commentaires, les Réactions et la modération admin par suppression logique.

La messagerie MVP gère les Conversations privées entre deux utilisateurs, l'historique des Messages privés, le marquage comme lu et une notification WebSocket simple pour les nouveaux Messages privés.

Les données football sont lues depuis football-data.org avec une normalisation côté backend. Les réponses brutes de l'API externe ne sont jamais renvoyées directement au mobile.

Les résultats et matchs à venir peuvent être filtrés par journée avec le paramètre optionnel `matchday`.

Le Back Office Admin MVP est protégé par `ROLE_ADMIN`. Il ne retourne pas l'email dans la liste utilisateurs et utilise uniquement la suppression logique pour la modération du Feed.

## CORS Et Expo Go

Le MVP mobile est testé avec Expo Go en application native. Dans ce contexte, CORS n'est pas bloquant comme il peut l'être dans un navigateur web.

La variable `CORS_ALLOW_ORIGIN` est conservée dans `.env.example` pour une future exposition web éventuelle, mais aucune configuration CORS dédiée n'est nécessaire pour la démonstration mobile native actuelle.
