# Architecture

## Decision Architecturale

L'architecture officielle de LeKlub est :

> Architecture en couches orientee Use Cases, inspiree Clean Architecture, adaptee a Symfony.

Cette formulation est volontairement precise. LeKlub n'est pas seulement une application Symfony MVC classique : les controleurs ne portent pas la logique metier et ne pilotent pas directement toute la persistance. Le coeur applicatif est organise autour de cas d'utilisation explicites, de repositories, de DTO, de voters et de services d'infrastructure.

Le choix est adapte au contexte CDA parce qu'il reste :

- simple a expliquer devant un jury ;
- coherent avec Symfony, Doctrine et React Native Expo ;
- testable par cas d'utilisation ;
- suffisamment structure pour separer les responsabilites ;
- moins risque qu'une architecture trop abstraite ou surdimensionnee.

## Ce Que LeKlub Est

LeKlub est une application mobile avec API REST securisee, organisee autour de deux grands ensembles :

- un backend Symfony structure en couches ;
- un frontend React Native Expo structure par navigation, ecrans, services API, composants et types.

Le backend suit quatre couches principales :

- **Controller / API** : recoit les requetes HTTP, transforme les entrees en DTO, appelle un use case et retourne une reponse JSON.
- **Application / Use Case** : orchestre un cas d'utilisation concret de l'application.
- **Domain** : contient les entites principales, contrats de repositories, exceptions et value objects.
- **Infrastructure** : contient les details techniques : Doctrine, API football, WebSocket, services de notification.

La securite est transversale :

- les firewalls JWT sont configures dans Symfony Security ;
- les routes sensibles sont protegees par authentification ;
- les droits par ressource sont centralises dans des voters ;
- les use cases gardent les regles applicatives importantes ;
- les donnees sensibles ne sont pas exposees dans les presenters.

## Ce Que LeKlub N'est Pas

### Pas Un MVC Symfony Classique

Symfony permet naturellement une organisation MVC, mais LeKlub ne suit pas le modele MVC classique ou le controleur contient une grande partie de la logique.

Dans LeKlub :

- les controleurs restent fins ;
- la logique applicative est dans `Application/*UseCase.php` ;
- les acces aux donnees passent par des repositories ;
- les reponses sont normalisees par des presenters ou par `ApiResponse`.

Exemple :

- `Controller/Api/Feed/FeedController.php` recoit la requete ;
- `Application/Feed/UpdatePostUseCase.php` orchestre la modification ;
- `Domain/Repository/PostRepositoryInterface.php` definit le contrat ;
- `Infrastructure/Doctrine/Repository/PostRepository.php` implemente la requete Doctrine.

### Pas Du DDD Strict

LeKlub emprunte certaines idees du Domain-Driven Design, notamment la presence d'un dossier `Domain`, d'entites metier, de value objects et d'exceptions metier.

Mais LeKlub n'est pas du DDD strict :

- il n'y a pas de bounded contexts formalises ;
- les aggregates ne sont pas modelises de maniere avancee ;
- le langage omnipresent n'est pas pousse au niveau d'un projet d'entreprise complexe ;
- les entites `Domain/Entity` utilisent des attributs Doctrine.

Ce compromis est volontaire. Pour un projet CDA, l'objectif est de montrer une separation claire sans complexifier artificiellement le code.

### Pas Une Architecture Hexagonale Complete

LeKlub n'est pas une architecture hexagonale complete.

Il existe bien des interfaces de repositories dans `Domain/Repository`, mais tous les ports et adapters ne sont pas generalises. Certains services techniques restent volontairement dans `Infrastructure`, par exemple :

- `Infrastructure/Football/FootballDataClientInterface.php` ;
- `Infrastructure/WebSocket/MessageNotifierInterface.php`.

Ce choix est pragmatique : l'API football et le WebSocket sont des details techniques du MVP. Les extraire dans une couche de ports plus abstraite aurait ajoute de la complexite sans benefice suffisant pour le perimetre CDA.

### Pas Une Clean Architecture Stricte

LeKlub est inspire de la Clean Architecture, mais n'en applique pas toutes les regles strictes.

En particulier :

- les entites de domaine portent des attributs Doctrine ;
- Symfony reste visible dans certaines parties du projet ;
- les presenters sont places dans `Application` par simplicite ;
- certaines interfaces techniques restent dans `Infrastructure`.

Le benefice recherche est la separation des responsabilites, pas la purete theorique.

## Arborescence Backend Commentee

