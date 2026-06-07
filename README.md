<!-- Installation et Utilisation de IFOD -->

# 📱 IFOD - Guide Complet d'Installation et d'Utilisation

## 🚀 Vue d'ensemble

IFOD est une plateforme de commerce électronique complète avec:
- **Frontend Web** responsive (HTML/CSS/JavaScript)
- **Backend API** en PHP
- **Base de données** MySQL
- **Application Mobile** PWA (Progressive Web App)

---

## 📋 Prérequis

- **PHP 7.4+** ou supérieur
- **MySQL 5.7+** ou MariaDB
- **Serveur web** (Apache, Nginx, ou PHP built-in)
- **Navigateur moderne** (Chrome, Firefox, Safari, Edge)

---

## 🔧 Installation Backend

### 1. Configuration de la base de données

```bash
# Créer la base de données
mysql -u root -p

mysql> CREATE DATABASE ifod_db;
mysql> USE ifod_db;
```

### 2. Importer le schéma

```bash
mysql -u root -p ifod_db < backend/db/schema.sql
```

### 3. Configurer la base de données

Éditer `backend/config/database.php`:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'votre_mot_de_passe');
define('DB_NAME', 'ifod_db');
```

### 4. Démarrer le serveur PHP

```bash
cd backend
php -S localhost:8000
```

---

## 🌐 Installation Frontend Web

### 1. Ouvrir l'application

- Naviguer vers: `http://localhost/frontend-web/index.html`
- Ou: `http://localhost:3000` (si vous utilisez un serveur local)

### 2. Structure des fichiers

```
frontend-web/
├── index.html          # Page principale
├── css/
│   └── style.css       # Styles
└── js/
    ├── app.js          # Logique principale
    └── api.js          # Communication API
```

---

## 📱 Installation Application Mobile (PWA)

### 1. Accéder à l'app mobile

- URL: `http://localhost/mobile-app/index.html`

### 2. Installation sur téléphone

**Android:**
1. Ouvrir dans Chrome
2. Appuyer sur l'icône ⋮ (menu)
3. Sélectionner "Installer l'app"

**iOS:**
1. Ouvrir dans Safari
2. Appuyer sur Partager
3. Sélectionner "Sur l'écran d'accueil"

### 3. Fonctionnalités PWA

✅ **Installation** comme application native
✅ **Mode hors ligne** avec Service Worker
✅ **Synchronisation** des commandes en arrière-plan
✅ **Notifications push** (à configurer)
✅ **Icônes** et splash screens personnalisés

---

## 🔑 Utilisateurs de Test

### Compte Client
```
Email: test@example.com
Mot de passe: password
```

### Compte Admin
```
Email: admin@ifod.com
Mot de passe: password
```

---

## 🔗 Endpoints API

### Authentification

```
POST /backend/api/auth.php?action=register
POST /backend/api/auth.php?action=login
```

### Produits

```
GET  /backend/api/products.php?action=list&page=1&limit=12
GET  /backend/api/products.php?action=get&id=1
GET  /backend/api/products.php?action=search&q=laptop
POST /backend/api/products.php?action=add
POST /backend/api/products.php?action=update&id=1
```

### Commandes

```
GET  /backend/api/orders.php?action=list&user_id=1
GET  /backend/api/orders.php?action=get&id=1
POST /backend/api/orders.php?action=create
POST /backend/api/orders.php?action=update_status&id=1
POST /backend/api/orders.php?action=cancel&id=1
```

---

## 💻 Guide d'utilisation

### Pour les clients

1. **Parcourir les produits**
   - Page d'accueil affiche les produits populaires
   - Utilisez la recherche pour trouver un produit spécifique

2. **Ajouter au panier**
   - Cliquez sur "Ajouter" sur une carte produit
   - Le nombre d'articles s'affiche en haut

3. **Effectuer une commande**
   - Allez dans le panier
   - Vérifiez les articles et la quantité
   - Validez la commande

