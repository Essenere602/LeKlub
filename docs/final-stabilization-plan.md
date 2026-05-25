# Plan de stabilisation finale CDA

## 1. Résumé global de l'état du projet

LeKlub est dans un état solide pour une soutenance CDA : le backend Symfony expose une API structurée, testée et sécurisée ; l'application mobile Expo couvre les parcours principaux ; Docker, CI GitHub Actions et la documentation projet sont en place.

L'architecture officielle reste : **architecture en couches orientée Use Cases, inspirée Clean Architecture, adaptée à Symfony**. Les dernières étapes ont renforcé les zones critiques : refresh token, reset password avec Mailpit, upload avatar, rate limiting, audit Clean Code, audit sécurité et documentation de déploiement.

Cette phase ne doit pas ajouter de nouvelle fonctionnalité. Elle doit uniquement corriger les derniers irritants visibles avant soutenance : cohérence UX, messages, états, robustesse mobile et petits oublis d'intégration.

## 2. Points déjà solides

### Backend

- Authentification JWT avec refresh token stocké hashé côté serveur.
- Rotation des refresh tokens et révocation au logout, changement de mot de passe et reset password.
- Reset password via Mailpit en local, token hashé, expiration et usage unique.
- Rate limiting sur les endpoints auth sensibles.
- Upload avatar validé : taille, MIME, extension, nom aléatoire, stockage hors Git.
- Signalements, modération admin, avertissements et suspensions temporaires.
- Messagerie privée protégée, suppression pour soi et WebSocket utilisé uniquement comme notification.
- Fixtures de démonstration cohérentes.
- Tests backend nombreux et CI verte.

### Mobile

- Navigation par tabs claire : Accueil, Feed, Football, Messages, Profil, Admin si ROLE_ADMIN.
- Stockage des tokens via Expo Secure Store.
- Gestion refresh token avec verrou anti-refresh concurrent.
- Feed complet : création, modification, suppression, réactions, commentaires, signalements.
- Messagerie fonctionnelle : conversations, temps réel, badge non lu, suppression pour soi.
- Football lisible avec journées, classements, buteurs, logos et drapeaux.
- Compte complet : profil, avatar, changement mot de passe, modification email/username.
- Admin mobile sérieux : utilisateurs, modération, signalements, avertissements, rôles, suspensions.
- Design system léger existant : `Screen`, `AppCard`, `AppButton`, `AppInput`, `AppBadge`, états loading/empty/error.

### Documentation / DevOps

- Documentation architecture, sécurité, API, base de données, déploiement.
- Diagrammes Mermaid pour la conception.
- Docker local complet : Symfony, Nginx, MySQL, WebSocket, Mailpit.
- CI GitHub Actions backend/mobile verte.
- `.env.example` et `mobile/.env.example` documentés sans secret réel.

## 3. Points faibles encore visibles avant soutenance

### Mobile - UX globale

1. **Avatar non affiché sur la Home**
   - La Home affiche encore une initiale locale alors que l'upload avatar est disponible partout ailleurs.
   - Impact : incohérence visible juste après connexion.
   - Correction faible risque : réutiliser le composant avatar déjà utilisé en Feed/Messages/Admin.

2. **Avatar du composer Feed encore en fallback**
   - Le composer de publication utilise l'initiale, même si l'utilisateur a une photo de profil.
   - Impact : le Feed, centre émotionnel du produit, perd en cohérence sociale.

3. **Certains écrans utilisent encore des états custom**
   - `ConversationListScreen`, `ConversationDetailScreen`, `UserPickerScreen` utilisent encore des `ActivityIndicator` ou empty states locaux.
   - Impact modéré : perception moins homogène, mais fonctionnalités OK.

4. **Texte WebSocket légèrement ambigu**
   - Le badge affiche parfois "Temps réel actif" dès `connected`, alors que l'état réellement authentifié est `authenticated`.
   - Impact faible : wording à corriger sans toucher au WebSocket.

