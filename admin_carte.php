<?php
// admin_carte.php
require_once 'includes/config.php';

// Seuls le restaurateur et l'admin ont accès
$user = currentUser();
if (!$user || !in_array($user['role'], ['restaurateur', 'admin'])) {
    header('Location: index.php');
    exit;
}

$plats = loadJSON(DATA_PLATS);
$categories = [
    'entree'  => 'Entrées',
    'burger'  => 'Burgers',
    'plat'    => 'Plats',
    'bowl'    => 'Bowls & Salades',
    'dessert' => 'Desserts',
    'boisson' => 'Boissons',
];

$pageTitle  = 'CY-FAT — Gérer la Carte';
$activePage = '';
include 'includes/header.php';
?>

<main>
<section class="section section-alt">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h2>Gestion de la Carte</h2>
        <a href="#form-plat" class="btn btn-primary" style="background:var(--color-primary); color:#fff; text-decoration:none; padding:10px 20px; border-radius:8px;">+ Ajouter un plat</a>
    </div>

    <div class="admin-table-container" style="background:#fff; padding:20px; border-radius:15px; box-shadow:var(--shadow-soft);">
        <table style="width:100%; border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:2px solid var(--color-bg); text-align:left;">
                    <th style="padding:12px;">Image</th>
                    <th>Nom & Catégorie</th>
                    <th>Prix</th>
                    <th>Statut</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($plats as $p): ?>
                <tr style="border-bottom:1px solid #eee;">
                    <td style="padding:10px;"><img src="img/<?= h($p['image']) ?>" alt="" style="width:50px; height:50px; object-fit:cover; border-radius:5px; background:#eee;"></td>
                    <td>
                        <strong><?= h($p['nom']) ?></strong><br>
                        <small style="color:var(--color-muted);"><?= $categories[$p['categorie']] ?? $p['categorie'] ?></small>
                    </td>
                    <td><?= number_format($p['prix'], 2) ?> €</td>
                    <td>
                        <span style="padding:4px 8px; border-radius:12px; font-size:0.8rem; background:<?= $p['disponible'] ? '#e6f7ee;color:#2d7a4f;' : '#fdecea;color:#c03030;' ?>">
                            <?= $p['disponible'] ? 'En vente' : 'Épuisé' ?>
                        </span>
                    </td>
                    <td style="text-align:right;">
                        <form action="actions/save_plat.php" method="POST" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                            <input type="hidden" name="action" value="toggle_dispo">
                            <button type="submit" class="btn-secondary" style="cursor:pointer; border:1px solid #ccc; padding:5px 10px; border-radius:5px;" title="Changer disponibilité">🔄</button>
                        </form>
                        <form action="actions/save_plat.php" method="POST" style="display:inline;" onsubmit="return confirm('Supprimer ce plat ?');">
                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                            <input type="hidden" name="action" value="delete">
                            <button type="submit" style="background:none; border:none; cursor:pointer; font-size:1.2rem;" title="Supprimer">🗑️</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div id="form-plat" style="margin-top:40px; background:#fff; padding:30px; border-radius:15px; box-shadow:var(--shadow-soft);">
        <h3>Ajouter un nouveau plat</h3>
        <form action="actions/save_plat.php" method="POST" style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-top:20px;">
            <input type="hidden" name="action" value="add">
            
            <div>
                <label style="display:block; margin-bottom:5px;">Nom du plat *</label>
                <input type="text" name="nom" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;">
            </div>
            
            <div>
                <label style="display:block; margin-bottom:5px;">Catégorie *</label>
                <select name="categorie" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;">
                    <?php foreach ($categories as $val => $libelle): ?>
                        <option value="<?= $val ?>"><?= $libelle ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="grid-column: span 2;">
                <label style="display:block; margin-bottom:5px;">Description</label>
                <textarea name="description" rows="2" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;"></textarea>
            </div>

            <div>
                <label style="display:block; margin-bottom:5px;">Prix (€) *</label>
                <input type="number" step="0.01" name="prix" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;">
            </div>

            <div>
                <label style="display:block; margin-bottom:5px;">Nom de l'image (ex: burger.jpg)</label>
                <input type="text" name="image" placeholder="image_defaut.jpg" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;">
            </div>

            <div style="grid-column: span 2;">
                <button type="submit" class="btn btn-primary" style="width:100%; background:var(--color-primary); color:#fff; border:none; padding:15px; border-radius:8px; cursor:pointer; font-weight:bold;">
                    Enregistrer le plat
                </button>
            </div>
        </form>
    </div>
</section>
</main>

<?php include 'includes/footer.php'; ?>
