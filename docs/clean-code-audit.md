# Audit SOLID / Clean Code

## 1. Résumé exécutif

LeKlub repose sur une base globalement saine pour un projet CDA : le backend suit bien l'architecture officielle du projet, c'est-à-dire une architecture en couches orientée Use Cases, inspirée Clean Architecture et adaptée à Symfony. Le mobile Expo est également structuré de façon lisible, avec une séparation claire entre écrans, composants, services API, contextes, navigation et types.

Aucun problème critique bloquant n'a été identifié pendant cet audit. Le projet est démontrable, maintenable et cohérent avec les choix annoncés dans la documentation.

Les principaux points de vigilance sont de niveau moyen :

- certains écrans mobiles et composants sont devenus longs avec l'enrichissement progressif du MVP ;
- quelques controllers Symfony répètent la même mécanique de lecture JSON, validation, récupération utilisateur et mapping d'erreurs ;
- certains use cases admin, surtout la résolution de signalements, orchestrent beaucoup de règles métier en une seule classe ;
- les presenters admin commencent à regrouper plusieurs formats de réponse ;
- quelques règles transverses existent en double côté mobile, notamment le formatage des dates et certains feedbacks utilisateur.

La recommandation principale est de ne pas lancer de refonte massive avant la soutenance. Les corrections utiles doivent rester ciblées : extraire quelques helpers, clarifier les responsabilités les plus chargées, consolider les duplications simples, et documenter les compromis assumés.

## 2. État global backend

Le backend respecte bien la séparation attendue :

- `src/Controller/Api/` reçoit les requêtes HTTP, délègue aux DTO et aux use cases, puis retourne une réponse API ;
- `src/DTO/` porte la validation d'entrée ;
- `src/Application/` contient les cas d'utilisation et l'orchestration applicative ;
- `src/Domain/` contient les entités, exceptions, interfaces de repositories et value objects ;
- `src/Infrastructure/` contient Doctrine, Football, WebSocket, Mailer et Storage ;
- `src/Security/` contient les voters et subscribers liés à l'authentification JWT ;
- `src/Shared/Api/` contient les helpers communs de réponse et pagination.

Les controllers sont majoritairement légers. Ils ne contiennent pas de logique métier lourde, mais ils répètent souvent la même plomberie technique : `json_decode`, validation DTO, récupération de l'utilisateur authentifié, `try/catch`, et transformation d'exceptions en réponses API.

Les use cases sont globalement lisibles. Les règles de sécurité importantes restent côté backend : ownership des posts/commentaires, contrôle conversation, suspension, rôles admin, refresh token, reset password, signalements, modération et upload avatar.

Les entités Doctrine sont placées dans `Domain/Entity`, ce qui correspond au compromis documenté du projet : elles représentent le modèle métier tout en portant les attributs Doctrine. Ce n'est pas du DDD strict, mais c'est adapté au niveau CDA et évite une surcouche inutile.

## 3. État global mobile

Le mobile est correctement organisé :

- `screens/` porte les écrans par domaine fonctionnel ;
- `components/` contient les composants UI et métier réutilisables ;
- `services/` isole les appels API, le stockage des tokens et le WebSocket ;
- `contexts/` gère l'authentification et le compteur de messages non lus ;
- `navigation/` sépare les stacks et la tab bar ;
- `types/` documente les contrats côté TypeScript ;
- `config/` centralise l'environnement et le thème ;
- `utils/` contient la validation de mot de passe.

Le choix de ne pas utiliser Redux/Zustand reste cohérent : l'état global est limité à l'authentification et au compteur de messages non lus. Les écrans gardent leur état local, ce qui simplifie la défense du projet.

Le principal risque mobile est la taille de certains écrans et composants. Plusieurs fichiers mélangent chargement API, états de formulaire, feedback UX, rendu et formatage. Ce n'est pas bloquant, mais cela rendra les futures évolutions plus coûteuses si le produit continue à grandir.

## 4. Points forts

- Architecture backend claire et défendable devant un jury.
- Use cases explicites pour les fonctionnalités sensibles : auth, reset password, refresh token, feed, messagerie, admin, suspension, avatar.
- Séparation nette entre repositories interfaces côté domaine et repositories Doctrine côté infrastructure.
- DTO Symfony avec contraintes de validation sur les entrées importantes.
- Voters utilisés pour les ressources sensibles : posts, commentaires, conversations.
- Présenters dédiés pour éviter de retourner directement les entités Doctrine ou les réponses externes.
- API football normalisée côté backend au lieu d'exposer la réponse brute de football-data.org.
- Refresh token implémenté maison avec hash en base, rotation et révocation.
- Reset password avec token hashé, expiration et Mailpit local.
- Upload avatar avec validation MIME, extension, taille et nommage aléatoire.
- Mobile structuré par domaines fonctionnels, sans state manager lourd.
- SecureStore utilisé pour les tokens côté mobile.
- WebSocket limité au rôle de notification, REST restant source de vérité.
- CI GitHub Actions déjà en place et utile pour backend/mobile.

