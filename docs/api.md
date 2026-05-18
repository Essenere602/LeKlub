# API

Les endpoints sont documentés progressivement avec le développement du MVP.

Base actuelle :

```text
/api/auth/register
/api/auth/login
/api/me
/api/feed
/api/conversations
/api/football
/api/admin
```

## Format De Réponse

Les endpoints applicatifs utilisent une réponse JSON uniforme :

```json
{
  "success": true,
  "data": {},
  "message": "Message lisible.",
  "errors": []
}
```

Exception : `/api/auth/login` est géré directement par Symfony Security et LexikJWTAuthenticationBundle. Il retourne le token JWT au format Lexik.

## Principaux Codes HTTP

Codes utilisés dans l'API :

- `200 OK` : lecture ou action réussie
- `201 Created` : ressource créée
- `400 Bad Request` : requête invalide ou règle métier refusée
- `401 Unauthorized` : JWT absent, invalide ou expiré
- `403 Forbidden` : utilisateur authentifié mais non autorisé
- `404 Not Found` : ressource inexistante ou non visible
- `409 Conflict` : conflit métier, par exemple email déjà utilisé
- `422 Unprocessable Entity` : erreur de validation des données

## Authentification

### POST /api/auth/register

Inscrit un utilisateur.

```json
{
  "email": "user@example.com",
  "username": "samuel",
  "password": "Password123!"
}
```

Réponse uniforme :

```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "email": "user@example.com",
      "username": "samuel",
      "roles": ["ROLE_USER"]
    }
  },
  "message": "User registered successfully.",
  "errors": []
}
```

Codes possibles : `201`, `400`, `409`, `422`.

### POST /api/auth/login

Connexion gérée par Symfony Security et LexikJWTAuthenticationBundle.

```json
{
  "email": "user@example.com",
  "password": "Password123!"
}
```

Réponse :

```json
{
  "token": "jwt..."
}
```

Codes possibles : `200`, `401`.

### GET /api/me

Retourne l'utilisateur authentifié.

Header requis :

```text
Authorization: Bearer <token>
```

Codes possibles : `200`, `401`.

### PATCH /api/me/profile

Modifie uniquement le profil de l'utilisateur authentifié.

```json
{
  "displayName": "Samuel",
  "bio": "Projet CDA LeKlub",
  "favoriteTeamName": "Paris",
  "avatarUrl": "https://example.com/avatar.png"
}
```

Codes possibles : `200`, `400`, `401`, `422`.

### GET /api/users?query=&limit=20

Retourne un annuaire minimal des utilisateurs disponibles pour créer une Conversation privée.

La route est protégée par JWT, exclut l'utilisateur connecté et ne retourne jamais `email`, `roles`, `password` ou donnée sensible.

Paramètres :

- `query` : optionnel, recherche simple sur `username` et `displayName`
- `limit` : optionnel, défaut `20`, maximum `20`

Exemple de réponse :

```json
{
  "id": 2,
  "username": "alex",
  "displayName": "Alex Paris",
  "avatarUrl": "https://example.com/avatar.png"
}
```

Codes possibles : `200`, `401`.

## Feed

Toutes les routes du feed demandent un JWT.

La pagination est volontairement bornée :

```text
page minimum : 1
limit par défaut : 10
limit maximum : 20
```

### GET /api/feed?page=1&limit=10

Retourne les Posts non supprimés.

Chaque Post contient les compteurs :

```json
{
  "id": 1,
  "content": "Post feed CDA",
  "author": {
    "id": 1,
    "username": "samuel"
  },
  "likesCount": 0,
  "dislikesCount": 0,
  "commentsCount": 1,
  "createdAt": "2026-05-14T18:13:00+00:00"
}
```

Codes possibles : `200`, `401`.

### POST /api/feed

Crée un Post texte.

```json
{
  "content": "Mon Post LeKlub"
}
```

Le contenu est normalisé :

- trim
- suppression des balises HTML
- refus si vide après normalisation
- stockage en texte brut

Codes possibles : `201`, `400`, `401`, `422`.

### GET /api/feed/{id}

Retourne un Post visible. Un Post supprimé retourne `404`.

Codes possibles : `200`, `401`, `404`.

### PATCH /api/feed/{id}

Modifie le contenu d'un Post visible.

Autorisé uniquement pour l'auteur du Post. `ROLE_ADMIN` ne modifie pas le contenu utilisateur, il modère par suppression logique.

```json
{
  "content": "Nouveau contenu du Post"
}
```

Le contenu est normalisé comme à la création :

- trim
- suppression des balises HTML
- refus si vide après normalisation
- stockage en texte brut

Codes possibles : `200`, `400`, `401`, `403`, `404`, `422`.

### DELETE /api/feed/{id}

Supprime logiquement un Post.

Autorisé :

