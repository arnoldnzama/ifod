// ========================================
// APP.JS - Logique principale de l'application
// ========================================

// État global
const appState = {
    user: null,
    cart: [],
    products: [],
    currentPage: 'home'
};

// Initialiser l'application au chargement
document.addEventListener('DOMContentLoaded', () => {
    initApp();
    setupEventListeners();
    checkUserSession();
});

// Initialiser l'app
async function initApp() {
    console.log('Initialisation de IFOD...');
    await loadProducts();
    updateCartCount();
}

// Vérifier la session utilisateur
function checkUserSession() {
    const token = localStorage.getItem('token');
    const user = localStorage.getItem('user');
    
    if (token && user) {
        appState.user = JSON.parse(user);
        updateUserUI();
    }
}

// Configurer les écouteurs d'événements
function setupEventListeners() {
    // Recherche
    const searchBtn = document.getElementById('searchBtn');
    const searchInput = document.getElementById('searchInput');
    
    if (searchBtn && searchInput) {
        searchBtn.addEventListener('click', () => searchProducts());
        searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') searchProducts();
        });
    }
    
    // Navigation
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const page = link.getAttribute('data-page');
            navigateTo(page);
        });
    });
    
    // Authentification
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');
    const logoutBtn = document.getElementById('logoutBtn');
    
    if (loginForm) loginForm.addEventListener('submit', handleLogin);
    if (registerForm) registerForm.addEventListener('submit', handleRegister);
    if (logoutBtn) logoutBtn.addEventListener('click', handleLogout);
}

// Charger les produits
async function loadProducts(page = 1, category = null) {
    try {
        const response = await api.getProducts(page, 12, category);
        if (response.success) {
            appState.products = response.products;
            displayProducts(response.products);
        }
    } catch (error) {
        console.error('Erreur lors du chargement des produits:', error);
        showNotification('Erreur lors du chargement des produits', 'error');
    }
}

// Afficher les produits
function displayProducts(products) {
    const productsContainer = document.getElementById('productsContainer');
    if (!productsContainer) return;
    
    productsContainer.innerHTML = '';
    
    if (products.length === 0) {
        productsContainer.innerHTML = '<p class="text-center">Aucun produit trouvé</p>';
        return;
    }
    
    products.forEach(product => {
        const productCard = createProductCard(product);
        productsContainer.appendChild(productCard);
    });
}

// Créer une carte produit
function createProductCard(product) {
    const card = document.createElement('div');
    card.className = 'product-card';
    card.innerHTML = `
        <div class="product-image">
            <img src="${product.image_url || 'assets/placeholder.png'}" alt="${product.name}">
            ${product.discount_price ? '<span class="badge-discount">-' + 
              Math.round((1 - product.discount_price / product.price) * 100) + '%</span>' : ''}
        </div>
        <div class="product-info">
            <h3>${product.name}</h3>
            <p class="product-description">${product.description?.substring(0, 100) || ''}...</p>
            <div class="product-price">
                <span class="price">${formatPrice(product.discount_price || product.price)}</span>
                ${product.discount_price ? 
                    `<span class="original-price">${formatPrice(product.price)}</span>` : ''}
            </div>
            <div class="product-actions">
                <button class="btn btn-primary" onclick="addToCart(${product.id})">
                    <i class="fas fa-shopping-cart"></i> Ajouter
                </button>
                <button class="btn btn-secondary" onclick="viewProduct(${product.id})">
                    <i class="fas fa-eye"></i> Détails
                </button>
            </div>
        </div>
    `;
    return card;
}

// Ajouter au panier
function addToCart(productId) {
    const product = appState.products.find(p => p.id === productId);
    if (!product) return;
    
    const cartItem = appState.cart.find(item => item.id === productId);
    
    if (cartItem) {
        cartItem.quantity++;
    } else {
        appState.cart.push({
            id: product.id,
            name: product.name,
            price: product.discount_price || product.price,
            quantity: 1
        });
    }
    
    localStorage.setItem('cart', JSON.stringify(appState.cart));
    updateCartCount();
    showNotification(`${product.name} ajouté au panier`, 'success');
}

// Mettre à jour le nombre d'articles du panier
function updateCartCount() {
    const cartCount = document.getElementById('cartCount');
    if (cartCount) {
        const count = appState.cart.reduce((sum, item) => sum + item.quantity, 0);
        cartCount.textContent = count;
        cartCount.style.display = count > 0 ? 'inline-block' : 'none';
    }
}

