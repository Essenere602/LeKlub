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
- Les routes `/api/admin` demandent `ROLE_ADMIN`.
- `ROLE_ADMIN` hérite de `ROLE_USER`.

Le login utilise l'email comme identifiant. Le JWT contient donc l'email dans le claim configuré par `user_id_claim`.

Le JWT contient aussi l'identifiant interne `id`, ajouté par un subscriber Lexik. Ce choix reste simple et permet au serveur WebSocket de rattacher une connexion au bon utilisateur sans exposer de mot de passe ni de donnée sensible.

## Mots De Passe

Les mots de passe sont hashés avec le password hasher Symfony configuré en mode `auto`.

Le mot de passe n'est jamais retourné dans les réponses API.

Le changement de mot de passe est disponible pour l'utilisateur authentifié via `PATCH /api/me/password`.

Mesures appliquées :

- ancien mot de passe obligatoire
- vérification de l'ancien mot de passe avec Symfony PasswordHasher
- nouveau mot de passe validé avec les mêmes règles qu'à l'inscription
- confirmation du nouveau mot de passe obligatoire
- hash du nouveau mot de passe avec Symfony PasswordHasher
- message générique si le changement est refusé
- aucun mot de passe clair ou hash retourné dans l'API
- aucune modification de l'email, du username ou des rôles

## Tokens JWT

Les clés JWT sont générées localement dans `backend/config/jwt/`.

Ces fichiers `.pem` sont exclus du repository.

Commande :

```bash
docker compose --env-file .env.example run --rm php php bin/console lexik:jwt:generate-keypair --skip-if-exists
```

Pas de refresh token dans le MVP initial. Ce choix réduit la complexité et reste défendable pour une première version stable.

Après un changement de mot de passe, les JWT déjà émis restent valides jusqu'à leur expiration. Cette limite est documentée et acceptée dans le MVP, car l'application ne gère pas encore de révocation de tokens ni de sessions serveur.

## Feed Et Modération

Toutes les routes `/api/feed` demandent `ROLE_USER`.

Règles :

- un utilisateur peut créer un Post texte
- un utilisateur peut commenter un Post visible
- un utilisateur peut liker ou disliker un Post visible
- un seul vote est autorisé par utilisateur et par Post
- un utilisateur peut changer ou retirer sa Réaction
- un auteur peut modifier son propre Post ou Commentaire visible
- un auteur peut supprimer son propre Post ou Commentaire
- `ROLE_ADMIN` peut supprimer n'importe quel Post ou Commentaire
- `ROLE_ADMIN` ne modifie pas le contenu utilisateur, il modère uniquement par suppression logique

La modération admin reste volontairement simple :

- suppression logique via `deletedAt`
- trace de l'utilisateur modérateur via `deletedBy`
- pas de statut `pending` ou `approved`

Les signalements Feed ajoutent une étape de contrôle communautaire sans mélanger les responsabilités :

- un utilisateur connecté peut signaler un Post ou un Commentaire visible
- un contenu supprimé ne peut pas être signalé
- un utilisateur ne peut signaler qu'une seule fois le même contenu
- les raisons sont limitées à une liste validée côté backend
- le détail optionnel est limité à 500 caractères, normalisé en texte brut et nullable si vide après `trim`
- l'admin peut consulter et résoudre un signalement sans supprimer le contenu
- l'admin peut rejeter un signalement sans supprimer le contenu
- l'admin peut supprimer directement le contenu signalé et avertir l'auteur
- après 3 avertissements, le compte de l'auteur est suspendu temporairement 7 jours pour les actions d'écriture
- les notifications système informent le reporter et l'auteur sans utiliser la messagerie privée

Les contenus supprimés sont invisibles dans :

- le feed
- le détail d'un Post
- les Commentaires
- les Réactions

Un contenu supprimé ne peut plus être modifié.

Les contenus texte sont normalisés avant stockage :

- `trim`
- suppression des balises HTML
- refus si vide après normalisation
- stockage en texte brut

## Back Office Admin

Le Back Office Admin MVP est protégé côté backend par `ROLE_ADMIN` :

- règle globale dans `security.yaml` sur `/api/admin`
- attribut `#[IsGranted('ROLE_ADMIN')]` sur les contrôleurs admin
- onglet mobile Admin affiché uniquement si l'utilisateur courant possède `ROLE_ADMIN`

Les endpoints admin appliquent un principe de minimisation :