5. **Padding bas à vérifier sur quelques listes**
   - Les écrans longs sous tab bar doivent garder un `paddingBottom` suffisant.
   - À vérifier notamment : Notifications, listes admin, Messages.

6. **Feedbacks encore parfois en `Alert.alert`**
   - Les confirmations destructives sont justifiées.
   - Certains succès pourraient être plus discrets, mais ce n'est pas bloquant avant soutenance.

### Mobile - Auth/session

1. **Expiration session : UX à vérifier seulement**
   - Le refresh token a été validé.
   - Si refresh échoue, les tokens sont nettoyés ; il faut vérifier que le retour Login reste compréhensible côté utilisateur.
   - Pas de changement profond recommandé.

2. **Erreurs réseau**
   - Le message centralisé est clair : "Impossible de joindre l'API LeKlub...".
   - À garder, mais vérifier qu'il n'apparaît pas pour une erreur métier classique.

### Backend

1. **WebSocket production minimaliste**
   - La sécurité actuelle est suffisante pour MVP : auth JWT à la connexion, messages REST comme source de vérité.
   - Limite assumée : pas de reconnexion avancée, pas de blacklist JWT côté WebSocket, pas de scale horizontal.
   - À ne pas refondre avant soutenance.

2. **JWT stateless**
   - Un access token peut rester valide jusqu'à expiration même après révocation du refresh token.
   - Limite normale et déjà documentée.
   - Ne pas ajouter de blacklist JWT avant soutenance.

3. **Upload avatar sans re-encodage**
   - Validation stricte déjà présente.
   - Antivirus/re-encodage à garder post-soutenance.

4. **Erreurs API**
   - Les endpoints sensibles ont été durcis.
   - Les erreurs métier authentifiées restent explicites, ce qui est souhaitable pour l'UX.

## 4. Corrections prioritaires recommandées

### Priorité 1 - Très faible risque, forte cohérence visuelle

1. **Afficher l'avatar uploadé sur HomeScreen**
   - Zone : `mobile/src/screens/Home/HomeScreen.tsx`.
   - Objectif : remplacer le fallback local par le composant avatar réutilisable.
   - Tests : iPhone admin et user standard, avatar présent et fallback sans avatar.

2. **Afficher l'avatar uploadé dans le composer Feed**
   - Zone : `mobile/src/screens/Feed/FeedScreen.tsx`.
   - Objectif : rendre le Feed cohérent avec `PostCard` et `CommentCard`.
   - Tests : Feed avec avatar uploadé, création de post, fallback sans avatar.

3. **Corriger le wording du badge temps réel**
   - Zones : `ConversationListScreen`, `ConversationDetailScreen`.
   - Objectif : afficher "Temps réel actif" seulement pour `authenticated`.
   - Tests : ouvrir Messages, recevoir un message WebSocket, vérifier badge.

4. **Uniformiser les états Messaging les plus visibles**
   - Zones : `ConversationListScreen`, `ConversationDetailScreen`, `UserPickerScreen`.
   - Objectif : réutiliser `LoadingState`, `EmptyState`, `ErrorState` là où le changement est local.
   - Tests : liste vide, chargement, erreur réseau volontaire si possible.

### Priorité 2 - Utile si temps disponible

1. **Vérifier et ajuster le padding bas des listes sous tab bar**
   - Zones : `NotificationsScreen`, listes admin, Messages.
   - Objectif : aucun dernier élément masqué par la tab bar.
   - Tests : iPhone petit écran, compte admin avec beaucoup de données.

2. **Harmoniser les messages succès non destructifs**
   - Zones : Feed signalement, profil, notifications.
   - Objectif : éviter les alertes trop agressives pour les succès simples.
   - Risque : faible, mais peut prendre du temps visuel.

3. **Relecture wording FR**
   - Zones : Auth, Feed, Messages, Admin, Football.
   - Objectif : tutoiement/vouvoiement cohérent et messages courts.
   - Risque : faible, mais attention à ne pas modifier les messages API sensibles.

### Priorité 3 - À documenter ou reporter

