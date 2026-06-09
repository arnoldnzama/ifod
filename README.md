# IFOD · SIRH — Système d'Information des Ressources Humaines

Plateforme RH d'entreprise (HRIS) multi-modules, multi-utilisateurs et multilingue (FR/EN).
Ce dépôt est livré **par phases fonctionnelles**. La **Phase 0** (fondations) est décrite ci-dessous.

> Stack : **Laravel 12 / PHP 8.4** (REST API, JWT, RBAC, Swagger) · **MySQL 8** + **phpMyAdmin** ·
> **React + TypeScript + Vite + Material UI + Tailwind + Framer Motion** · **Docker / Docker Compose** ·
> **Flutter** (mobile, phases ultérieures).

---

## Sommaire

- [Architecture du monorepo](#architecture-du-monorepo)
- [Périmètre livré — Phase 0](#périmètre-livré--phase-0)
- [Démarrage rapide (Docker)](#démarrage-rapide-docker)
- [Démarrage manuel (sans Docker)](#démarrage-manuel-sans-docker)
- [Comptes de démonstration](#comptes-de-démonstration)
- [API & documentation Swagger](#api--documentation-swagger)
- [Tests](#tests)
- [Feuille de route des phases](#feuille-de-route-des-phases)

---

## Architecture du monorepo

```
ifod/
├── backend/            # API Laravel 12 (PHP 8.4) — JWT, RBAC, Swagger
├── frontend-web/       # Application web React + TypeScript (Vite)
├── infra/
│   └── nginx/          # Configs Nginx (backend + frontend)
├── doc/                # Cahier des charges & documentation
├── docker-compose.yml  # Orchestration : db, phpmyadmin, backend, backend-web, frontend
└── .github/workflows/  # CI (tests backend + lint/build frontend)
```

## Périmètre livré — Phase 0

**Socle technique opérationnel + Module 1 (base) + Dashboard exécutif.**

- 🔐 **Authentification JWT** (login / me / refresh / logout) — stateless.
- 👥 **RBAC** via Spatie : 8 profils (Super Admin, Admin RH, Resp. Paie, Resp. Formation,
  Resp. Recrutement, Manager, Employé, Auditeur) et 29 permissions par domaine.
- 🧑‍💼 **Module 1 — Gestion administrative (base)** : CRUD employés (dossier complet),
  matricule auto `IFOD-AAAA-NNNN`, archivage, **historique des mouvements** (audit),
  référentiels Départements / Services / Fonctions.
- 📊 **Dashboard exécutif RH** (données réelles) : effectifs (total/actifs/suspendus/congé/mission),
  masse salariale, alertes (contrats expirant, anniversaires), graphiques (effectifs par
  département, évolution salariale, répartition par sexe, types de contrat).
- 📖 **Swagger / OpenAPI** auto-généré.
- 🌍 **Internationalisation** FR/EN côté web.
- 🐳 **Docker Compose** : MySQL 8 + phpMyAdmin + API (PHP-FPM + Nginx) + Web (Nginx).
- ✅ **CI GitHub Actions** : tests backend (PHPUnit) + lint & build frontend.

## Démarrage rapide (Docker)

Prérequis : **Docker** + **Docker Compose v2**.

```bash
# 1. Cloner puis se placer à la racine du dépôt
cd ifod

# 2. (optionnel) créer le fichier d'environnement du backend
cp backend/.env.example backend/.env

# 3. Construire et démarrer toute la stack
docker compose up -d --build
```

Au premier démarrage, le conteneur backend attend la base, exécute **migrations + seeders**
(comptes de test, départements, ~50 employés) puis génère la doc Swagger.

| Service          | URL                              |
|------------------|----------------------------------|
| Frontend web     | http://localhost:3000            |
| API REST         | http://localhost:8000/api        |
| Swagger UI       | http://localhost:8000/api/documentation |
| phpMyAdmin       | http://localhost:8081            |
| MySQL            | localhost:3306                   |

> Variables surchargables (ports, identifiants DB…) : voir `docker-compose.yml`.

## Démarrage manuel (sans Docker)

### Backend

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan jwt:secret --force
# Base SQLite zéro-config (par défaut), ou configurez MySQL dans .env
touch database/database.sqlite
php artisan migrate --seed
php artisan serve            # http://127.0.0.1:8000
```

### Frontend

```bash
cd frontend-web
npm install
cp .env.example .env         # VITE_API_URL=http://localhost:8000/api
npm run dev                  # http://localhost:5173
```

## Comptes de démonstration

Mot de passe commun : **`password`**

| Profil                  | E-mail                   |
|-------------------------|--------------------------|
| Super Administrateur    | superadmin@ifod.local    |
| Administrateur RH       | rh@ifod.local            |
| Responsable Paie        | paie@ifod.local          |
| Responsable Formation   | formation@ifod.local     |
| Responsable Recrutement | recrutement@ifod.local   |
| Manager                 | manager@ifod.local       |
| Employé                 | employe@ifod.local       |
| Auditeur                | auditeur@ifod.local      |

## API & documentation Swagger

- Doc interactive : **`/api/documentation`**
- Authentification : `POST /api/auth/login` → `Authorization: Bearer <token>`

Endpoints principaux (Phase 0) :

| Méthode | Endpoint                          | Permission        |
|---------|-----------------------------------|-------------------|
| POST    | `/api/auth/login`                 | public            |
| GET     | `/api/auth/me`                    | auth              |
| GET     | `/api/dashboard`                  | `dashboard.view`  |
| GET     | `/api/employees`                  | `employees.view`  |
| POST    | `/api/employees`                  | `employees.create`|
| GET     | `/api/employees/{id}`             | `employees.view`  |
| PUT     | `/api/employees/{id}`             | `employees.update`|
| POST    | `/api/employees/{id}/archive`     | `employees.update`|
| DELETE  | `/api/employees/{id}`             | `employees.delete`|
| GET     | `/api/employees/{id}/movements`   | `employees.view`  |
| GET     | `/api/departments` `/services` `/job-titles` | `employees.view` |

## Tests

```bash
# Backend (PHPUnit, SQLite en mémoire)
cd backend && php artisan test

# Frontend (lint + build)
cd frontend-web && npm run lint && npm run build
```

## Feuille de route des phases

| Phase | Contenu | État |
|-------|---------|------|
| **0** | Fondations : monorepo, Docker, auth JWT, RBAC, Module 1 (base), dashboard, CI | ✅ |
| 1 | Module 1 complet : documents (MinIO/S3), upload, OCR, archivage avancé | ⏳ |
| 2 | Présences & Congés + workflow de validation | ⏳ |
| 3 | Paie (brut/net, primes, IRPP/CNSS/INPP, bulletins PDF + QR) | ⏳ |
| 4 | Missions / Recrutement / Formation / Évaluations | ⏳ |
| 5 | Portail employé + Reporting (PDF/Excel/CSV) | ⏳ |
| 6 | Application mobile Flutter + build **APK** | ⏳ |
| 7 | Intégrations (FCM, Twilio, Maps, LDAP) + sécurité avancée (MFA, AES-256, audit) | ⏳ |

### Limites matérielles connues
- **IPA iOS** : nécessite macOS + Xcode + certificat Apple (impossible sur ce socle Linux).
  Le code Flutter et la doc de build sont fournis ; pas de `.ipa` signé.
- **Déploiement SSL (Ubuntu/Nginx/Let's Encrypt)** : configs + guide fournis ; le déploiement
  réel requiert l'accès à votre serveur.

---

© IFOD — Projet SIRH. Livré par phases, conforme au cahier des charges (`doc/`).
