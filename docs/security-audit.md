# Audit securite LeKlub

Date : 25 mai 2026  
Branche : `feature/security-audit`  
Perimetre : backend Symfony, mobile Expo, Docker, CI, configuration, dependances, auth, feed, messagerie, moderation, upload avatar, reset password, refresh token.

## 1. Resume executif

LeKlub dispose d'une base securite solide pour un MVP CDA : API stateless protegee par JWT, refresh tokens hashes et rotatifs, reset password avec token hash en base, stockage mobile via Expo SecureStore, autorisations metier par voters/use cases, moderation admin sans lecture des messages prives, validation DTO et separation des couches.

L'audit initial a identifie des risques reels. Les corrections ciblees appliquees sur cette branche sont :

- mise a jour des dependances Symfony signalees par `composer audit` ;
- rate limiting simple sur les endpoints auth sensibles ;
- log du token reset password rendu opt-in et limite a `APP_ENV=dev` ;
- headers Nginx simples ;
- message d'echec refresh rendu plus generique ;
- documentation des limites JWT, WebSocket, CORS, Expo et upload avatar.

Aucun secret versionne n'a ete detecte. `.env`, `mobile/.env`, uploads et cles JWT locales sont ignores par Git. Les cles JWT presentes localement dans `backend/config/jwt/` ne sont pas suivies par Git.

Conclusion : le projet est defensible pour une soutenance CDA en environnement local/demo. Une mise en production publique demanderait encore un durcissement CORS/WebSocket, une strategie TLS complete, du monitoring et une politique plus avancee de sessions.

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
- Client API avec refresh automatique et verrou anti-concurrence.
- Navigation protegee selon authentification et role admin.
- Upload avatar via image picker.
- WebSocket avec JWT envoye dans un message d'authentification, jamais dans l'URL.
- Variables publiques Expo : URL API et URL WebSocket uniquement, pas de secret.

### Infrastructure

- Docker : Nginx, PHP/Symfony, MySQL, WebSocket, Mailpit.
- CI GitHub Actions : Composer, lint container, Doctrine mapping, PHPUnit, TypeScript mobile.
- Environnements : `.env.example`, `backend/.env.test`, `backend/.env.dev`, variables CI factices.

## 3. Points forts securite

- JWT access token court : 15 minutes.
- Refresh token :
  - token brut jamais stocke en base ;
  - hash SHA-256 stocke ;
  - expiration 30 jours ;
  - rotation a chaque refresh ;
  - revocation au logout ;
  - revocation apres changement de mot de passe et reset password.
- Reset password :
  - reponse generique a la demande ;
  - token brut non retourne par l'API ;
  - token hash en base ;
  - expiration 30 minutes ;
  - usage unique ;
  - anciens tokens invalides a nouvelle demande ;
  - Mailpit local sans secret SMTP.
- Rate limiting auth :
  - login : 5 tentatives par minute ;
  - forgot password : 3 demandes par 15 minutes ;
  - reset password : 5 tentatives par 15 minutes ;
  - refresh token : 10 tentatives par minute ;
  - changement de mot de passe : 5 tentatives par 15 minutes.
- Mobile :
  - tokens stockes dans SecureStore ;
  - pas d'AsyncStorage pour les tokens ;
  - refresh automatique sans boucle infinie connue.
- Controle d'acces :
  - `/api/admin/*` protege par `ROLE_ADMIN` ;
  - voters pour posts, commentaires et conversations ;
  - use cases metier pour ownership et moderation.
- Messagerie :
  - admin ne lit pas les messages prives ;
  - WebSocket sert uniquement a notifier ;
  - REST reste source de verite.
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

## 4. Risques critiques

### C1 - Dependances backend Symfony avec avis de securite - corrige

L'audit initial remontait 10 avis de securite affectant 7 packages Symfony.

Correction appliquee :

- mise a jour ciblee en Symfony 6.4.x ;
- aucune montee majeure ;
- `composer audit` relance avec succes ;
- `php bin/phpunit`, `lint:container` et `doctrine:schema:validate --skip-sync` relances avec succes.

