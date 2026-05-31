<?php
// ========================================================
// includes/header.php — Version Intégrale & Design Phase 3
// ========================================================
require_once 'includes/config.php';

$user = currentUser();
$role = $user['role'] ?? 'guest';

// Gestion des liens de navigation par défaut
$navLinks = [
    'index.php'     => 'Accueil',
    'carte.php'     => 'Carte',
    'livraison.php' => 'Livraison',
    'apropos.php'   => 'À propos',
    'contact.php'   => 'Contact',
    'avis.php'      => 'Avis',
];

// Si l'utilisateur n'est pas connecté, on affiche l'onglet Connexion
if (!$user) {
    $navLinks['connexion.php'] = 'Connexion';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= h($pageTitle ?? 'CY-FAT') ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
</head>
<body class="<?= $role === 'admin' ? 'admin-mode ' : '' ?>">

<header class="topbar">
    <div class="topbar-main">
        
        <div class="brand-logo">
            <a href="index.php" style="text-decoration: none; font-weight: bold; color: var(--color-primary); font-size: 1.5rem; display: flex; align-items: center; gap: 8px;">
                <span>🍳</span> CY-FAT
            </a>
        </div>

        <div class="search-container">
            <input type="text" id="search-input" placeholder="Rechercher un plat..." value="<?= h($_GET['search'] ?? '') ?>">
            <button id="search-btn">🔍</button>
        </div>

        <div class="profile-section">
            <button id="theme-toggle-btn" class="profile-btn" title="Changer de thème">🌓 Thème</button>
            
            <button id="profile-btn" class="profile-btn">👤 <?= $user ? h($user['prenom']) : 'Profil' ?></button>
            
            <div id="profile-dropdown" class="profile-dropdown" style="display: none; position: absolute; top: 110%; right: 0; background: var(--color-surface); border: 1px solid rgba(114,106,101,0.15); border-radius: 8px; box-shadow: var(--shadow-soft); z-index: 1000; min-width: 180px;">
                <?php if ($user): ?>
                    <div id="user-info" style="padding: 12px; border-bottom: 1px solid rgba(0,0,0,0.06);">
                        <strong style="color: var(--color-dark);"><?= h($user['prenom'] . ' ' . $user['nom']) ?></strong><br>
                        <small style="color: var(--color-muted); font-size: 0.8rem; text-transform: capitalize;"><?= h($user['role']) ?></small>
                    </div>
                    <?php if ($user['role'] === 'client'): ?>
                        <a href="profil.php" class="dropdown-item" style="display: block; padding: 10px 12px; text-decoration: none; color: var(--color-dark); font-size: 0.9rem;">Mon Profil</a>
                        <a href="historique.php" class="dropdown-item" style="display: block; padding: 10px 12px; text-decoration: none; color: var(--color-dark); font-size: 0.9rem;">Mes Commandes</a>
                    <?php endif; ?>
                    <a href="actions/logout.php" class="dropdown-item" style="display: block; padding: 10px 12px; text-decoration: none; color: var(--color-primary); font-weight: 600; font-size: 0.9rem; border-top: 1px solid rgba(0,0,0,0.06);">Se déconnecter</a>
                <?php else: ?>
                    <a href="connexion.php" class="dropdown-item" style="display: block; padding: 10px 12px; text-decoration: none; color: var(--color-dark); font-size: 0.9rem;">Se connecter / S'inscrire</a>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <nav>
        <ul>
            <?php foreach ($navLinks as $url => $label): ?>
                <li>
                    <a href="<?= $url ?>" class="<?= ($activePage === $url) ? 'active' : '' ?>">
                        <?= $label ?>
                    </a>
                </li>
            <?php endforeach; ?>
            
            <?php if ($user && $user['role'] === 'admin'): ?>
                <li><a href="admin_users.php" class="<?= ($activePage === 'admin_users.php') ? 'active' : '' ?>" style="color: var(--color-primary); font-weight: bold;">🔧 Admin</a></li>
            <?php elseif ($user && $user['role'] === 'restaurateur'): ?>
                <li><a href="restaurateur.php" class="<?= ($activePage === 'restaurateur.php') ? 'active' : '' ?>" style="color: #2d7a4f; font-weight: bold;">👨‍🍳 Cuisine</a></li>
            <?php elseif ($user && $user['role'] === 'livreur'): ?>
                <li><a href="livreur.php" class="<?= ($activePage === 'livreur.php') ? 'active' : '' ?>" style="color: #2a6f97; font-weight: bold;">🛵 Livraisons</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>

<?php if ($user && $user['role'] === 'client'): ?>
<div id="cart-fab" class="cart-fab" onclick="window.location='panier.php'">
    🛒 <span id="cart-count">0</span>
</div>
<?php endif; ?>

<script>
    // 1. Initialisation et chargement immédiat du thème enregistré
    if (localStorage.getItem('cyfat-theme') === 'sombre') {
        document.body.classList.add('theme-sombre');
    }

    // Gestion de l'interrupteur du Thème (Clair / Sombre)
    const themeToggleBtn = document.getElementById('theme-toggle-btn');
    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', function() {
            document.body.classList.toggle('theme-sombre');
            if (document.body.classList.contains('theme-sombre')) {
                localStorage.setItem('cyfat-theme', 'sombre');
            } else {
                localStorage.setItem('cyfat-theme', 'clair');
            }
        });
    }

    // 2. Gestion de l'ouverture/fermeture du menu déroulant profil
    const profileBtn = document.getElementById('profile-btn');
    const profileDropdown = document.getElementById('profile-dropdown');
    
    if (profileBtn && profileDropdown) {
        profileBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            profileDropdown.style.display = profileDropdown.style.display === 'block' ? 'none' : 'block';
        });
        
        // Ferme le menu de profil si l'on clique n'importe où ailleurs sur la page
        document.addEventListener('click', function() {
            profileDropdown.style.display = 'none';
        });
    }

    // 3. Gestion de la soumission de la barre de recherche
    const searchBtn = document.getElementById('search-btn');
    const searchInput = document.getElementById('search-input');
    
    if (searchBtn && searchInput) {
        searchBtn.addEventListener('click', function() {
            const query = searchInput.value.trim();
            window.location.href = 'carte.php?search=' + encodeURIComponent(query);
        });
        
        // Permet de lancer la recherche en appuyant sur la touche "Entrée"
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchBtn.click();
            }
        });
    }

    // 4. Calcul et rafraîchissement en arrière-plan de l'indicateur panier
    function updateCartCount() {
        const cart = JSON.parse(localStorage.getItem('cyfatCart') || '[]');
        const totalItems = cart.reduce((accumulateur, item) => accumulateur + item.quantite, 0);
        const cartBadge = document.getElementById('cart-count');
        if (cartBadge) {
            cartBadge.textContent = totalItems;
        }
    }
    document.addEventListener("DOMContentLoaded", updateCartCount);
</script>