- auteur du Post
- `ROLE_ADMIN`

Codes possibles : `200`, `401`, `403`, `404`.

## Commentaires

### GET /api/feed/{postId}/comments?page=1&limit=20

Retourne les Commentaires visibles d'un Post visible.

Codes possibles : `200`, `401`, `404`.

### POST /api/feed/{postId}/comments

Ajoute un Commentaire texte à un Post visible.

```json
{
  "content": "Beau match."
}
```

Codes possibles : `201`, `400`, `401`, `404`, `422`.

### DELETE /api/feed/comments/{id}

Supprime logiquement un Commentaire.

Autorisé :

- auteur du Commentaire
- `ROLE_ADMIN`

Codes possibles : `200`, `401`, `403`, `404`.

### PATCH /api/feed/comments/{id}

Modifie le contenu d'un Commentaire visible.

Autorisé uniquement pour l'auteur du Commentaire. Un Commentaire supprimé ou lié à un Post supprimé retourne `404`.

```json
{
  "content": "Nouveau commentaire"
}
```

Codes possibles : `200`, `400`, `401`, `403`, `404`, `422`.

## Réactions

### PUT /api/feed/{postId}/reaction

Ajoute ou modifie la Réaction de l'utilisateur connecté sur un Post visible.

```json
{
  "type": "like"
}
```

Valeurs autorisées :

```text
like
dislike
```

Un utilisateur ne peut avoir qu'une seule Réaction par Post.

Codes possibles : `200`, `400`, `401`, `404`, `422`.

### DELETE /api/feed/{postId}/reaction

Retire la Réaction de l'utilisateur connecté sur un Post visible.

Codes possibles : `200`, `401`, `404`.

## Conversations Privées

Toutes les routes de messagerie demandent un JWT.

La messagerie MVP est volontairement simple :

- Conversations privées entre deux utilisateurs
- Messages privés texte uniquement
- suppression d'un Message privé pour soi uniquement
- contenu normalisé en texte brut
- accès limité aux participants
- pas de groupes, pièces jointes ou suppression avancée

### GET /api/conversations

Retourne les Conversations privées de l'utilisateur authentifié.

Chaque Conversation privée contient uniquement les données utiles :

```json
{
  "id": 1,
  "participant": {
    "id": 2,
    "username": "alex"
  },
  "lastMessage": {
    "id": 10,
    "content": "Salut",
    "sender": {
      "id": 2,
      "username": "alex"
    },
    "readAt": null,
    "createdAt": "2026-05-14T22:04:00+00:00"
  },
  "unreadCount": 1,
  "updatedAt": "2026-05-14T22:04:00+00:00"
}
```

Codes possibles : `200`, `401`.

### POST /api/conversations

Crée ou retourne la Conversation privée existante avec un autre utilisateur.

```json
{
  "recipientId": 2,
  "firstMessage": "Salut"
}
```

`firstMessage` est optionnel. Un utilisateur ne peut pas créer une Conversation privée avec lui-même.

Codes possibles : `201`, `400`, `401`, `404`, `422`.

### GET /api/conversations/{id}/messages

Retourne l'historique des Messages privés d'une Conversation privée.

Autorisé uniquement si l'utilisateur authentifié est participant de la Conversation privée.

Les Messages privés masqués par l'utilisateur courant ne sont pas retournés.

Codes possibles : `200`, `401`, `403`, `404`.

### POST /api/conversations/{id}/messages

Envoie un Message privé dans une Conversation privée.

```json
{
  "content": "Message privé"
}
```

Le contenu est normalisé :

- trim
- suppression des balises HTML
- refus si vide après normalisation
- limite à 1000 caractères

Codes possibles : `201`, `400`, `401`, `403`, `404`, `422`.

### DELETE /api/conversations/{id}/messages/{messageId}

Masque un Message privé uniquement pour l'utilisateur authentifié.

Effets :

- le Message privé ne réapparaît plus dans son historique
- l'autre participant conserve le Message privé
- aucune suppression physique n'est effectuée
- le comportement est idempotent

Autorisé uniquement pour un participant de la Conversation privée.

Codes possibles : `200`, `401`, `403`, `404`.

### PATCH /api/conversations/{id}/read

Marque comme lus les Messages privés reçus dans la Conversation privée.

Autorisé uniquement pour un participant.

Codes possibles : `200`, `401`, `403`, `404`.

## WebSocket

Le WebSocket MVP sert uniquement à notifier les nouveaux Messages privés.

URL locale :

```text
ws://localhost:8081
```

Authentification après connexion :

```json
{
  "type": "auth",
  "token": "jwt..."
}
```

Message reçu lors d'un nouveau Message privé :

