# Architecture

LeKlub utilise une architecture en couches simple, lisible et défendable pour un projet CDA.

## Backend

- Controller / API
- Application / Use Case
- Domain
- Infrastructure

Structure Symfony :

```text
backend/src/
  Controller/Api/
  Application/
    Admin/
    Auth/
    User/
    Feed/
    Messaging/
    Football/
  Domain/
    Entity/
    Repository/
    Exception/
    ValueObject/
  Infrastructure/
    Doctrine/
    Football/
    WebSocket/
  DTO/
    Auth/
    Feed/
    Messaging/
    User/
  Security/Voter/
  Security/EventSubscriber/
  Shared/Api/
```

## Pourquoi Les Entités Doctrine Sont Dans `Domain/Entity`

Les entités principales sont placées dans `Domain/Entity` parce qu'elles représentent les objets métier manipulés par l'application : `User`, `UserProfile`, `Post`, `Comment`, `PostReaction`, `Conversation` et `Message`.

Ce choix reste volontairement simple :

- les entités portent les données et quelques règles métier locales faciles à comprendre
- les contrôleurs ne manipulent pas directement la base de données
- les cas d'utilisation travaillent avec des objets métier explicites
- les repositories Doctrine restent dans `Infrastructure/Doctrine/Repository`
- les interfaces de repositories restent dans `Domain/Repository`

Le projet n'essaie pas de faire une architecture hexagonale abstraite ou complexe. Les attributs Doctrine sont acceptés dans les entités pour rester cohérent avec Symfony et Doctrine, tout en conservant une séparation claire entre :

- la logique applicative dans `Application`
- les objets métier dans `Domain`
- les détails techniques de persistance dans `Infrastructure`

Ce compromis est adapté à un MVP CDA : il est maintenable, simple à expliquer et évite la sur-ingénierie.

## Feed

Le feed suit la même séparation :

- `Controller/Api/Feed` reçoit les requêtes HTTP et retourne des réponses JSON.
- `Application/Feed` orchestre les cas d'utilisation : créer un Post, lister le feed, ajouter un Commentaire, gérer une Réaction.
- `Domain/Entity` contient `Post`, `Comment` et `PostReaction`.
- `Domain/ValueObject` contient les valeurs métier simples comme le type de Réaction.
- `Security/Voter` centralise les droits auteur/admin.
- `Infrastructure/Doctrine/Repository` contient les requêtes Doctrine.

## Administration

Le Back Office Admin suit la même architecture en couches :

- `Controller/Api/Admin` expose les endpoints `/api/admin/*`.
- `Application/Admin` contient les cas d'utilisation : synthèse, liste utilisateurs, liste/modération Posts et Commentaires.
- Les repositories existants sont enrichis avec quelques méthodes admin ciblées, plutôt que créer une couche parallèle inutile.
- `Infrastructure/Doctrine/Repository` garde les requêtes Doctrine concrètes.

Ce choix est volontairement pragmatique pour le MVP CDA :

- pas de dashboard complexe
- pas de permissions avancées
- pas de lecture des Messages privés
- pas de suppression physique utilisateur
- suppression logique existante pour la modération

La séparation reste claire : le contrôleur reçoit la requête, le use case orchestre, le repository accède aux données et le presenter admin normalise les réponses.

## Messagerie

La messagerie suit la même séparation que le reste du backend :

- `Controller/Api/Messaging` reçoit les requêtes HTTP et retourne les réponses JSON.
- `Application/Messaging` orchestre les cas d'utilisation : lister, créer, envoyer, marquer comme lu.
- `Domain/Entity` contient `Conversation` et `Message`.
- `Domain/Repository` déclare les contrats attendus par l'application.
- `Infrastructure/Doctrine/Repository` contient les requêtes Doctrine.
- `Security/Voter/ConversationVoter` centralise le contrôle d'accès aux Conversations privées.
- `Infrastructure/WebSocket` contient le serveur temps réel et le notificateur technique.

Cette organisation permet d'expliquer clairement où se trouvent les règles d'accès, la logique applicative et les détails techniques.

## Football

L'intégration football est volontairement en lecture seule dans le MVP :

- `Controller/Api/Football` expose les endpoints REST.
- `Application/Football` vérifie les championnats supportés, limite les résultats et orchestre les appels.
- `Domain/ValueObject/FootballCompetition` contient la liste des championnats autorisés dans le MVP.
- `Infrastructure/Football` contient le client HTTP vers football-data.org.

Le contrôleur ne renvoie jamais la réponse brute de l'API externe. Le client d'infrastructure transforme les données externes en tableaux normalisés adaptés au mobile.

Ce choix permet de démontrer une intégration d'API externe sans ajouter de persistance inutile en base de données.

L'interface du client football reste dans `Infrastructure/Football` par choix pragmatique MVP : elle décrit un service technique externe et évite d'ajouter une abstraction de domaine plus lourde pour un besoin en lecture seule.

De la même manière, l'interface de notification WebSocket reste dans `Infrastructure/WebSocket`. Les cas d'utilisation de messagerie en dépendent pour déclencher une notification, mais le choix reste volontairement simple et défendable : le temps réel est un détail technique local au MVP, pas un sous-domaine métier complexe.

## Frontend

Structure prévue :

- `screens/`
- `components/`
- `navigation/`
- `services/`
- `hooks/`
- `contexts/`
- `types/`
- `config/`

Le frontend doit garder la logique d'appel API dans des services dédiés et éviter de mélanger les composants visuels avec la logique réseau ou l'authentification.
