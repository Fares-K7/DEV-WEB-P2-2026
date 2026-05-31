<?php
// restaurateur.php
require_once 'includes/config.php';
requireLogin('connexion.php');

$user = currentUser();
if (!in_array($user['role'], ['restaurateur', 'admin'])) {
    header('Location: index.php');
    exit;
}

$commandes = loadJSON(DATA_COMMANDES);
$users     = loadJSON(DATA_USERS);

// Livreurs disponibles
$livreurs = array_filter($users, fn($u) => $u['role'] === 'livreur' && $u['statut'] === 'actif');

// Trier les commandes par date décroissante
usort($commandes, fn($a,$b) => strtotime($b['date_commande']) - strtotime($a['date_commande']));

// Grouper par statut
$groups = [
    'en_attente'     => ['title' => '📥 En attente',    'color' => '#3a8fc0', 'cmds' => []],
    'en_preparation' => ['title' => '👨‍🍳 En préparation', 'color' => '#c06010', 'cmds' => []],
    'pret'           => ['title' => '✅ Prêt',          'color' => '#4a9060', 'cmds' => []],
    'en_livraison'   => ['title' => '🛵 En livraison',  'color' => '#7050c0', 'cmds' => []],
    'livre'          => ['title' => '🎉 Livrées',       'color' => '#3a9060', 'cmds' => []],
];

foreach ($commandes as $cmd) {
    if (isset($groups[$cmd['statut']])) {
        $groups[$cmd['statut']]['cmds'][] = $cmd;
    }
}

// Helper: trouver un user par id
function getUserById(array $users, int $id): ?array {
    foreach ($users as $u) if ($u['id'] === $id) return $u;
    return null;
}

$pageTitle  = 'CY-FAT — Cuisine';
$activePage = '';
include 'includes/header.php';
?>