// Rechercher des produits
async function searchProducts() {
    const query = document.getElementById('searchInput').value;
    if (!query.trim()) return;
    
    try {
        const response = await api.searchProducts(query);
        if (response.success) {
            displayProducts(response.products);
        }
    } catch (error) {
        console.error('Erreur lors de la recherche:', error);
    }
}

// Gérer la connexion
async function handleLogin(e) {
    e.preventDefault();
    
    const email = document.getElementById('loginEmail').value;
    const password = document.getElementById('loginPassword').value;
    
    try {
        const response = await api.login(email, password);
        
        if (response.success) {
            appState.user = response.user;
            api.setToken(response.token);
            localStorage.setItem('user', JSON.stringify(response.user));
            
            showNotification('Connexion réussie!', 'success');
            updateUserUI();
            navigateTo('home');
        } else {
            showNotification(response.message || 'Erreur de connexion', 'error');
        }
    } catch (error) {
        console.error('Erreur lors de la connexion:', error);
        showNotification('Erreur de connexion', 'error');
    }
}

// Gérer l'inscription
async function handleRegister(e) {
    e.preventDefault();
    
    const formData = {
        first_name: document.getElementById('registerFirstName').value,
        last_name: document.getElementById('registerLastName').value,
        email: document.getElementById('registerEmail').value,
        password: document.getElementById('registerPassword').value,
        phone: document.getElementById('registerPhone').value
    };
    
    try {
        const response = await api.register(formData);
        
        if (response.success) {
            showNotification('Inscription réussie! Veuillez vous connecter.', 'success');
            navigateTo('login');
        } else {
            showNotification(response.message || 'Erreur lors de l\'inscription', 'error');
        }
    } catch (error) {
        console.error('Erreur lors de l\'inscription:', error);
        showNotification('Erreur lors de l\'inscription', 'error');
    }
}

// Gérer la déconnexion
function handleLogout() {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    appState.user = null;
    appState.cart = [];
    updateUserUI();
    showNotification('Déconnexion réussie', 'success');
    navigateTo('home');
}

// Mettre à jour l'interface utilisateur
function updateUserUI() {
    const userInfo = document.getElementById('userInfo');
    const authButtons = document.getElementById('authButtons');
    
    if (appState.user && userInfo && authButtons) {
        authButtons.style.display = 'none';
        userInfo.style.display = 'block';
        userInfo.innerHTML = `
            <span>${appState.user.first_name} ${appState.user.last_name}</span>
            <button id="logoutBtn" class="btn btn-link">Déconnexion</button>
        `;
        document.getElementById('logoutBtn').addEventListener('click', handleLogout);
    } else if (userInfo && authButtons) {
        authButtons.style.display = 'block';
        userInfo.style.display = 'none';
    }
}

// Naviguer vers une page
function navigateTo(page) {
    appState.currentPage = page;
    
    document.querySelectorAll('[data-page-content]').forEach(el => {
        el.style.display = 'none';
    });
    
    const pageContent = document.querySelector(`[data-page-content="${page}"]`);
    if (pageContent) {
        pageContent.style.display = 'block';
    }
}

// Afficher une notification
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        background: ${type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : '#17a2b8'};
        color: white;
        border-radius: 4px;
        z-index: 9999;
        animation: slideIn 0.3s ease-in-out;
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Formater le prix
function formatPrice(price) {
    return new Intl.NumberFormat('fr-CM', {
        style: 'currency',
        currency: 'XAF'
    }).format(price);
}

// Afficher les détails d'un produit
async function viewProduct(id) {
    try {
        const response = await api.getProduct(id);
        if (response.success) {
            // Afficher la modale produit
            displayProductModal(response.product);
        }
    } catch (error) {
        console.error('Erreur:', error);
    }
}

// Afficher la modale produit
function displayProductModal(product) {
    const modal = document.getElementById('productModal');
    if (!modal) return;
    
    modal.innerHTML = `
        <div class="modal-content">
            <span class="close" onclick="closeProductModal()">&times;</span>
            <div class="modal-body">
                <img src="${product.image_url}" alt="${product.name}" class="modal-image">
                <h2>${product.name}</h2>
                <p>${product.description}</p>
                <div class="modal-price">
                    <span>${formatPrice(product.discount_price || product.price)}</span>
                </div>
                <button class="btn btn-primary" onclick="addToCart(${product.id})">
                    Ajouter au panier
                </button>
            </div>
        </div>
    `;
    modal.style.display = 'block';
}

// Fermer la modale produit
function closeProductModal() {
    const modal = document.getElementById('productModal');
    if (modal) modal.style.display = 'none';
}

// Charger le panier au démarrage
appState.cart = JSON.parse(localStorage.getItem('cart') || '[]');