Risque residuel :

- maintenir `composer audit` dans les controles reguliers avant toute mise en ligne.

## 5. Risques moyens

### M1 - Rate limiting simple sur endpoints auth - corrige MVP

Le risque de brute force ou d'abus reset/refresh est traite par des limiters Symfony simples et locaux aux endpoints sensibles.

Limite residuelle :

- pas de verrouillage de compte ;
- pas de rate limiting avance par appareil ;
- pas de monitoring des tentatives.

### M2 - Token reset password en logs dev - durci

Le token reset password n'est plus loggue par defaut. Le fallback de diagnostic est possible uniquement si `APP_ENV=dev` et `PASSWORD_RESET_LOG_TOKEN=true`.

Decision :

- Mailpit reste le canal local principal ;
- le fallback log ne doit jamais etre active en test, CI ou production.

### M3 - WebSocket local sans garanties production

Le WebSocket authentifie le client via JWT dans un message `auth`. Le token n'est pas dans l'URL.

Risques restants :

- local en `ws://`, non chiffre ;
- pas de controle d'origine ;
- pas de limitation de connexions ;
- pas de strategie production `wss://` complete.

Decision MVP :

- ne pas refondre WebSocket avant soutenance ;
- documenter `wss://`, controle d'origine et limitation de connexions comme pre-requis production.

### M4 - Upload avatar sans retraitement image

Le stockage local valide taille, extension et MIME, refuse SVG et genere un nom aleatoire.

Risques restants :

- pas de re-encodage image ;
- pas d'antivirus ;
- fichiers servis depuis `public/uploads`.

Correction appliquee :

- ajout de headers Nginx simples, dont `X-Content-Type-Options: nosniff`.

Decision MVP :

- ne pas ajouter d'antivirus ni re-encodage serveur avant soutenance pour ne pas fragiliser l'upload iPhone valide.

### M5 - Access tokens valides jusqu'a expiration apres revocation refresh

Apres logout, reset password ou changement de mot de passe, les refresh tokens sont revoques. Un access token deja emis reste valide jusqu'a son expiration de 15 minutes.

Justification MVP :

- comportement normal avec JWT stateless ;
- TTL court ;
- pas de blacklist JWT lourde ;
- actions d'ecriture bloquees pour utilisateurs suspendus via `SuspensionGuard`.

Correction post-MVP :

- version de session dans le payload JWT ou blacklist courte si besoin produit.

### M6 - CORS non finalise pour production web

`CORS_ALLOW_ORIGIN` est present dans `.env.example`, mais aucune configuration CORS production dediee n'est active.

Decision MVP :

- non bloquant pour Expo Go natif ;
- a traiter avant client web public.

### M7 - Enumeration partielle via unicite email/username

Les endpoints compte peuvent retourner des conflits email/username.

Decision MVP :

- acceptable car l'utilisateur doit etre authentifie ;
- utile pour l'UX compte ;
- login, forgot et reset restent generiques.

## 6. Risques faibles

### F1 - Donnees publiques Expo

Les variables `EXPO_PUBLIC_API_BASE_URL` et `EXPO_PUBLIC_WEBSOCKET_URL` sont publiques par nature. Aucun secret ne doit etre place dans une variable `EXPO_PUBLIC_*`.

### F2 - `.env.example` dev only

`.env.example` contient des valeurs locales comme `change_me`, Mailpit et des mots de passe MySQL de developpement. Elles ne sont pas des secrets de production et doivent etre remplacees avant toute mise en ligne.

### F3 - Messages d'erreur API parfois informatifs

Les erreurs metier authentifiees restent explicites pour preserver l'UX admin/utilisateur. Les flux sensibles login/reset/refresh restent generiques.

### F4 - Headers Nginx production partiels

La configuration locale ajoute des headers simples : `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy` et `Permissions-Policy`.

Restera a traiter en production :

- TLS ;
- HSTS en HTTPS ;
- proxy headers ;
- politique CORS si client web.

### F5 - Absence de surveillance securite runtime

Le MVP n'a pas de monitoring ou alerting securite avance.

