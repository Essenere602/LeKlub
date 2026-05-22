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

### PATCH /api/me/password

Modifie le mot de passe de l'utilisateur authentifié.

```json
{
  "currentPassword": "OldPassword123",
  "newPassword": "NewPassword123",
  "newPasswordConfirmation": "NewPassword123"
}
```

Règles :

- route protégée par JWT
- ancien mot de passe obligatoire
- nouveau mot de passe obligatoire
- confirmation obligatoire
- validation du nouveau mot de passe identique à l'inscription : minimum 10 caractères, maximum 128, au moins une minuscule, une majuscule et un chiffre
- hash du nouveau mot de passe via Symfony PasswordHasher
- aucun hash ou mot de passe n'est retourné
- email, username et rôles ne sont pas modifiés

En cas d'ancien mot de passe incorrect, l'API retourne un message générique :

```json
{
  "success": false,
  "data": null,
  "message": "Unable to update password.",
  "errors": []
}
```

Codes possibles : `200`, `400`, `401`, `422`.

### GET /api/me/notifications?page=1&limit=10

Retourne les notifications système de l'utilisateur connecté.

Ces notifications sont distinctes de la messagerie privée.

Codes possibles : `200`, `401`.

### PATCH /api/me/notifications/{id}/read

Marque une notification système comme lue.

Un utilisateur ne peut marquer comme lue que ses propres notifications.

Codes possibles : `200`, `401`, `404`.

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

## Signalements Feed

Toutes les routes de signalement demandent un JWT.

Le signalement et la modération restent séparés :

- signaler crée un `FeedReport`
- modérer reste une action admin distincte par suppression logique
- un utilisateur ne peut signaler qu'une seule fois le même Post ou Commentaire
- un contenu déjà supprimé retourne `404` et ne peut pas être signalé

Raisons autorisées :

```text
spam
insults
harassment
hate_content
inappropriate_content
other
```

Le champ `details` est optionnel, limité à 500 caractères, normalisé en texte brut et enregistré à `null` s'il est vide après `trim`.

### POST /api/feed/{postId}/reports

Signale un Post visible.

```json
{
  "reason": "spam",
  "details": "Message optionnel pour la modération"
}
```

Codes possibles : `201`, `400`, `401`, `404`, `409`, `422`.

### POST /api/feed/comments/{commentId}/reports

Signale un Commentaire visible.

```json
{
  "reason": "insults",
  "details": null
}
```

Codes possibles : `201`, `400`, `401`, `404`, `409`, `422`.

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
- gestion contrôlée du rôle administrateur
- suppression logique uniquement

Il ne permet pas de lire les Messages privés, de supprimer physiquement un utilisateur, de bannir un compte ou de gérer des permissions avancées.

### GET /api/admin/overview

Retourne des compteurs de supervision.

```json
{
  "usersCount": 12,
  "postsCount": 34,
  "commentsCount": 51,
  "openReportsCount": 2,
  "conversationsCount": 8,
  "messagesCount": 93,
  "deletedPostsCount": 3,
  "deletedCommentsCount": 5,
  "suspendedUsersCount": 1,
  "warningsCount": 7
}
```

