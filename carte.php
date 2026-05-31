<?php
// carte.php
require_once 'includes/config.php';

$pageTitle  = 'CY-FAT — Carte';
$activePage = 'carte.php';

$plats  = loadJSON(DATA_PLATS);
$menus  = loadJSON(DATA_MENUS);
$user   = currentUser();

// Filtre catégorie
$filtre    = sanitize($_GET['cat']    ?? 'tout');
$search    = sanitize($_GET['search'] ?? '');

$categories = [
    'tout'    => 'Tout',
    'entree'  => 'Entrées',
    'burger'  => 'Burgers',
    'plat'    => 'Plats',
    'bowl'    => 'Bowls & Salades',
    'dessert' => 'Desserts',
    'boisson' => 'Boissons',
];

// Filtrage PHP
$plats_filtres = array_filter($plats, function($p) use ($filtre, $search) {
    if ($filtre !== 'tout' && $p['categorie'] !== $filtre) return false;
    if ($search && stripos($p['nom'] . ' ' . $p['description'], $search) === false) return false;
    return $p['disponible'];
});

include 'includes/header.php';
?>

<main>
    <section class="section section-alt">
        <h2>Carte CY-FAT</h2>
        <p class="section-intro">Tous nos plats préparés avec soin. Commandez en ligne ou venez manger sur place !</p>

        <!-- Filtres catégories -->
        <div class="filtres-bar">
            <?php foreach ($categories as $slug => $label): ?>
                <a href="carte.php?cat=<?= h($slug) ?><?= $search ? '&search='.h($search) : '' ?>"
                   class="filtre-btn <?= $filtre === $slug ? 'filtre-actif' : '' ?>">
                    <?= h($label) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <?php if ($search): ?>
            <p class="section-intro" style="color:var(--color-primary);">
                Résultats pour <strong>"<?= h($search) ?>"</strong> — <?= count($plats_filtres) ?> plat(s) trouvé(s).
                <a href="carte.php">Effacer</a>
            </p>
        <?php endif; ?>

        <!-- Menus -->
        <?php if ($filtre === 'tout' && !$search): ?>
        <h3 style="margin:30px 0 16px;color:var(--color-accent);">🍽️ Nos formules menus</h3>
        <div class="menus-grid">
            <?php foreach ($menus as $menu): if (!$menu['disponible']) continue; ?>
                <article class="menu-card">
                    <div class="menu-card-body">
                        <h3><?= h($menu['nom']) ?></h3>
                        <p><?= h($menu['description']) ?></p>
                        <p style="color:var(--color-muted);font-size:.85rem;">🕐 <?= h($menu['horaires']) ?></p>
                        <p class="price" style="font-size:1.2rem;margin-top:8px;"><?= number_format($menu['prix_total'], 2, ',', '') ?> €</p>
                        <?php if ($user && $user['role'] === 'client'): ?>
                            <button class="btn btn-primary add-to-cart"
                                    style="margin-top:10px;background:var(--color-primary);color:#fff;"
                                    data-id="<?= $menu['id'] ?>"
                                    data-type="menu"
                                    data-nom="<?= h($menu['nom']) ?>"
                                    data-prix="<?= $menu['prix_total'] ?>">
                                🛒 Ajouter au panier
                            </button>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Plats -->
        <h3 style="margin:30px 0 16px;color:var(--color-accent);">
            <?= $filtre === 'tout' ? '🥗 Plats à la carte' : h($categories[$filtre] ?? '') ?>
        </h3>

        <?php if (empty($plats_filtres)): ?>
            <p class="section-intro">Aucun plat trouvé.</p>
        <?php else: ?>
        <div class="plats-grid">
            <?php foreach ($plats_filtres as $plat): ?>
                <article class="plat-card">
                    <div class="plat-card-body">
                        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:8px;">
                            <h4><?= h($plat['nom']) ?></h4>
                            <span class="price"><?= number_format($plat['prix'], 2, ',', '') ?> €</span>
                        </div>
                        <p class="plat-desc"><?= h($plat['description']) ?></p>

                        <?php if (!empty($plat['allergenes'])): ?>
                        <p class="allergenes">⚠️ <?= h(implode(', ', $plat['allergenes'])) ?></p>
                        <?php endif; ?>

                        <p style="font-size:.8rem;color:var(--color-muted);">
                            🔥 <?= $plat['infos_nutritionnelles']['calories'] ?> kcal
                            · 🥩 <?= $plat['infos_nutritionnelles']['proteines'] ?>g prot.
                        </p>

                        <?php if ($user && $user['role'] === 'client'): ?>
                        <button class="btn btn-primary add-to-cart"
                                style="margin-top:10px;width:100%;background:var(--color-primary);color:#fff;"
                                data-id="<?= $plat['id'] ?>"
                                data-type="plat"
                                data-nom="<?= h($plat['nom']) ?>"
                                data-prix="<?= $plat['prix'] ?>">
                            🛒 Ajouter
                        </button>
                        <?php elseif (!$user): ?>
                        <a href="connexion.php" class="btn btn-secondary" style="margin-top:10px;display:block;text-align:center;border-color:var(--color-primary);color:var(--color-primary);">
                            Connectez-vous pour commander
                        </a>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </section>
</main>

<?php include 'includes/footer.php'; ?>

<script>
// Ajouter au panier (localStorage)
document.querySelectorAll('.add-to-cart').forEach(btn => {
    btn.addEventListener('click', function() {
        const id    = parseInt(this.dataset.id);
        const type  = this.dataset.type;
        const nom   = this.dataset.nom;
        const prix  = parseFloat(this.dataset.prix);

        let cart = JSON.parse(localStorage.getItem('cyfatCart') || '[]');
        const idx = cart.findIndex(i => i.id === id && i.type === type);

        if (idx >= 0) {
            cart[idx].quantite++;
        } else {
            cart.push({ id, type, nom, prix, quantite: 1 });
        }

        localStorage.setItem('cyfatCart', JSON.stringify(cart));
        updateCartCount();

        // Feedback visuel
        this.textContent = '✅ Ajouté !';
        setTimeout(() => this.innerHTML = '🛒 Ajouter' + (type === 'menu' ? ' au panier' : ''), 1200);
    });
});
</script>