1. **Reconnexion WebSocket avancée**
   - Reporter post-soutenance.
   - Le MVP actuel est stable et validé iPhone.

2. **Offline mode**
   - Reporter post-soutenance.
   - Garder seulement les erreurs réseau compréhensibles.

3. **Pagination mobile plus poussée**
   - Reporter post-soutenance.
   - Les flows actuels sont suffisants pour démo CDA.

4. **Scan/re-encodage avatars**
   - Reporter post-soutenance.
   - La validation actuelle est défendable pour MVP.

## 5. Corrections optionnelles

- Ajouter une micro-section "Version actuelle" dans Compte seulement si elle aide la démo, sans exposer de données techniques.
- Réduire quelques textes longs dans Admin pour améliorer la lecture en 3 secondes.
- Vérifier que les boutons destructifs sont toujours en variante danger et confirmés.
- Remplacer les derniers `ActivityIndicator` directs uniquement si cela ne complexifie pas les écrans.
- Ajouter un fallback visuel homogène si une image avatar échoue au chargement dans Home/Profile.

## 6. Risques de régression

### Risques élevés à éviter

- Modifier la logique auth, refresh token ou Secure Store.
- Changer les routes API ou les contrats TypeScript.
- Modifier les règles de rate limiting.
- Toucher aux migrations ou aux entités sans besoin.
- Refondre le WebSocket.
- Refondre Admin ou Feed alors que les tests iPhone sont validés.

### Risques moyens

- Changer les layouts des écrans sous tab bar sans tester iPhone réel.
- Remplacer des états custom par des composants génériques avec des tailles différentes.
- Modifier les messages d'erreur auth et dégrader la sécurité ou l'UX.

### Risques faibles

- Réutiliser un composant avatar existant.
- Ajuster des labels/badges.
- Ajouter un `paddingBottom` ciblé sur une liste qui manque d'espace.

## 7. Ordre d'exécution conseillé

1. **Passe Avatar cohérence**
   - HomeScreen.
   - Composer Feed.
   - Test iPhone rapide : Compte, Home, Feed.

2. **Passe Messaging légère**
   - Badge temps réel.
   - Loading/empty states visibles.
   - Test iPhone : liste conversations, détail conversation, WebSocket.

3. **Passe Scroll/tab bar**
   - Notifications.
   - Admin listes.
   - Messages.
   - Test iPhone petit écran.

4. **Passe Wording**
   - Relire Auth, Compte, Feed, Admin.
   - Ne pas toucher aux erreurs sensibles backend sauf fuite réelle.

5. **Vérifications finales**
   - `npx tsc --noEmit`.
   - `composer validate --strict`.
   - `php bin/console lint:container`.
   - `php bin/console doctrine:schema:validate --skip-sync`.
   - `php bin/phpunit`.
   - `composer audit`.
   - `npm audit --omit=dev --audit-level=high`.
   - Tests iPhone finaux sur parcours démo.

## 8. Ce qu'il ne faut surtout plus toucher avant soutenance

- Les règles métier validées : feed, commentaires, réactions, signalements, sanctions, rôles admin, suspensions.
- La stratégie auth : JWT court, refresh token, reset password, rate limiting.
- La base de données et les migrations, sauf bug critique.
- Les services API football et l'intégration football-data.org.
- La structure Docker, Mailpit, Nginx, WebSocket.
- La navigation principale.
- Les gros écrans Admin déjà validés.
- Les documents d'architecture, sécurité, déploiement, sauf correction factuelle.

## Recommandation finale

Pour la soutenance, la meilleure stratégie est de faire une **passe courte de polish visible**, puis de figer le produit.

Les corrections les plus rentables sont :

1. avatar Home + composer Feed ;
2. wording WebSocket ;
3. états Messaging homogènes ;
4. vérification scroll/tab bar ;
5. relecture wording FR.

Tout le reste doit rester documenté comme limite MVP ou amélioration post-soutenance. À ce stade, la stabilité vaut plus qu'une nouvelle ambition fonctionnelle.