## 7. Limites MVP assumees

- Pas de gestion des sessions ou appareils connectes.
- Pas de revocation serveur immediate des access tokens JWT avant leur expiration courte.
- Pas de verification email.
- Pas de SMTP de production pour le reset password.
- Pas de deep link automatique pour le reset password.
- Pas de blocage de compte apres plusieurs tentatives echouees.
- Rate limiting simple sur auth, sans strategie anti-abus avancee.
- Pas de chiffrement applicatif du contenu des messages prives.
- Pas de notification push.
- Pas de reconnexion WebSocket avancee.
- Pas d'antivirus ni re-encodage image pour avatars.
- Pas de CORS production finalise.
- Football-data utilise une cle d'environnement locale, a regenerer avant mise en ligne.

## 8. Corrections indispensables avant soutenance

1. Conserver les dependances a jour et relancer `composer audit`.
2. Conserver les tests backend verts :
   - `php bin/phpunit` ;
   - `php bin/console lint:container` ;
   - `php bin/console doctrine:schema:validate --skip-sync`.
3. Verifier qu'aucun secret local n'est suivi par Git.
4. Presenter clairement les limites JWT stateless, Mailpit local, Expo public env et WebSocket local.

## 9. Corrections recommandees post-soutenance

1. Ajouter du rate limiting avance sur upload avatar, signalements et envoi messages.
2. Ajouter CORS explicite si client web.
3. Exposer WebSocket uniquement en `wss://` avec controle d'origine.
4. Ajouter re-encodage/scanning des avatars si application publique.
5. Ajouter ecran sessions/appareils.
6. Ajouter monitoring securite :
   - erreurs auth ;
   - tentatives reset ;
   - actions admin critiques ;
   - uploads rejetes.
7. Ajouter HSTS et durcissement TLS dans la configuration de production.

## 10. Checklist securite finale

### Auth et sessions

- [x] Login JWT protege par Symfony Security.
- [x] Access token court : 15 minutes.
- [x] Refresh token stocke hashe.
- [x] Refresh token rotatif.
- [x] Logout revoque le refresh token courant.
- [x] Changement mot de passe revoque les refresh tokens.
- [x] Reset password revoque les refresh tokens.
- [x] Rate limiting login/reset/refresh/password.
- [ ] Revocation immediate access token.

### Reset password

- [x] Token brut non stocke.
- [x] Token hash en base.
- [x] Expiration 30 minutes.
- [x] Usage unique.
- [x] Reponse generique a la demande.
- [x] Mailpit pour developpement local.
- [x] Log dev du token conditionne par `PASSWORD_RESET_LOG_TOKEN=true`.

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
- [x] Header `nosniff`.
- [ ] Re-encodage/scanning images.

### Mobile

- [x] Tokens stockes dans SecureStore.
- [x] Pas d'AsyncStorage pour tokens.
- [x] Refresh automatique avec verrou anti-concurrence.
- [x] Navigation protegee.
- [x] Variables Expo sans secret.

### Dependances et CI

- [x] CI execute tests backend/mobile.
- [x] `composer audit` ne remonte plus d'avis de securite.
- [x] `npm audit --omit=dev --audit-level=high` ne remonte pas de vulnerabilite high.
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
- `composer audit` initial : echec securite, 10 avis affectant 7 packages.
- `composer audit` apres correction : aucun avis de securite.
- `npm audit --omit=dev --audit-level=high` : pas de fail high, 11 vulnerabilites moderate liees a Expo/PostCSS/uuid.

## Decision de priorisation

Corrige pendant cette branche :

1. dependances backend signalees par `composer audit` ;
2. rate limiting simple sur endpoints auth sensibles ;
3. log reset token dev rendu opt-in ;
4. headers Nginx simples ;
5. message refresh generique ;
6. documentation explicite des limites securite MVP.

A documenter comme limites MVP :

1. access token non revocable immediatement ;
2. CORS/WebSocket production non finalises ;
3. upload avatar sans re-encodage/scanning ;
4. absence de monitoring securite avance.
