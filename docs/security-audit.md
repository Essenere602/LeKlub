# Audit securite LeKlub

Date : 25 mai 2026  
Branche : `feature/security-audit`  
Perimetre : backend Symfony, mobile Expo, Docker, CI, configuration, dependances, flux auth, feed, messagerie, moderation, upload avatar, reset password, refresh token.

## 1. Resume executif

LeKlub dispose d'une base securite solide pour un MVP CDA : API stateless protegee par JWT, refresh tokens hashes et rotatifs, reset password avec token hash en base, stockage mobile via Expo SecureStore, autorisations metier par voters/use cases, moderation admin sans lecture des messages prives, validation DTO et separation des couches.

L'audit ne met pas en evidence de fuite de secret versionne : `.env`, `mobile/.env`, uploads et cles JWT locales sont ignores par Git. Les cles JWT presentes localement dans `backend/config/jwt/` ne sont pas suivies par Git.

Les risques les plus importants avant soutenance sont :

- dependances backend Symfony avec avis de securite remontes par `composer audit` ;
- absence de limitation de debit sur login, reset password, refresh token et actions sensibles ;
- token de reset password journalise en environnement `dev` comme fallback local ;
- WebSocket et CORS encore penses pour le local, pas pour une exposition production.

Conclusion : le projet est defensible en MVP local/demo, mais il faut corriger les dependances vulnerables et documenter les limites de mise en production avant de presenter LeKlub comme publiable.

## 2. Surface d'attaque du projet

### Backend API

- Authentification : `/api/auth/register`, `/api/auth/login`, `/api/auth/refresh`, `/api/auth/logout`, reset password.
- Compte utilisateur : `/api/me`, `/api/me/profile`, `/api/me/account`, `/api/me/password`, `/api/me/avatar`.
- Feed : posts, commentaires, reactions, signalements.
- Messagerie : conversations, messages, marquage lu, suppression pour soi.
- Admin : utilisateurs, roles, suspensions, moderation, signalements, avertissements.
- Football : endpoints de lecture vers football-data.org avec token serveur.
- Fichiers statiques : avatars dans `backend/public/uploads/avatars/`.
- WebSocket : notification simple de nouveau message.

### Mobile Expo

- Stockage des tokens dans SecureStore.
- Client API avec refresh automatique.
- Navigation protegee selon authentification et role admin.
- Upload avatar via image picker.
- WebSocket avec token JWT envoye dans un message d'authentification, pas dans l'URL.
- Variables publiques Expo : URL API et URL WebSocket uniquement, pas de secret.

### Infrastructure

- Docker : Nginx, PHP/Symfony, MySQL, WebSocket, Mailpit.
- CI GitHub Actions : installation, validation Composer, lint container, validation Doctrine, phpunit, TypeScript mobile.
- Environnements : `.env.example`, `backend/.env.test`, `backend/.env.dev`, variables CI factices.

## 3. Points forts securite

- JWT access token court : `token_ttl: 900` soit 15 minutes.
- Refresh token maison :
  - token brut jamais stocke en base ;
  - hash SHA-256 stocke ;
  - expiration 30 jours ;
  - rotation a chaque refresh ;
  - revocation au logout ;
  - revocation apres changement de mot de passe et reset password.
- Reset password :
  - reponse generique pour limiter l'enumeration email ;
  - token brut non retourne par l'API ;
  - token hash en base ;
  - expiration 30 minutes ;
  - usage unique ;
  - anciens tokens invalides a nouvelle demande.
- Mobile :
  - access token et refresh token stockes dans SecureStore ;
  - pas d'AsyncStorage pour les tokens ;
  - verrou anti-refresh concurrent cote client.
- Controle d'acces :
  - routes publiques limitees aux endpoints auth/health/reset ;
  - `/api/admin/*` protege par `ROLE_ADMIN` ;
  - voters pour posts, commentaires, conversations ;
  - use cases metier pour ownership et moderation.
- Messagerie :
  - admin ne lit pas les messages prives ;
  - WebSocket sert uniquement a notifier, REST reste source de verite ;
  - suppression de message pour soi avec filtrage par utilisateur.