## 5. Problèmes critiques

Aucun problème critique bloquant n'a été identifié dans le périmètre Clean Code / SOLID.

Le projet ne nécessite pas de refonte urgente avant la soutenance. Les risques relevés sont principalement des risques de maintenabilité progressive, pas des défauts structurels qui invalident l'architecture.

## 6. Problèmes moyens

### Controllers API avec plomberie répétée

Fichiers représentatifs :

- `backend/src/Controller/Api/Feed/FeedController.php`
- `backend/src/Controller/Api/Feed/CommentController.php`
- `backend/src/Controller/Api/Feed/ReportController.php`
- `backend/src/Controller/Api/Messaging/MessageController.php`
- `backend/src/Controller/Api/Admin/AdminUserController.php`

Constat : les controllers restent légers côté métier, mais ils répètent beaucoup la gestion JSON, la validation DTO, `currentUser()`, et la conversion d'exceptions en réponses API.

Risque : incohérences futures de messages d'erreur, duplication de code, controllers plus longs à mesure que l'API grandit.

Correction ciblée possible : créer un petit helper de lecture/validation JSON ou standardiser une méthode privée par controller seulement là où la duplication est forte. Éviter un système abstrait trop générique.

### Use case admin de résolution des signalements chargé

Fichier :

- `backend/src/Application/Admin/ResolveFeedReportUseCase.php`

Constat : ce use case orchestre la décision admin, la suppression logique du contenu, la création d'avertissement, la suspension automatique et les notifications système.

Risque : classe difficile à faire évoluer si la modération devient plus riche.

Correction ciblée possible : extraire plus tard une petite politique de sanction, par exemple `WarningSuspensionPolicy`, et un service de notifications de modération. Ne pas refactorer avant soutenance si le comportement est stable.

### Presenter admin en croissance

Fichier :

- `backend/src/Application/Admin/AdminPresenter.php`

Constat : le presenter admin formate les utilisateurs, posts, commentaires, signalements et avertissements.

Risque : la classe peut devenir un point central trop chargé si le back office continue à grandir.

Correction ciblée possible : scinder en `AdminUserPresenter`, `AdminContentPresenter`, `AdminReportPresenter` uniquement si de nouveaux formats sont ajoutés.

### Écrans mobiles longs et riches

Fichiers représentatifs :

- `mobile/src/screens/Feed/FeedScreen.tsx`
- `mobile/src/screens/Feed/PostDetailScreen.tsx`
- `mobile/src/screens/Football/FootballCompetitionScreen.tsx`
- `mobile/src/screens/Profile/EditProfileScreen.tsx`
- `mobile/src/screens/Admin/AdminUsersScreen.tsx`
- `mobile/src/screens/Messaging/ConversationDetailScreen.tsx`

Constat : plusieurs écrans gèrent à la fois chargement API, état local, erreurs, formulaires, confirmations et rendu.

Risque : les futures évolutions peuvent créer de la duplication ou rendre les écrans plus difficiles à tester manuellement.

Correction ciblée possible : extraire progressivement de petits hooks locaux et lisibles, par exemple `useFeedPosts`, `usePostDetail`, `useAdminUsers`, ou des sous-composants de formulaire. Ne pas créer une couche abstraite globale.

### Composants mobiles métier un peu chargés

Fichiers représentatifs :

- `mobile/src/components/feed/PostCard.tsx`
- `mobile/src/components/feed/CommentCard.tsx`
- `mobile/src/components/admin/AdminUserCard.tsx`
- `mobile/src/components/admin/AdminReportCard.tsx`

Constat : ces composants combinent rendu, menus d'action, confirmations et parfois formulaire inline.

Risque : le composant devient plus difficile à faire évoluer si de nouvelles actions arrivent.

Correction ciblée possible : conserver la logique actuelle pour la soutenance, puis extraire les confirmations répétées ou les blocs d'actions si le besoin revient.

### Plusieurs connexions WebSocket côté mobile

Fichiers :