```text
backend/src/
  Controller/Api/
    Admin/                 Endpoints admin proteges ROLE_ADMIN
    Auth/                  Inscription et authentification publique
    Feed/                  Endpoints Posts, Commentaires, Reactions
    Football/              Endpoints lecture seule football
    Messaging/             Endpoints Conversations et Messages prives
    User/                  Profil, utilisateur courant, annuaire

  DTO/
    Auth/                  Donnees entrantes inscription
    Feed/                  Donnees entrantes Posts, Commentaires, Reactions
    Messaging/             Donnees entrantes Conversations et Messages
    User/                  Donnees entrantes profil utilisateur

  Application/
    Admin/                 Cas d'utilisation du Back Office Admin
    Auth/                  Cas d'utilisation d'inscription
    Feed/                  Cas d'utilisation du feed social
    Football/              Cas d'utilisation lecture football
    Messaging/             Cas d'utilisation messagerie privee
    User/                  Cas d'utilisation profil et annuaire

  Domain/
    Entity/                Entites principales : User, Post, Comment, Message...
    Repository/            Interfaces de repositories attendues par l'application
    Exception/             Exceptions metier ou applicatives
    ValueObject/           Valeurs metier simples : competition, reaction...

  Infrastructure/
    Doctrine/Repository/   Implementations Doctrine des repositories
    Football/              Client vers football-data.org
    WebSocket/             Serveur temps reel et notification technique

  Security/
    Voter/                 Autorisations par ressource
    EventSubscriber/       Adaptation technique du JWT

  Shared/
    Api/                   Format de reponse API et pagination
```

## Arborescence Frontend Commentee

```text
mobile/src/
  navigation/              Navigation publique, protegee, tabs et stacks internes
  screens/                 Ecrans metier affiches a l'utilisateur
    Admin/                 Back Office mobile reserve ROLE_ADMIN
    Auth/                  Login et Register
    Feed/                  Feed, detail Post, Commentaires
    Football/              Competitions, resultats, classements, buteurs
    Home/                  Accueil utilisateur
    Messaging/             Conversations, messages, choix utilisateur
    Profile/               Profil et compte utilisateur

  components/
    admin/                 Cartes et elements specifiques au Back Office
    feed/                  PostCard, CommentCard, reactions
    football/              Cards football, classements, buteurs, logos
    messaging/             ConversationCard, MessageBubble, avatars
    ui/                    Design system leger : headers, cards, etats

  services/
    api/                   Client Axios, erreurs API
    auth/                  Login, register, stockage token
    feed/                  Appels REST feed
    football/              Appels REST football
    messaging/             Appels REST messagerie et WebSocket
    user/                  Profil et annuaire utilisateur
    admin/                 Appels REST admin

  contexts/                AuthContext
  hooks/                   Hooks transverses comme WebSocket
  types/                   Types TypeScript par domaine fonctionnel
  config/                  Theme, variables d'environnement, visuels football
```

Le frontend applique le meme principe general que le backend : les ecrans affichent et orchestrent l'interface, mais les appels reseau sont separes dans `services/`, les composants visuels sont reutilisables et les contrats sont formalises dans `types/`.

## Backend Par Domaine Fonctionnel

### Authentification Et Profil

- `Controller/Api/Auth/RegisterController.php`
- `Application/Auth/RegisterUserUseCase.php`
- `Controller/Api/User/MeController.php`
- `Application/User/GetCurrentUserProfileUseCase.php`
- `Application/User/UpdateCurrentUserProfileUseCase.php`
- `Security/EventSubscriber/JWTCreatedSubscriber.php`

L'authentification JWT est geree par Symfony Security et LexikJWTAuthenticationBundle. Le backend reste responsable de l'identite, des roles et du controle d'acces.

### Feed Social

- `Controller/Api/Feed/FeedController.php`
- `Controller/Api/Feed/CommentController.php`
- `Application/Feed/*UseCase.php`
- `Domain/Entity/Post.php`
- `Domain/Entity/Comment.php`
- `Domain/Entity/PostReaction.php`
- `Security/Voter/PostVoter.php`
- `Security/Voter/CommentVoter.php`

Le feed illustre bien la separation en couches : les controleurs recoivent les requetes, les use cases appliquent les regles, les voters protegent les actions auteur/admin et Doctrine persiste les donnees.

### Messagerie Privee

- `Controller/Api/Messaging/ConversationController.php`
- `Controller/Api/Messaging/MessageController.php`
- `Application/Messaging/*UseCase.php`
- `Domain/Entity/Conversation.php`
- `Domain/Entity/Message.php`
- `Domain/Entity/MessageHiddenForUser.php`
- `Security/Voter/ConversationVoter.php`
- `Infrastructure/WebSocket/*`

La messagerie separe clairement REST et WebSocket :

- REST reste la source de verite pour lire, envoyer, masquer et marquer comme lus les messages ;
- WebSocket sert uniquement a notifier l'arrivee d'un nouveau message.

L'admin ne lit jamais le contenu des messages prives.

### Football

- `Controller/Api/Football/FootballController.php`
- `Application/Football/*UseCase.php`
- `Domain/ValueObject/FootballCompetition.php`
- `Infrastructure/Football/ExternalFootballDataClient.php`

Les donnees football sont en lecture seule. Le controleur ne retourne jamais la reponse brute de football-data.org : les donnees sont normalisees pour l'application mobile.

### Back Office Admin