- Upload avatar :
  - pas de base64 en base ;
  - stockage local ignore par Git ;
  - taille limitee a 2 Mo ;
  - formats limites ;
  - nom de fichier aleatoire ;
  - remplacement prudent des avatars locaux uniquement.
- Donnees sensibles :
  - password/hash/tokens non exposes dans les presenters ;
  - `/api/users` et admin users ne retournent pas l'email ;
  - token football-data lu depuis variable d'environnement, pas versionne.
- Git :
  - `.env`, `mobile/.env`, uploads, `var`, vendor, node_modules et `backend/config/jwt/*.pem` ignores.

## 4. Risques critiques

### C1 - Dependances backend Symfony avec avis de securite

`composer audit` remonte 10 avis de securite affectant 7 packages Symfony, notamment `symfony/cache`, `symfony/dom-crawler`, `symfony/monolog-bridge`, `symfony/routing`, `symfony/runtime`, `symfony/security-http` et `symfony/yaml`.

Impact potentiel :

- injection SQL dans un composant cache selon usage ;
- XXE/local file disclosure dans DomCrawler selon usage ;
- contournements/risques dans runtime, routing, security-http ;
- DoS/memoire dans YAML parser.

Risque pour LeKlub :

- certains composants ne sont pas directement exposes aux utilisateurs finaux ;
- le risque reste critique au niveau hygiene dependances, car un audit dependances echoue et le projet doit etre defendu devant jury.

Correction indispensable :

- mettre a jour les composants Symfony vers une version patchee compatible ;
- relancer `composer audit`, `php bin/phpunit`, `lint:container`, `doctrine:schema:validate --skip-sync`.

## 5. Risques moyens

### M1 - Absence de rate limiting sur endpoints sensibles

Endpoints concernes :

- login ;
- forgot/reset password ;
- refresh token ;
- creation de signalement ;
- upload avatar ;
- envoi de messages.

Risque :

- brute force login ;
- abus de reset password ;
- enumeration indirecte ;
- spam API.

Le package `symfony/rate-limiter` est present, mais aucun usage effectif n'a ete trouve dans la configuration ou le code.

Correction recommandee :

- ajouter une limitation simple par IP et/ou identifiant sur login/reset ;
- limiter refresh et upload avatar ;
- documenter les seuils.

### M2 - Token reset password journalise en environnement dev

Le notifier de reset password envoie un email Mailpit et journalise aussi le token brut en `APP_ENV=dev`.

Risque :

- acceptable pour test local ;
- dangereux si un environnement deploye est mal configure en `dev`, ou si les logs dev sont partages.

Correction recommandee :

- conserver Mailpit comme canal local principal ;
- supprimer le fallback de log ou le rendre explicitement opt-in via variable dediee ;
- rappeler qu'aucun environnement public ne doit tourner en `APP_ENV=dev`.

### M3 - WebSocket local sans garanties production

Le WebSocket authentifie le client via JWT envoye dans un message `auth`. Le token n'est pas dans l'URL, ce qui est positif.

Risques restants :

- URL locale souvent en `ws://`, donc pas chiffree ;
- pas de controle d'origine ;
- pas de limitation de connexions ;
- erreurs WebSocket assez explicites ;
- pas de strategie production `wss://` documentee en detail.

Correction recommandee :

- utiliser `wss://` derriere reverse proxy en production ;
- ajouter verification d'origine si exposition publique ;
- limiter connexions et messages par client.

### M4 - Upload avatar sans retraitement image

Le stockage local valide taille, extension et MIME, et refuse les formats dangereux comme SVG.

Risques restants :

- pas de re-encodage image ;
- pas d'antivirus ;
- fichiers servis depuis `public/uploads` ;
- headers securite Nginx minimaux.

Correction recommandee :

- ajouter headers `X-Content-Type-Options: nosniff` ;
- envisager re-encodage image ou stockage hors `public` avec controleur de lecture si mise en production ;
- conserver la limite 2 Mo.

### M5 - Access tokens valides jusqu'a expiration apres revocation refresh

Apres logout, reset password ou changement de mot de passe, les refresh tokens sont revoques, mais un access token deja emis reste valide jusqu'a son expiration de 15 minutes.

