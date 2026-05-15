# LeKlub Mobile

Application mobile Expo React Native pour le MVP LeKlub.

## Lancement Avec Expo Go

Le frontend mobile n'est pas dockerisé. Il se lance localement pour faciliter Expo Go, le QR code, le hot reload et les tests sur iPhone.

Depuis la racine du projet :

```bash
cd mobile
npm run start
```

Ensuite :

- ouvrir Expo Go sur iPhone
- scanner le QR code affiché par Expo
- vérifier que le backend Docker est lancé

Backend attendu :

```bash
cd ..
docker compose --env-file .env.example up -d
```

## Configuration API

Créer un fichier local `mobile/.env` à partir de `mobile/.env.example`.

```bash
cp .env.example .env
```

Expo lit les variables `EXPO_PUBLIC_*` au démarrage du serveur Metro. Après modification de `mobile/.env`, il faut arrêter puis relancer :

```bash
npm run start
```

### iPhone Physique Avec Expo Go

Utiliser l'adresse IP locale de la machine qui lance Docker et Expo.

Exemple :

```text
EXPO_PUBLIC_API_BASE_URL=http://192.168.1.20:8080/api
EXPO_PUBLIC_WEBSOCKET_URL=ws://192.168.1.20:8081
```

L'iPhone et l'ordinateur doivent être sur le même réseau.

Test rapide depuis Safari sur l'iPhone :

```text
http://192.168.1.20:8080/api/health
```

La réponse attendue est :

```json
{"status":"ok","application":"LeKlub API"}
```

### Simulateur iOS

`localhost` peut fonctionner :

```text
EXPO_PUBLIC_API_BASE_URL=http://localhost:8080/api
EXPO_PUBLIC_WEBSOCKET_URL=ws://localhost:8081
```

### Android Emulator

Utiliser `10.0.2.2` pour joindre la machine hôte :

```text
EXPO_PUBLIC_API_BASE_URL=http://10.0.2.2:8080/api
EXPO_PUBLIC_WEBSOCKET_URL=ws://10.0.2.2:8081
```

## Socle Technique

- Expo
- TypeScript
- React Navigation
- Axios
- Expo Secure Store
- thème simple LeKlub

## Choix MVP

- pas de Docker pour le frontend mobile
- pas de Redux ou Zustand pour l'instant
- pas de librairie UI lourde
- stockage sécurisé du JWT avec Expo Secure Store
- appels API isolés dans `src/services`
- thème centralisé dans `src/config/theme.ts`

## Authentification Mobile

L'authentification mobile utilise :

- `POST /api/auth/register` pour créer un compte
- `POST /api/auth/login` pour récupérer le JWT
- `GET /api/me` pour charger l'utilisateur connecté
- Expo Secure Store pour stocker le JWT

Au démarrage, si un token existe, l'application appelle `/api/me`.

Si `/api/me` retourne `401`, le token est supprimé et l'utilisateur revient sur l'écran de connexion.

## Tests Manuels Auth

À vérifier sur iPhone avec Expo Go :

- l'application démarre sur l'écran Login sans token
- Register crée un compte
- Login connecte l'utilisateur
- l'écran Home affiche l'utilisateur retourné par `/api/me`
- fermer puis rouvrir Expo Go conserve la session
- Logout supprime la session et revient au Login
- un mauvais mot de passe affiche une erreur propre
- une mauvaise URL API affiche une erreur réseau compréhensible

## Profil Mobile

Le profil mobile MVP utilise :

- `GET /api/me` pour afficher l'utilisateur connecté et son profil
- `PATCH /api/me/profile` pour modifier `displayName`, `bio`, `favoriteTeamName` et `avatarUrl`
- `AuthContext.refreshCurrentUser()` après modification pour recharger les données depuis le backend

L'avatar reste une URL texte pour le MVP. Aucun upload d'image n'est prévu à cette étape.

## Tests Manuels Profil

À vérifier sur iPhone avec Expo Go :

- depuis Home, ouvrir `Mon profil`
- modifier le nom affiché, la bio et l'équipe favorite
- enregistrer et vérifier le message de succès
- revenir à Home et vérifier que les données sont rafraîchies
- saisir une URL avatar invalide et vérifier l'erreur de validation
- vider un champ et vérifier qu'il est bien accepté comme valeur vide
- utiliser Logout depuis l'écran Profil

## Feed Mobile

Le Feed mobile MVP utilise :

- `GET /api/feed?page=1&limit=10` pour afficher les Posts
- `POST /api/feed` pour créer un Post texte
- `GET /api/feed/{id}` pour afficher un Post
- `GET /api/feed/{postId}/comments?page=1&limit=20` pour afficher les Commentaires
- `POST /api/feed/{postId}/comments` pour ajouter un Commentaire
- `PUT /api/feed/{postId}/reaction` pour liker ou disliker
- `DELETE /api/feed/{postId}/reaction` pour retirer la Réaction

Le backend ne retourne pas encore la Réaction courante de l'utilisateur. L'interface affiche donc les compteurs serveur et propose les actions `Like`, `Dislike` et `Retirer`, sans état visuel actif du vote.

## Tests Manuels Feed

À vérifier sur iPhone avec Expo Go :