- la liste utilisateurs ne retourne pas l'email
- aucun mot de passe, token JWT ou secret n'est exposé
- les Messages privés ne sont jamais lus ni affichés dans l'admin
- les actions de modération utilisent la suppression logique existante
- `deletedBy` conserve une trace du modérateur
- les décisions de signalement gardent `resolvedAt`, `resolvedBy`, `decision` et une note admin optionnelle
- les notifications système sont séparées des Messages privés
- la supervision admin peut consulter les Posts et Commentaires actifs ou supprimés, mais toujours sans email, mot de passe, token ou hash
- les contenus supprimés restent des suppressions logiques avec `deletedAt` et `deletedBy`
- la gestion du rôle admin reste limitée à `ROLE_USER` / `ROLE_ADMIN`
- un admin ne peut pas modifier ses propres rôles
- le dernier administrateur ne peut pas être rétrogradé
- un utilisateur suspendu ne peut pas être promu administrateur
- les notifications système `admin_role_granted` et `admin_role_removed` informent l'utilisateur concerné

Le MVP admin ne contient pas :

- suppression physique utilisateur
- bannissement
- RBAC complexe ou permissions fines
- dashboard complexe ou graphiques

## Suspension Temporaire

La suspension est volontairement simple :

- chaque contenu supprimé suite à un signalement validé crée un avertissement
- les avertissements sont consultables par `ROLE_ADMIN` via `/api/admin/warnings`
- à partir de 3 avertissements, le compte est suspendu temporairement 7 jours
- un admin peut suspendre manuellement un utilisateur pour 1, 7 ou 30 jours avec une raison obligatoire
- l'auto-suspension admin est interdite
- la suspension manuelle d'un autre `ROLE_ADMIN` est interdite dans cette version
- la lecture de l'application reste possible
- les actions d'écriture sont bloquées :
  - créer ou modifier un Post
  - créer ou modifier un Commentaire
  - réagir à un Post
  - signaler un contenu
  - envoyer un Message privé
- il n'y a pas de bannissement définitif dans cette version
- l'admin ne peut pas supprimer ou modifier manuellement un avertissement dans le MVP
- les JWT existants restent valides pendant une suspension, mais les endpoints d'écriture restent protégés côté backend
- les notifications système `user_suspended` et `user_unsuspended` informent l'utilisateur concerné

## Messagerie Et WebSocket

Toutes les routes `/api/conversations` demandent `ROLE_USER`.

Règles :

- une Conversation privée contient deux participants
- un utilisateur ne peut pas créer une Conversation privée avec lui-même
- seul un participant peut lire les Messages privés d'une Conversation privée
- seul un participant peut envoyer un Message privé dans une Conversation privée
- seul un participant peut marquer une Conversation privée comme lue
- seul un participant peut masquer un Message privé pour lui-même
- les réponses API exposent l'id et le username, jamais le mot de passe ni l'email de l'autre participant

Les Messages privés sont normalisés avant stockage :

- `trim`
- suppression des balises HTML
- refus si vide après normalisation
- limite à 1000 caractères
- stockage en texte brut

Le WebSocket est authentifié par JWT après ouverture de connexion.

La suppression de Message privé dans le MVP est une suppression pour soi uniquement :

- table dédiée `message_hidden_for_user`
- aucune suppression physique
- aucun impact sur l'autre participant
- l'historique REST filtre les Messages privés masqués pour l'utilisateur courant
- la liste des Conversations privées utilise le dernier Message privé encore visible pour l'utilisateur courant
- aucun changement WebSocket

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
- pas de révocation des JWT déjà émis après changement de mot de passe
- pas de vérification d'email
- pas de reset password
- pas de blocage de compte après plusieurs tentatives échouées
- pas de rate limiting avancé par endpoint
- pas de chiffrement applicatif du contenu des Messages privés
- pas de suppression avancée des Messages privés
- pas de notifications push hors application ouverte
- pas de reconnexion WebSocket avancée
- pas de pagination de l'historique des Messages privés
- pas de notifications automatiques liées aux signalements
- pas d'escalade complexe des signalements
- pas de cache des données football
- pas de persistance des données football
- dépendance à la disponibilité et au quota de football-data.org
- WebSocket adapté à une démonstration locale, pas à une architecture multi-serveurs en production

Ces limites sont assumées pour garder un MVP stable, compréhensible et réaliste dans le cadre du titre CDA.