Risque :

- fenetre residuelle courte mais reelle.

Justification MVP :

- JWT stateless ;
- TTL court ;
- actions d'ecriture bloquees pour utilisateurs suspendus via `SuspensionGuard`.

Correction post-MVP :

- liste de revocation access token ou version de session dans le payload JWT.

### M6 - CORS non finalise pour production web

`CORS_ALLOW_ORIGIN` est present dans `.env.example`, mais aucune configuration CORS effective n'a ete identifiee.

Risque :

- non bloquant pour Expo Go natif ;
- a clarifier avant frontend web ou exposition publique.

Correction recommandee :

- ajouter une configuration CORS explicite si un client web est expose ;
- garder une liste d'origines autorisees, jamais `*` avec credentials.

### M7 - Enumeration partielle via unicite email/username

Les endpoints compte peuvent retourner des conflits email/username.

Risque :

- faible a moyen, car l'utilisateur doit etre authentifie ;
- utile UX pour corriger le formulaire.

Decision MVP :

- acceptable pour compte connecte ;
- ne pas rendre les erreurs login/reset plus precises.

## 6. Risques faibles

### F1 - Donnees publiques Expo

Les variables `EXPO_PUBLIC_API_BASE_URL` et `EXPO_PUBLIC_WEBSOCKET_URL` sont publiques par nature.

Risque :

- faible, car aucune cle API ni secret n'y est stocke.

### F2 - Secrets de developpement dans fichiers exemples

`.env.example` contient `change_me` et des mots de passe de base de donnees de developpement.

Risque :

- faible si clairement remplaces hors local ;
- a ne jamais utiliser en production.

### F3 - Messages d'erreur API parfois informatifs

Certaines erreurs indiquent explicitement une ressource introuvable ou une action interdite.

Risque :

- faible dans le contexte API authentifiee ;
- a harmoniser si exposition publique plus large.

### F4 - Absence de headers securite Nginx avances

La configuration Nginx locale est simple.

Risque :

- faible en local ;
- a completer avant deploiement : `nosniff`, restrictions upload, logs, TLS, proxy headers.

### F5 - Absence de surveillance securite runtime

Le projet a des logs applicatifs, mais pas de monitoring ou alerting securite.

Risque :

- acceptable MVP ;
- a prevoir pour production.

## 7. Limites MVP assumees

- Pas de rate limiting actif.
- Pas de verrouillage de compte apres tentatives de login.
- Pas de verification email apres changement email.
- Pas de gestion multi-appareils des sessions.
- Pas d'ecran utilisateur pour voir/revoquer ses sessions.
- Pas de revocation immediate des access tokens deja emis.
- Pas de notification push.
- Pas de chiffrement de bout en bout des messages prives.
- Pas de lecture admin des messages prives, par choix de confidentialite.
- Pas d'antivirus ni re-encodage image pour avatars.
- Pas de CORS production finalise.
- Pas de deploiement production securise documente dans cette etape.
- Football-data utilise une cle d'environnement locale, a regenerer avant mise en ligne.

## 8. Corrections indispensables avant soutenance

1. Mettre a jour les dependances backend vulnerables signalees par `composer audit`.
2. Relancer et archiver les resultats :
   - `composer audit` ;
   - `php bin/phpunit` ;
   - `php bin/console lint:container` ;
   - `php bin/console doctrine:schema:validate --skip-sync`.
3. Documenter dans `docs/security.md` la politique de rotation des secrets :
   - `APP_SECRET` ;
   - cle JWT ;
   - passphrase JWT ;
   - token football-data ;
   - secrets SMTP production futurs.
4. Clarifier que Mailpit est local/dev uniquement.
5. Clarifier que le fallback de log du token reset password est strictement local/dev.

## 9. Corrections recommandees post-soutenance

1. Ajouter rate limiting sur :
   - login ;
   - forgot/reset password ;
   - refresh ;
   - upload avatar ;
   - signalements ;
   - envoi messages.
2. Ajouter headers securite Nginx :
   - `X-Content-Type-Options: nosniff` ;
   - politique cache uploads ;
   - durcissement TLS en production.