- depuis Home, ouvrir `Feed`
- créer un Post valide
- tenter de publier un Post vide et vérifier l'erreur
- ouvrir un Post
- ajouter un Commentaire valide
- tenter d'ajouter un Commentaire vide et vérifier l'erreur
- liker un Post et vérifier le compteur
- disliker le même Post et vérifier la mise à jour
- retirer la Réaction et vérifier la mise à jour
- utiliser `Charger plus` si plus de 10 Posts existent
- revenir au Feed après un commentaire et rafraîchir la liste

## Football Mobile

Le module Football mobile MVP est en lecture seule et consomme uniquement les données normalisées par le backend LeKlub :

- `GET /api/football/competitions`
- `GET /api/football/competitions/{code}/results`
- `GET /api/football/competitions/{code}/upcoming`
- `GET /api/football/competitions/{code}/standings`
- `GET /api/football/competitions/{code}/scorers`

Les sections `Résultats` et `À venir` acceptent une sélection de journée côté mobile. L'application envoie `matchday` au backend, par exemple :

```text
GET /api/football/competitions/FL1/results?limit=20&matchday=12
GET /api/football/competitions/FL1/upcoming?limit=20&matchday=12
```

Les compétitions supportées sont :

- France : `FL1`
- Angleterre : `PL`
- Espagne : `PD`
- Italie : `SA`
- Allemagne : `BL1`

## Tests Manuels Football

À vérifier sur iPhone avec Expo Go :

- depuis Home, ouvrir `Football`
- vérifier que les cinq compétitions sont affichées
- ouvrir chaque compétition
- tester les sections `Résultats`, `À venir`, `Classement` et `Buteurs`
- changer de journée dans `Résultats` et `À venir`
- vérifier les états de chargement
- vérifier les états vides si l'API ne retourne aucune donnée
- vérifier qu'une erreur football externe affiche un message propre sans détail technique

## Messagerie Mobile

La Messagerie mobile MVP utilise :

- `GET /api/users?query=&limit=20` pour choisir un utilisateur
- `GET /api/conversations` pour lister les Conversations privées
- `POST /api/conversations` pour créer ou ouvrir une Conversation privée
- `GET /api/conversations/{id}/messages` pour charger l'historique
- `POST /api/conversations/{id}/messages` pour envoyer un Message privé
- `PATCH /api/conversations/{id}/read` pour marquer les Messages privés reçus comme lus

Le WebSocket sert uniquement à notifier l'arrivée d'un nouveau Message privé. Le contenu affiché dans l'interface vient toujours de l'API REST.

### WebSocket Avec Expo Go

Configurer `EXPO_PUBLIC_WEBSOCKET_URL` dans `mobile/.env`.

Sur iPhone physique, utiliser l'IP locale de la machine :

```text
EXPO_PUBLIC_WEBSOCKET_URL=ws://192.168.1.20:8081
```

Le mobile ouvre la connexion WebSocket, envoie le JWT stocké dans Expo Secure Store, puis écoute uniquement les événements `new_message`.

Quand un événement arrive :

- la liste des Conversations privées est rafraîchie via REST
- si la Conversation privée concernée est ouverte, l'historique est rafraîchi via REST
- aucun contenu de Message privé venant du WebSocket n'est affiché directement

### État Lu / Non Lu

Les Messages privés envoyés par l'utilisateur courant affichent :

- `✓` : Message privé envoyé, pas encore lu
- `✓✓` : Message privé lu, basé sur `readAt`

Il n'y a pas de présence en ligne, pas de statut `delivered`, pas d'animation et pas d'accusé de lecture complexe dans le MVP.

## Tests Manuels Messagerie

À vérifier sur iPhone avec Expo Go :

- depuis Home, ouvrir `Messages`
- créer une Conversation privée avec un autre utilisateur
- envoyer un Message privé
- vérifier que le Message privé apparaît dans l'historique
- se connecter avec le deuxième utilisateur et ouvrir la Conversation privée
- vérifier que les Messages privés reçus sont marqués lus
- revenir avec le premier utilisateur et vérifier le passage de `✓` à `✓✓`
- envoyer un Message privé pendant que l'autre utilisateur est dans l'écran Messages et vérifier le rafraîchissement REST après notification WebSocket

## Audit NPM

`npm audit --omit=dev --audit-level=high` remonte actuellement des vulnérabilités modérées transitives dans l'outillage Expo / Metro via `postcss`.

La correction automatique proposée par npm implique un changement majeur de version Expo. Elle n'est pas appliquée dans le MVP pour conserver la compatibilité Expo Go.

## Limites MVP Mobile

- pas de Redux ou Zustand
- pas de notifications push hors application ouverte
- pas de reconnexion WebSocket avancée
- pas de pagination de l'historique des Messages privés
- pas de cache football complexe
- pas d'upload d'avatar ou d'image de Post
- pas de statut visuel actif pour la Réaction courante du Feed

## Structure

```text
src/
  components/
    feed/
    football/
    messaging/
    ui/
  config/
  contexts/
  hooks/
  navigation/
  services/
    api/
    auth/
    feed/
    football/
    messaging/
    user/
  screens/
    Auth/
    Feed/
    Football/
    Home/
    Messaging/
    Profile/
  types/
```