<main>
<section class="section section-alt">
    <h2>🍳 Espace restaurateur</h2>
    <div style="margin-bottom: 20px; display: flex; gap: 10px;">
    <a href="admin_carte.php" class="btn btn-secondary" style="border-color:var(--color-primary); color:var(--color-primary); text-decoration:none; padding:8px 15px; border-radius:8px;">
        📖 Gérer la carte (Plats)
    </a>
    </div>
    <p class="section-intro">Vue restaurateur — Toutes les commandes en temps réel.</p>

    <!-- Résumé rapide -->
    <div class="resto-stats">
        <?php foreach ($groups as $key => $g): if ($key === 'livre') continue; ?>
        <div class="resto-stat-card" style="border-top:3px solid <?= $g['color'] ?>;">
            <span class="stat-val" style="color:<?= $g['color'] ?>;"><?= count($g['cmds']) ?></span>
            <span class="stat-lbl"><?= $g['title'] ?></span>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Colonnes Kanban -->
    <div class="kanban-board">
        <?php foreach ($groups as $statut => $group): ?>
        <div class="kanban-col">
            <div class="kanban-col-header" style="border-top:3px solid <?= $group['color'] ?>;">
                <?= $group['title'] ?> <span class="kanban-count"><?= count($group['cmds']) ?></span>
            </div>

            <?php foreach ($group['cmds'] as $cmd):
                $client = getUserById($users, $cmd['client_id']);
                $livreur = $cmd['livreur_id'] ? getUserById($users, $cmd['livreur_id']) : null;
            ?>
            <div class="kanban-card">
                <div class="kanban-card-header">
                    <strong>Cmd #<?= $cmd['id'] ?></strong>
                    <small><?= date('H:i', strtotime($cmd['date_commande'])) ?></small>
                </div>

                <div style="font-size:.85rem;color:var(--color-muted);margin-bottom:6px;">
                    <?= $client ? h($client['prenom'].' '.$client['nom']) : 'Client inconnu' ?>
                    · <?= h(ucfirst(str_replace('_',' ',$cmd['mode']))) ?>
                </div>

                <ul class="kanban-articles">
                    <?php foreach ($cmd['articles'] as $art): ?>
                    <li><?= h($art['nom']) ?> ×<?= $art['quantite'] ?></li>
                    <?php endforeach; ?>
                </ul>

                <div style="display:flex;justify-content:space-between;align-items:center;margin-top:6px;">
                    <strong class="price"><?= number_format($cmd['total'],2,',','') ?> €</strong>
                    <?php if ($cmd['mode'] === 'livraison' && $cmd['adresse_livraison']): ?>
                    <small style="color:var(--color-muted);font-size:.75rem;">📍 <?= h(substr($cmd['adresse_livraison'],0,25)) ?>…</small>
                    <?php endif; ?>
                </div>

                <!-- Boutons d'action selon statut -->
                <div class="kanban-actions">
                    <?php if ($statut === 'en_attente'): ?>
                    <form action="actions/update_commande.php" method="POST">
                        <input type="hidden" name="commande_id" value="<?= $cmd['id'] ?>">
                        <input type="hidden" name="statut" value="en_preparation">
                        <button type="submit" class="kanban-btn" style="background:#c0601020;color:#c06010;border-color:#c06010;">
                            👨‍🍳 Commencer
                        </button>
                    </form>

                    <?php elseif ($statut === 'en_preparation'): ?>
                    <form action="actions/update_commande.php" method="POST">
                        <input type="hidden" name="commande_id" value="<?= $cmd['id'] ?>">
                        <input type="hidden" name="statut" value="pret">
                        <button type="submit" class="kanban-btn" style="background:#4a906020;color:#4a9060;border-color:#4a9060;">
                            ✅ Marquer prêt
                        </button>
                    </form>

                    <?php elseif ($statut === 'pret'): ?>
                        <?php if ($cmd['mode'] === 'livraison'): ?>
                        <form action="actions/update_commande.php" method="POST" style="display:flex;gap:6px;flex-wrap:wrap;">
                            <input type="hidden" name="commande_id" value="<?= $cmd['id'] ?>">
                            <input type="hidden" name="statut" value="en_livraison">
                            <select name="livreur_id" style="font-size:.8rem;padding:4px 8px;border-radius:6px;border:1px solid rgba(0,0,0,.15);flex:1;">
                                <option value="">Choisir livreur</option>
                                <?php foreach ($livreurs as $l): ?>
                                <option value="<?= $l['id'] ?>" <?= $livreur && $livreur['id']===$l['id']?'selected':'' ?>>
                                    <?= h($l['prenom'].' '.$l['nom']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="kanban-btn" style="background:#7050c020;color:#7050c0;border-color:#7050c0;">
                                🛵 Envoyer
                            </button>
                        </form>
                        <?php else: ?>
                        <form action="actions/update_commande.php" method="POST">
                            <input type="hidden" name="commande_id" value="<?= $cmd['id'] ?>">
                            <input type="hidden" name="statut" value="livre">
                            <button type="submit" class="kanban-btn" style="background:#3a906020;color:#3a9060;border-color:#3a9060;">
                                🎉 Remis au client
                            </button>
                        </form>
                        <?php endif; ?>

                    <?php elseif ($statut === 'en_livraison'): ?>
                        <span style="font-size:.8rem;color:var(--color-muted);">
                            Livreur : <?= $livreur ? h($livreur['prenom']) : '—' ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>

            <?php if (empty($group['cmds'])): ?>
            <p style="font-size:.85rem;color:var(--color-muted);text-align:center;padding:20px 0;">Aucune commande</p>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</section>
</main>

<?php include 'includes/footer.php'; ?>
<section class="section section-alt" style="margin-top: 40px;">
    <h2>📦 Gestion des stocks & Disponibilité</h2>
    <p class="section-intro">Rendez un plat disponible ou indisponible à la carte instantanément.</p>

    <div style="background: var(--color-surface); padding: 20px; border-radius: 12px; box-shadow: var(--shadow-soft);">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="border-bottom: 2px solid rgba(0,0,0,0.1); color: var(--color-accent);">
                    <th style="padding: 10px;">Plat</th>
                    <th style="padding: 10px;">Catégorie</th>
                    <th style="padding: 10px;">Prix</th>
                    <th style="padding: 10px; text-align: center;">Statut</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $plats_stocks = loadJSON(DATA_PLATS);
                foreach ($plats_stocks as $p): 
                ?>
                <tr style="border-bottom: 1px solid rgba(0,0,0,0.05);">
                    <td style="padding: 10px;"><strong><?= h($p['nom']) ?></strong></td>
                    <td style="padding: 10px;"><span class="category-badge" style="background: rgba(0,0,0,0.05); padding: 4px 8px; border-radius: 6px; font-size: 0.85rem;"><?= h($p['categorie']) ?></span></td>
                    <td style="padding: 10px;"><?= number_format($p['prix'], 2, ',', '') ?> €</td>
                    <td style="padding: 10px; text-align: center;">
                        <form action="actions/toggle_plat.php" method="POST" style="margin: 0;">
                            <input type="hidden" name="plat_id" value="<?= $p['id'] ?>">
                            <?php if ($p['disponible']): ?>
                                <button type="submit" class="btn" style="background: #e6f7ee; color: #2d7a4f; border: 1px solid #2d7a4f; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-weight: 600;">
                                    🟢 En Stock
                                </button>
                            <?php else: ?>
                                <button type="submit" class="btn" style="background: #fdecea; color: #c03030; border: 1px solid #c03030; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-weight: 600;">
                                    🔴 Épuisé
                                </button>
                            <?php endif; ?>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<script>
// Auto-refresh toutes les 30 secondes
setTimeout(() => location.reload(), 30000);
</script>