Cette synthèse ne retourne jamais le contenu des Messages privés.

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
  "createdAt": "2026-05-16T12:00:00+00:00",
  "isSuspended": false,
  "suspendedUntil": null
}
```

La réponse ne retourne pas `email`, `password`, token JWT ou donnée sensible.

Codes possibles : `200`, `401`, `403`.

### PATCH /api/admin/users/{id}/suspend

Suspend temporairement un utilisateur.

Payload :

```json
{
  "durationDays": 7,
  "reason": "Comportement contraire aux règles de la communauté."
}
```

Règles :

- `durationDays` doit valoir `1`, `7` ou `30`
- `reason` est obligatoire, en texte brut, limité à 500 caractères
- un admin ne peut pas se suspendre lui-même
- la suspension manuelle d'un utilisateur `ROLE_ADMIN` est refusée dans cette version
- la lecture de l'application reste autorisée
- les JWT existants restent valides, mais les actions d'écriture restent bloquées par `SuspensionGuard`
- une notification système `user_suspended` est créée

Codes possibles : `200`, `401`, `403`, `404`, `422`.

### PATCH /api/admin/users/{id}/unsuspend

Lève une suspension temporaire utilisateur.

Payload optionnel :

```json
{
  "reason": "Suspension levée après vérification."
}
```

L'action est idempotente : lever une suspension inexistante retourne un succès propre. Une notification système `user_unsuspended` est créée.

Codes possibles : `200`, `401`, `403`, `404`, `422`.

### PATCH /api/admin/users/{id}/promote-admin

Attribue le rôle `ROLE_ADMIN` à un utilisateur non suspendu.

Payload : aucun.

Règles :

- `ROLE_ADMIN` obligatoire
- un admin ne peut pas modifier ses propres rôles
- un utilisateur suspendu ne peut pas être promu
- seuls les rôles `ROLE_USER` et `ROLE_ADMIN` sont gérés
- le tableau `roles` ne duplique pas `ROLE_USER` ou `ROLE_ADMIN`
- une notification système `admin_role_granted` est créée

Codes possibles : `200`, `401`, `403`, `404`.

### PATCH /api/admin/users/{id}/demote-admin

Retire le rôle `ROLE_ADMIN` à un autre administrateur.

Payload : aucun.

Règles :

- `ROLE_ADMIN` obligatoire
- un admin ne peut pas modifier ses propres rôles
- le dernier administrateur ne peut pas être rétrogradé
- seuls les rôles `ROLE_USER` et `ROLE_ADMIN` sont gérés
- une notification système `admin_role_removed` est créée

Codes possibles : `200`, `401`, `403`, `404`.

### GET /api/admin/posts?page=1&limit=10&status=active&query=texte

Retourne les Posts à superviser dans la modération admin.

Paramètres :

- `status` : `active`, `deleted` ou `all`, défaut `active`
- `query` : recherche simple sur le contenu, username ou displayName auteur

Chaque Post retourne :

- l'auteur, sans email
- le contenu
- les compteurs utiles
- `deletedAt` et `deletedBy` si le Post est supprimé logiquement

Codes possibles : `200`, `401`, `403`.

### DELETE /api/admin/posts/{id}

Supprime logiquement un Post en renseignant `deletedAt` et `deletedBy`.

Codes possibles : `200`, `401`, `403`, `404`.

### GET /api/admin/comments?page=1&limit=10&status=active&query=texte

Retourne les Commentaires à superviser dans la modération admin.

Paramètres :

- `status` : `active`, `deleted` ou `all`, défaut `active`
- `query` : recherche simple sur le contenu, le Post parent, username ou displayName auteur

Chaque Commentaire retourne :

- l'auteur, sans email
- le contenu
- un extrait du Post parent
- `deletedAt` et `deletedBy` si le Commentaire est supprimé logiquement

Codes possibles : `200`, `401`, `403`.

### DELETE /api/admin/comments/{id}

Supprime logiquement un Commentaire en renseignant `deletedAt` et `deletedBy`.

Codes possibles : `200`, `401`, `403`, `404`.

### GET /api/admin/reports?page=1&limit=10&status=open

Retourne les signalements paginés.

Le filtre `status` est optionnel :

```text
open
resolved
```

Chaque signalement retourne :

- le type de contenu : `post` ou `comment`
- la raison
- le détail optionnel
- le statut
- le reporter, sans email
- un extrait du contenu signalé
- l'auteur du contenu, sans email
- la date de création
- les informations de résolution si le signalement est résolu

Codes possibles : `200`, `401`, `403`, `422`.

### PATCH /api/admin/reports/{id}/resolve

Arbitre un signalement.

Payload :

```json
{
  "decision": "rejected",
  "adminNote": "Note optionnelle"
}
```

Décisions possibles :

- `rejected` : le signalement n'est pas retenu, le contenu reste visible
- `content_removed` : le contenu est supprimé logiquement, l'auteur reçoit un avertissement

Si un utilisateur atteint 3 avertissements, son compte est temporairement suspendu 7 jours pour les actions d'écriture.

Le champ `adminNote` est optionnel, limité à 500 caractères et stocké en texte brut.

Cette action crée des notifications système :

- reporter notifié si son signalement est rejeté
- reporter notifié si le contenu est supprimé
- auteur notifié en cas d'avertissement
- auteur notifié en cas de suspension

Codes possibles : `200`, `400`, `401`, `403`, `404`, `409`, `422`.

### GET /api/admin/warnings?page=1&limit=10&userId=7&suspendedOnly=false

Retourne les avertissements utilisateurs créés après suppression d'un contenu signalé.

Filtres optionnels :

- `userId` : limite la liste aux avertissements d'un utilisateur
- `suspendedOnly` : retourne uniquement les utilisateurs actuellement suspendus

Chaque avertissement retourne :

- l'utilisateur averti, sans email
- la raison ou note de modération
- le type de contenu concerné : `post` ou `comment`
- l'identifiant du contenu concerné
- la date de création
- le modérateur à l'origine de l'avertissement, sans email
- le signalement lié
- le nombre d'avertissements de l'utilisateur
- l'état de suspension temporaire éventuel

Codes possibles : `200`, `401`, `403`, `422`.

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