4. **Créer un compte**
   - Cliquez sur "Inscription"
   - Remplissez les informations
   - Cliquez sur "S'inscrire"

5. **Se connecter**
   - Cliquez sur "Connexion"
   - Entrez vos identifiants
   - Cliquez sur "Connexion"

---

## 🛠️ Personnalisation

### Modifier les couleurs

Éditer `frontend-web/css/style.css`:

```css
:root {
    --primary-color: #007bff;      /* Couleur principale */
    --secondary-color: #6c757d;    /* Couleur secondaire */
    --success-color: #28a745;      /* Couleur de succès */
    /* ... etc */
}
```

### Ajouter des produits

Éditer `backend/db/schema.sql` ou utiliser l'API:

```bash
curl -X POST http://localhost:8000/api/products.php?action=add \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Produit",
    "price": 25000,
    "category_id": 1,
    "description": "Description"
  }'
```

---

## 📊 Base de données

### Tables principales

1. **users** - Clients et administrateurs
2. **products** - Catalogue de produits
3. **categories** - Catégories de produits
4. **orders** - Commandes
5. **order_items** - Articles des commandes

### Relations

```
users (1) ──→ (N) orders
products (1) ──→ (N) order_items
categories (1) ──→ (N) products
orders (1) ──→ (N) order_items
```

---

## 🐛 Dépannage

### La page ne charge pas

**Solution:**
- Vérifiez que le serveur PHP est en cours d'exécution
- Vérifiez l'URL dans l'adresse
- Vérifiez les erreurs dans la console (F12)

### Les produits ne s'affichent pas

**Solution:**
- Vérifiez la connexion à la base de données
- Vérifiez que la base de données contient des données
- Vérifiez les logs PHP

### Le panier ne fonctionne pas

**Solution:**
- Vérifiez que localStorage est activé
- Essayez de vider le cache
- Rechargez la page

### L'app mobile n'installe pas

**Solution:**
- Utilisez HTTPS (requis pour PWA)
- Vérifiez le manifeste.json
- Vérifiez le Service Worker

---

## 📦 Déploiement

### Déployer sur un serveur

1. **Transférer les fichiers**
   ```bash
   scp -r ./backend user@server:/var/www/ifod/
   scp -r ./frontend-web user@server:/var/www/ifod/
   scp -r ./mobile-app user@server:/var/www/ifod/
   ```

2. **Configurer HTTPS**
   - Obtenir un certificat SSL
   - Configurer votre serveur web

3. **Configurer la base de données**
   - Créer la base de données sur le serveur
   - Importer le schéma
   - Mettre à jour les paramètres de connexion

4. **Vérifier les permissions**
   ```bash
   chmod 755 /var/www/ifod
   chmod 644 /var/www/ifod/backend/config/database.php
   ```

---

## 📝 Notes importantes

⚠️ **Sécurité:**
- Changez les valeurs par défaut en production
- Utilisez HTTPS pour toutes les transactions
- Validez toutes les entrées utilisateur
- Utilisez des variables d'environnement pour les secrets

⚠️ **Performance:**
- Mettez en cache les images statiques
- Optimisez les requêtes de base de données
- Utilisez la pagination pour les listes longues

⚠️ **Maintenance:**
- Sauvegardez régulièrement la base de données
- Surveillez les logs
- Testez régulièrement les fonctionnalités

---

## 🎓 Prochaines étapes

- Implémenter un système de paiement
- Ajouter des notifications email
- Créer un tableau de bord administrateur
- Implémenter les critiques et évaluations
- Ajouter la livraison en temps réel
- Implémenter la multithérapie de devises
- Ajouter des filtres avancés

---

## 📞 Support

Pour toute question ou problème:
- Consultez la documentation
- Vérifiez les logs
- Testez dans la console navigateur (F12)
- Vérifiez les permissions des fichiers

---

## 📄 Licence

IFOD © 2024 - Tous droits réservés

---

**Bonne utilisation de IFOD! 🚀**