- `mobile/src/contexts/MessagingUnreadContext.tsx`
- `mobile/src/screens/Messaging/ConversationListScreen.tsx`
- `mobile/src/screens/Messaging/ConversationDetailScreen.tsx`
- `mobile/src/hooks/useMessagingSocket.ts`

Constat : le hook WebSocket est simple et clair, mais il est utilisé à plusieurs endroits. Cela peut ouvrir plusieurs connexions selon l'écran actif.

Risque : comportement acceptable en MVP, mais moins propre pour une application publiée avec plus de trafic.

Correction ciblée possible : centraliser une seule connexion WebSocket dans un provider messagerie, puis exposer les événements aux écrans. À reporter après soutenance si le système actuel reste stable.

### Formatage de dates répété côté mobile

Fichiers représentatifs :

- `mobile/src/components/feed/PostCard.tsx`
- `mobile/src/components/feed/CommentCard.tsx`
- `mobile/src/components/messaging/MessageBubble.tsx`
- `mobile/src/components/admin/AdminReportCard.tsx`
- `mobile/src/components/admin/AdminUserCard.tsx`
- `mobile/src/screens/Profile/NotificationsScreen.tsx`

Constat : plusieurs fonctions `formatDate` / `formatMessageDate` existent localement.

Risque : incohérence d'affichage et duplication simple.

Correction ciblée possible : créer `mobile/src/utils/dateFormat.ts` avec deux fonctions maximum : date courte et heure message.

### Repositories Doctrine multifonctions

Fichiers représentatifs :

- `backend/src/Infrastructure/Doctrine/Repository/PostRepository.php`
- `backend/src/Infrastructure/Doctrine/Repository/CommentRepository.php`
- `backend/src/Infrastructure/Doctrine/Repository/UserRepository.php`

Constat : certains repositories implémentent plusieurs interfaces, par exemple usage utilisateur, stats admin et modération.

Risque : à long terme, les repositories peuvent devenir des points de concentration de requêtes.

Correction ciblée possible : ne pas diviser maintenant. Si l'admin grandit, créer des repositories dédiés lecture admin, sans changer le domaine.

## 7. Problèmes faibles

### Vocabulaire français / anglais mélangé

Exemples :

- `Commentaire` côté TypeScript ;
- noms de variables et méthodes principalement anglais ;
- libellés UI en français.

Impact faible : le code reste compréhensible. Pour un projet CDA français, ce mélange n'est pas bloquant, mais il faudrait éviter d'ajouter de nouveaux types français isolés.

### Messages de feedback parfois dispersés

Exemples :

- `Alert.alert` dans plusieurs composants ;
- messages de succès/erreur parfois locaux à l'écran ;
- libellés UI directement dans les composants.

Impact faible : acceptable en MVP. Une centralisation complète serait excessive avant soutenance.

### `apiClient` contient des effets de session implicites

Fichier :

- `mobile/src/services/api/apiClient.ts`

Constat : l'interceptor gère le refresh token, la rotation et le nettoyage local si le refresh échoue.

Impact faible à moyen : c'est pratique et centralisé, mais il faut documenter ce comportement car une requête 401 peut entraîner une modification de session.

### Fixtures volumineuses

Fichier :

- `backend/src/DataFixtures/AppFixtures.php`

Constat : une seule fixture avec méthodes privées était un choix validé, mais le fichier est long.

Impact faible : ce fichier sert la démo. Il ne justifie pas une correction immédiate tant que les données restent cohérentes.

### Client football manuel

Fichier :

- `backend/src/Infrastructure/Football/ExternalFootballDataClient.php`

Constat : la normalisation est manuelle et assez longue.

Impact faible : c'est volontaire, lisible et défendable. Ne pas ajouter de mapper complexe tant que le périmètre football reste limité.

## 8. Recommandations priorisées

### Priorité 1 : ne pas refactorer massivement avant soutenance

Le projet est stable et fonctionnel. Une refonte globale maintenant augmenterait le risque de régression sans bénéfice CDA proportionnel.

### Priorité 2 : extraire les duplications très simples

Corrections utiles, courtes et peu risquées :

- centraliser le formatage des dates côté mobile ;
- harmoniser quelques confirmations répétées si elles évoluent encore ;
- documenter le comportement du refresh token mobile dans la documentation sécurité.

### Priorité 3 : surveiller les classes admin les plus riches

Si une nouvelle fonctionnalité admin est ajoutée :

- éviter d'agrandir encore `ResolveFeedReportUseCase` ;
- éviter d'ajouter trop de méthodes à `AdminPresenter` ;
- préférer de petits collaborateurs nommés clairement.