```json
{
  "type": "new_message",
  "recipientId": 1,
  "conversationId": 5,
  "message": {
    "id": 12,
    "sender": {
      "id": 2,
      "username": "alex"
    },
    "createdAt": "2026-05-14T22:04:00+00:00"
  }
}
```

Le contenu du Message privé n'est pas transmis dans l'événement WebSocket. Le mobile doit rafraîchir l'historique via l'API protégée.

## Administration

Toutes les routes `/api/admin/*` demandent un JWT avec `ROLE_ADMIN`.

Le Back Office Admin MVP est volontairement limité :

- synthèse simple
- liste utilisateurs sans email
- modération des Posts
- modération des Commentaires
- suppression logique uniquement

Il ne permet pas de lire les Messages privés, de supprimer physiquement un utilisateur, de bannir un compte ou de gérer les rôles.

### GET /api/admin/overview

Retourne des compteurs de supervision.

```json
{
  "usersCount": 12,
  "postsCount": 34,
  "commentsCount": 51,
  "conversationsCount": 8,
  "messagesCount": 93
}
```

Codes possibles : `200`, `401`, `403`.

### GET /api/admin/users?page=1&limit=10&query=

Retourne une liste paginée d'utilisateurs.

Champs retournés uniquement :

```json
{
  "id": 1,
  "username": "samuel",
  "displayName": "Samuel",
  "avatarUrl": null,
  "roles": ["ROLE_USER"],
  "createdAt": "2026-05-16T12:00:00+00:00"
}
```

La réponse ne retourne pas `email`, `password`, token JWT ou donnée sensible.

Codes possibles : `200`, `401`, `403`.

### GET /api/admin/posts?page=1&limit=10

Retourne les Posts visibles à modérer.

Codes possibles : `200`, `401`, `403`.

### DELETE /api/admin/posts/{id}

Supprime logiquement un Post en renseignant `deletedAt` et `deletedBy`.

Codes possibles : `200`, `401`, `403`, `404`.

### GET /api/admin/comments?page=1&limit=10

Retourne les Commentaires visibles à modérer.

Codes possibles : `200`, `401`, `403`.

### DELETE /api/admin/comments/{id}

Supprime logiquement un Commentaire en renseignant `deletedAt` et `deletedBy`.

Codes possibles : `200`, `401`, `403`, `404`.

## Football

Toutes les routes football demandent un JWT.

Les données football sont en lecture seule. Le backend ne renvoie jamais la réponse brute de football-data.org : les données sont normalisées avant d'être retournées au frontend.

Championnats supportés dans le MVP :

```text
FL1 : Ligue 1, France
PL  : Premier League, Angleterre
PD  : LaLiga, Espagne
SA  : Serie A, Italie
BL1 : Bundesliga, Allemagne
```

### GET /api/football/competitions

Retourne les championnats disponibles dans le MVP.

Codes possibles : `200`, `401`.

### GET /api/football/competitions/{code}/results?limit=10&matchday=34

Retourne les résultats d'un championnat. Le paramètre `matchday` est optionnel et permet de cibler une journée précise.

Paramètres :

- `limit` : optionnel, défaut `10`, maximum `20`
- `matchday` : optionnel, journée de championnat de `1` à `38`

Exemple de réponse normalisée :

```json
{
  "id": 10,
  "utcDate": "2026-05-15T20:00:00Z",
  "status": "FINISHED",
  "matchday": 34,
  "homeTeam": {
    "id": 1,
    "name": "Paris",
    "shortName": "PSG",
    "crest": "https://example.com/psg.png"
  },
  "awayTeam": {
    "id": 2,
    "name": "Lyon",
    "shortName": "OL",
    "crest": "https://example.com/ol.png"
  },
  "score": {
    "winner": "HOME_TEAM",
    "home": 2,
    "away": 1
  }
}
```

Codes possibles : `200`, `401`, `404`, `502`.

### GET /api/football/competitions/{code}/upcoming?limit=10&matchday=34

Retourne les matchs à venir d'un championnat. Le paramètre `matchday` est optionnel et permet de cibler une journée précise.

Paramètres :

- `limit` : optionnel, défaut `10`, maximum `20`
- `matchday` : optionnel, journée de championnat de `1` à `38`

Codes possibles : `200`, `401`, `404`, `502`.

### GET /api/football/competitions/{code}/standings

Retourne le classement d'un championnat.

Codes possibles : `200`, `401`, `404`, `502`.

### GET /api/football/competitions/{code}/scorers?limit=10

Retourne les statistiques des buteurs d'un championnat.

Codes possibles : `200`, `401`, `404`, `502`.

### Erreur API externe

Si football-data.org ne répond pas, dépasse le timeout ou refuse la requête, l'API retourne une réponse propre :

```json
{
  "success": false,
  "data": null,
  "message": "Football data is temporarily unavailable. Please try again later.",
  "errors": []
}
```