- `Controller/Api/Admin/*`
- `Application/Admin/*UseCase.php`
- `Application/Admin/AdminPresenter.php`
- repositories Doctrine existants enrichis avec des methodes admin ciblees
- ecrans mobiles dans `mobile/src/screens/Admin`

Le Back Office Admin reste simple :

- synthese ;
- liste utilisateurs sans donnees sensibles ;
- moderation Posts et Commentaires ;
- suppression logique ;
- aucune suppression physique utilisateur ;
- aucune lecture des Messages prives.

## Securite Dans L'Architecture

La securite est placee au backend, meme si le mobile masque aussi certains boutons pour l'experience utilisateur.

Principes :

- routes publiques limitees : health, register, login ;
- routes applicatives protegees par JWT ;
- routes admin protegees par `ROLE_ADMIN` ;
- voters pour les ressources sensibles ;
- validation des entrees par DTO ;
- mots de passe hashes ;
- secrets hors repository ;
- token JWT stocke cote mobile via Expo Secure Store ;
- pas de donnees sensibles dans les reponses presenters ;
- logs sans secret volontairement expose.

Exemples :

- un utilisateur ne peut modifier que son propre profil ;
- un utilisateur ne peut supprimer que ses propres Posts ou Commentaires ;
- un admin peut moderer Posts et Commentaires ;
- un utilisateur ne peut consulter que ses Conversations privees ;
- un Message masque pour soi reste visible pour l'autre participant.

## Infrastructure

Le projet utilise Docker pour fournir un environnement de developpement reproductible :

- conteneur PHP/Symfony ;
- conteneur Nginx ;
- conteneur MySQL ;
- conteneur WebSocket ;
- variables d'environnement via `.env` local non versionne et `.env.example` versionne.

GitHub Actions execute une CI simple :

- installation backend ;
- validation Composer ;
- validation container Symfony ;
- validation mapping Doctrine ;
- tests PHPUnit ;
- installation mobile ;
- verification TypeScript.

Le frontend Expo n'est pas dockerise en developpement. Il est lance localement pour conserver Expo Go, QR code, hot reload et tests sur iPhone physique.

## Compromis Assumes

Les compromis suivants sont volontaires et doivent etre expliques comme des choix MVP :

- les entites du domaine utilisent les attributs Doctrine ;
- les interfaces football et WebSocket restent dans `Infrastructure` ;
- les presenters sont dans `Application` pour normaliser les sorties sans ajouter une couche de presentation abstraite ;
- l'admin MVP reste volontairement limite ;
- pas de microservices ;
- pas de CQRS avance ;
- pas d'event sourcing ;
- pas de refresh token pour l'instant ;
- pas de lecture admin des messages prives ;
- pas de suppression physique utilisateur.

Ces choix gardent le projet comprehensible, stable et defendable dans le cadre CDA.

## Regles Pour Les Futures Features

Pour chaque nouvelle fonctionnalite :

1. creer une branche `feature/nom-explicite` depuis `develop` ;
2. ajouter ou modifier un DTO si une entree utilisateur est recue ;
3. garder le controleur fin ;
4. placer la logique applicative dans un use case ;
5. utiliser une interface de repository si le use case accede aux donnees ;
6. implementer les requetes Doctrine dans `Infrastructure/Doctrine/Repository` ;
7. ajouter un voter si l'action depend d'une ressource sensible ;
8. normaliser les reponses et ne pas exposer de donnees sensibles ;
9. ajouter des tests backend cibles ;
10. mettre a jour `docs/api.md`, `docs/security.md`, `docs/architecture.md` ou `mobile/README.md` si le changement les concerne ;
11. verifier `phpunit`, `lint:container`, `doctrine:schema:validate --skip-sync` et `npx tsc --noEmit` si le mobile est touche ;
12. merger vers `develop`, attendre CI verte, puis merger vers `main` uniquement pour une version stable.

## Comment L'Expliquer Au Jury CDA

Formulation courte :

> J'ai choisi une architecture en couches orientee Use Cases, inspiree Clean Architecture et adaptee a Symfony. Les controleurs recoivent les requetes, les DTO valident les entrees, les use cases portent la logique applicative, le domaine contient les entites et les contrats, et l'infrastructure gere Doctrine, l'API football et le WebSocket.

Points a defendre :

- **separation des responsabilites** : chaque couche a un role clair ;
- **securite** : les droits sont verifies cote backend, notamment via JWT, roles et voters ;
- **testabilite** : les use cases peuvent etre testes sans passer par l'interface mobile ;
- **maintenabilite** : une feature se range dans un domaine clair : Feed, Messaging, Football, Admin, User ;
- **pragmatisme** : le projet evite DDD strict, hexagonal complet, microservices et patterns lourds ;
- **coherence produit** : l'architecture soutient une application sociale football reelle, avec feed, messagerie, football, profil et admin.

Phrase de synthese :

> LeKlub n'est pas une demonstration technique jetable : c'est un MVP structure, securise et extensible, construit avec une architecture assez propre pour etre maintenue, mais assez simple pour etre livree et defendue dans le cadre d'un titre CDA.