### Priorité 4 : alléger progressivement les écrans mobiles longs

À faire uniquement si une nouvelle évolution touche l'écran concerné :

- extraire un hook local de chargement ;
- extraire un sous-composant de formulaire ;
- garder les services API séparés.

### Priorité 5 : centraliser le WebSocket mobile plus tard

Le fonctionnement actuel est acceptable en MVP. Une seule connexion WebSocket globale serait plus propre pour une version publiée, mais ce n'est pas indispensable avant soutenance.

## 9. Corrections proposées pour une prochaine étape

### Correction courte 1 : `mobile/src/utils/dateFormat.ts`

Objectif : supprimer les fonctions de formatage répétées.

Fichiers concernés :

- `PostCard.tsx`
- `CommentCard.tsx`
- `MessageBubble.tsx`
- `AdminReportCard.tsx`
- `AdminUserCard.tsx`
- `NotificationsScreen.tsx`

Risque : faible.

### Correction courte 2 : helper léger de validation JSON backend

Objectif : réduire la duplication dans les controllers sans changer l'architecture.

Fichiers concernés :

- controllers `Auth`, `User`, `Feed`, `Messaging`, `Admin`
- éventuellement un petit helper dans `Shared/Api/`

Risque : moyen si appliqué trop largement. À faire progressivement.

### Correction courte 3 : extraire la politique avertissement/suspension

Objectif : alléger `ResolveFeedReportUseCase`.

Fichiers concernés :

- `ResolveFeedReportUseCase.php`
- nouveau service applicatif court, par exemple `ApplyUserWarningPolicy`

Risque : moyen, car cela touche la modération. À faire uniquement avec tests.

### Correction courte 4 : hook local pour Feed

Objectif : alléger `FeedScreen` sans refonte.

Fichiers concernés :

- `FeedScreen.tsx`
- nouveau hook local si nécessaire, par exemple `useFeedPosts.ts`

Risque : faible à moyen.

### Correction courte 5 : provider WebSocket unique

Objectif : éviter plusieurs connexions WebSocket côté mobile.

Fichiers concernés :

- `MessagingUnreadContext.tsx`
- `useMessagingSocket.ts`
- écrans messagerie

Risque : moyen. À reporter si le temps est court.

## 10. Ce qu'il ne faut pas refactorer avant soutenance

- Ne pas transformer LeKlub en architecture hexagonale complète.
- Ne pas sortir toutes les entités Doctrine du domaine.
- Ne pas remplacer Symfony Security / LexikJWT par un système maison.
- Ne pas remplacer le WebSocket MVP par une architecture temps réel distribuée.
- Ne pas ajouter Redux, Zustand ou un state manager global lourd.
- Ne pas découper tous les screens mobiles uniquement pour réduire le nombre de lignes.
- Ne pas créer un design system abstrait avec des variants non utilisés.
- Ne pas réécrire les repositories Doctrine tant que les requêtes restent lisibles.
- Ne pas ajouter OpenAPI/codegen avant la soutenance si les contrats actuels sont documentés.
- Ne pas modifier les fonctionnalités validées uniquement pour atteindre une pureté théorique.

## Zones inspectées

Backend inspecté :

- `backend/src/Controller/Api/`
- `backend/src/DTO/`
- `backend/src/Application/`
- `backend/src/Domain/Entity/`
- `backend/src/Domain/Repository/`
- `backend/src/Domain/ValueObject/`
- `backend/src/Infrastructure/Doctrine/Repository/`
- `backend/src/Infrastructure/Football/`
- `backend/src/Infrastructure/Security/`
- `backend/src/Infrastructure/Storage/`
- `backend/src/Infrastructure/WebSocket/`
- `backend/src/Security/EventSubscriber/`
- `backend/src/Security/Voter/`
- `backend/src/Shared/Api/`

Mobile inspecté :

- `mobile/src/screens/`
- `mobile/src/components/`
- `mobile/src/services/`
- `mobile/src/hooks/`
- `mobile/src/contexts/`
- `mobile/src/navigation/`
- `mobile/src/types/`
- `mobile/src/config/`
- `mobile/src/utils/`

Vérifications complémentaires :

- recherche des fichiers longs backend/mobile ;
- recherche de `TODO`, `FIXME`, `console.log`, `@ts-ignore`, `any`, `Alert.alert`, `throw new`, `catch` ;
- inspection ciblée des fichiers les plus sensibles en maintenabilité.