3. Mettre en place CORS explicite si client web.
4. Remplacer le log dev du token reset par une option explicite du type `PASSWORD_RESET_LOG_TOKEN=true`.
5. Ajouter une strategie de revocation access token si besoin produit.
6. Ajouter un ecran "sessions/appareils" si LeKlub evolue vers une application publique.
7. Ajouter scan/re-encodage des avatars avant stockage public.
8. Ajouter monitoring securite minimal :
   - erreurs auth ;
   - tentatives reset ;
   - actions admin critiques ;
   - uploads rejetes.

## 10. Checklist securite finale

### Auth et sessions

- [x] Login JWT protege par Symfony Security.
- [x] Access token court : 15 minutes.
- [x] Refresh token stocke hashe.
- [x] Refresh token rotatif.
- [x] Logout revoque le refresh token courant.
- [x] Changement mot de passe revoque les refresh tokens.
- [x] Reset password revoque les refresh tokens.
- [ ] Rate limiting login/reset/refresh.
- [ ] Revocation immediate access token.

### Reset password

- [x] Token brut non stocke.
- [x] Token hash en base.
- [x] Expiration 30 minutes.
- [x] Usage unique.
- [x] Reponse generique a la demande.
- [x] Mailpit pour developpement local.
- [ ] Supprimer ou conditionner strictement le log dev du token.

### Autorisations

- [x] `/api/admin/*` protege `ROLE_ADMIN`.
- [x] Ownership posts/commentaires via voters/use cases.
- [x] Conversations protegees entre participants.
- [x] Suppression message pour soi uniquement.
- [x] Admin ne lit pas les messages prives.

### Donnees sensibles

- [x] `.env` non versionne.
- [x] `mobile/.env` non versionne.
- [x] Cles JWT locales non versionnees.
- [x] Password/hash/tokens non exposes par API.
- [x] Token football-data via variable d'environnement.
- [ ] Rotation secrets avant mise en ligne.

### Uploads

- [x] Pas de base64 en base.
- [x] Taille max 2 Mo.
- [x] Extension/MIME controles.
- [x] Noms aleatoires.
- [x] Uploads ignores par Git.
- [ ] Headers securite statiques.
- [ ] Re-encodage/scanning images.

### Mobile

- [x] Tokens stockes dans SecureStore.
- [x] Pas d'AsyncStorage pour tokens.
- [x] Refresh automatique avec verrou anti-concurrence.
- [x] Navigation protegee.
- [x] Variables Expo sans secret.

### Dependances et CI

- [x] CI execute tests backend/mobile.
- [x] `npm audit --omit=dev --audit-level=high` ne remonte pas de vulnerabilite high.
- [ ] `composer audit` remonte des vulnerabilites a corriger.
- [ ] Integrer ou documenter un audit dependances regulier dans le workflow.

## Verifications realisees

- `git status --short --branch`
- `git ls-files backend/config/jwt .env mobile/.env backend/.env backend/.env.dev backend/.env.test`
- `rg` sur variables sensibles et secrets potentiels hors dossiers ignores.
- Inspection de `security.yaml`, `lexik_jwt_authentication.yaml`, `services.yaml`.
- Inspection refresh token, reset password, password change, account/profile CRUD.
- Inspection upload avatar et configuration Nginx.
- Inspection WebSocket backend/mobile.
- Inspection voters posts/commentaires/conversations.
- Inspection configuration Docker, Mailpit et CI.
- `composer audit` : echec securite, 10 avis affectant 7 packages.
- `npm audit --omit=dev --audit-level=high` : pas de fail high, 11 vulnerabilites moderate liees a Expo/PostCSS/uuid.

## Decision de priorisation

A corriger maintenant :

1. dependances backend signalees par `composer audit` ;
2. documentation explicite du reset token dev/Mailpit ;
3. verification finale qu'aucun secret local n'est pousse.

A documenter comme limites MVP avant correction post-soutenance :

1. rate limiting absent ;
2. access token non revocable immediatement ;
3. CORS/WebSocket production non finalises ;
4. upload avatar sans re-encodage/scanning ;
5. absence de monitoring securite.
