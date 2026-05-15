# Sécurité

Mesures prévues :

- authentification JWT
- hash des mots de passe
- validation stricte des entrées
- contrôle d'accès backend
- rôles `ROLE_USER` et `ROLE_ADMIN`
- stockage sécurisé du token côté mobile
- secrets hors repository
- logs sans données sensibles

## Authentification Backend

Le backend utilise Symfony Security avec LexikJWTAuthenticationBundle.

- `/api/auth/register` est public.
- `/api/auth/login` est public et géré par `json_login`.
- `/api/health` est public.
- Les autres routes `/api` demandent `ROLE_USER`.
- Les routes `/api/admin` demanderont `ROLE_ADMIN`.
- `ROLE_ADMIN` hérite de `ROLE_USER`.

Le login utilise l'email comme identifiant. Le JWT contient donc l'email dans le claim configuré par `user_id_claim`.

Le JWT contient aussi l'identifiant interne `id`, ajouté par un subscriber Lexik. Ce choix reste simple et permet au serveur WebSocket de rattacher une connexion au bon utilisateur sans exposer de mot de passe ni de donnée sensible.

## Mots De Passe

Les mots de passe sont hashés avec le password hasher Symfony configuré en mode `auto`.

Le mot de passe n'est jamais retourné dans les réponses API.

## Tokens JWT

Les clés JWT sont générées localement dans `backend/config/jwt/`.

Ces fichiers `.pem` sont exclus du repository.

Commande :

```bash
docker compose --env-file .env.example run --rm php php bin/console lexik:jwt:generate-keypair --skip-if-exists
```

Pas de refresh token dans le MVP initial. Ce choix réduit la complexité et reste défendable pour une première version stable.

## Feed Et Modération

Toutes les routes `/api/feed` demandent `ROLE_USER`.

Règles :

- un utilisateur peut créer un Post texte
- un utilisateur peut commenter un Post visible
- un utilisateur peut liker ou disliker un Post visible
- un seul vote est autorisé par utilisateur et par Post
- un utilisateur peut changer ou retirer sa Réaction
- un auteur peut supprimer son propre Post ou Commentaire
- `ROLE_ADMIN` peut supprimer n'importe quel Post ou Commentaire

La modération admin reste volontairement simple :

- suppression logique via `deletedAt`
- trace de l'utilisateur modérateur via `deletedBy`
- pas de statut `pending` ou `approved`
- pas de signalements utilisateurs dans le MVP

Les contenus supprimés sont invisibles dans :

- le feed
- le détail d'un Post
- les Commentaires
- les Réactions

Les contenus texte sont normalisés avant stockage :

- `trim`
- suppression des balises HTML
- refus si vide après normalisation
- stockage en texte brut

## Messagerie Et WebSocket

Toutes les routes `/api/conversations` demandent `ROLE_USER`.

Règles :

- une Conversation privée contient deux participants
- un utilisateur ne peut pas créer une Conversation privée avec lui-même
- seul un participant peut lire les Messages privés d'une Conversation privée
- seul un participant peut envoyer un Message privé dans une Conversation privée
- seul un participant peut marquer une Conversation privée comme lue
- les réponses API exposent l'id et le username, jamais le mot de passe ni l'email de l'autre participant

Les Messages privés sont normalisés avant stockage :

- `trim`
- suppression des balises HTML
- refus si vide après normalisation
- limite à 1000 caractères
- stockage en texte brut

Le WebSocket est authentifié par JWT après ouverture de connexion.

Les notifications WebSocket ne transportent pas le contenu du Message privé. Elles contiennent uniquement l'identifiant de Conversation privée, l'identifiant du Message privé et l'expéditeur. Le client mobile récupère ensuite l'historique via l'API REST protégée.

Le MVP utilise un notificateur fichier local pour transmettre les événements au serveur WebSocket. Ce choix est volontairement simple pour l'examen CDA :

- pas de Redis
- pas de microservice
- pas de bus distribué
- comportement démontrable localement avec Docker

## API Football

Le backend utilise football-data.org, comme le projet de référence.

Mesures appliquées :

- clé API lue depuis `FOOTBALL_DATA_API_TOKEN`
- clé API jamais renvoyée dans les réponses JSON
- clé utilisée temporairement en local pour le MVP, jamais versionnée, à régénérer avant toute mise en ligne
- timeout HTTP explicite via `FOOTBALL_DATA_API_TIMEOUT`
- transformation des erreurs externes en réponse JSON propre
- aucun retour de stack trace au frontend
- aucune persistance en base de données dans le MVP
- normalisation des réponses externes avant retour API

Limites vérifiées dans la documentation officielle football-data.org :

- clients enregistrés en offre gratuite : 10 requêtes par minute
- clients non authentifiés : accès très limité

Conséquence MVP : les endpoints football restent simples et peu nombreux pour éviter de consommer inutilement le quota.

## Limites Connues Du MVP

- pas de refresh token
- pas de vérification d'email
- pas de reset password
- pas de blocage de compte après plusieurs tentatives échouées
- pas de rate limiting avancé par endpoint
- pas de chiffrement applicatif du contenu des Messages privés
- pas de suppression avancée des Messages privés
- pas de notifications push hors application ouverte
- pas de reconnexion WebSocket avancée
- pas de pagination de l'historique des Messages privés
- pas de système de signalement utilisateur
- pas de cache des données football
- pas de persistance des données football
- dépendance à la disponibilité et au quota de football-data.org
- WebSocket adapté à une démonstration locale, pas à une architecture multi-serveurs en production

Ces limites sont assumées pour garder un MVP stable, compréhensible et réaliste dans le cadre du titre CDA.
