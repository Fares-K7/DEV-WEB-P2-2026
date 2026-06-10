<?php
// admin_users.php
require_once 'includes/config.php';
requireRole('admin', 'connexion.php');

$users     = loadJSON(DATA_USERS);
$commandes = loadJSON(DATA_COMMANDES);

// Trier : admins en premier, puis par nom
usort($users, function($a, $b) {
    if ($a['role'] === 'admin' && $b['role'] !== 'admin') return -1;
    if ($a['role'] !== 'admin' && $b['role'] === 'admin') return 1;
    return strcmp($a['nom'], $b['nom']);
});

// Filtres
$filtre_role   = sanitize($_GET['role']   ?? 'tout');
$filtre_statut = sanitize($_GET['statut'] ?? 'tout');
$search        = sanitize($_GET['q']      ?? '');

$users_filtres = array_filter($users, function($u) use ($filtre_role, $filtre_statut, $search) {
    if ($filtre_role !== 'tout' && $u['role'] !== $filtre_role) return false;
    if ($filtre_statut !== 'tout' && $u['statut'] !== $filtre_statut) return false;
    if ($search && stripos($u['nom'].$u['prenom'].$u['email'], $search) === false) return false;
    return true;
});

$pageTitle  = 'CY-FAT — Admin — Utilisateurs';
$activePage = '';
include 'includes/header.php';
?>

<main>
<section class="section section-alt">
    <h2>👑 Administration — Utilisateurs</h2>
    <p class="section-intro"><?= count($users) ?> utilisateurs enregistrés.</p>

    <!-- Filtres -->
    <form method="GET" class="admin-filtres">
        <input type="text" name="q" value="<?= h($search) ?>" placeholder="Rechercher un utilisateur...">
        <select name="role">
            <option value="tout">Tous les rôles</option>
            <?php foreach (['admin','client','restaurateur','livreur'] as $r): ?>
            <option value="<?= $r ?>" <?= $filtre_role === $r ? 'selected' : '' ?>><?= ucfirst($r) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="statut">
            <option value="tout">Tous statuts</option>
            <option value="actif"  <?= $filtre_statut==='actif'   ? 'selected':'' ?>>Actif</option>
            <option value="bloque" <?= $filtre_statut==='bloque'  ? 'selected':'' ?>>Bloqué</option>
        </select>
        <button type="submit" class="btn btn-primary" style="background:var(--color-primary);color:#fff;">Filtrer</button>
        <a href="admin_users.php" class="btn btn-secondary" style="border-color:var(--color-primary);color:var(--color-primary);">Réinitialiser</a>
    </form>
<div style="background: var(--color-surface); padding: 20px; border-radius: 12px; box-shadow: var(--shadow-soft); margin-bottom: 30px;">
    <h3 style="color: var(--color-accent); margin-bottom: 12px;">➕ Créer un nouvel utilisateur (Admin / Staff)</h3>
    
    <?php if ($flash = getFlash()): ?>
        <div style="padding: 10px; margin-bottom: 15px; border-radius: 6px; background: <?= $flash['type'] === 'success' ? '#e6f7ee' : '#fdecea' ?>; color: <?= $flash['type'] === 'success' ? '#2d7a4f' : '#c03030' ?>;">
            <?= h($flash['msg']) ?>
        </div>
    <?php endif; ?>

    <form action="actions/admin_create_user.php" method="POST" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; align-items: end;">
        <div>
            <label style="font-size: 0.85rem; font-weight: 600;">Prénom</label>
            <input type="text" name="prenom" required style="width:100%; padding: 8px; border-radius: 6px; border: 1px solid rgba(0,0,0,.15);">
        </div>
        <div>
            <label style="font-size: 0.85rem; font-weight: 600;">Nom</label>
            <input type="text" name="nom" required style="width:100%; padding: 8px; border-radius: 6px; border: 1px solid rgba(0,0,0,.15);">
        </div>
        <div>
            <label style="font-size: 0.85rem; font-weight: 600;">Email</label>
            <input type="email" name="email" required style="width:100%; padding: 8px; border-radius: 6px; border: 1px solid rgba(0,0,0,.15);">
        </div>
        <div>
            <label style="font-size: 0.85rem; font-weight: 600;">Mot de passe</label>
            <input type="password" name="password" required placeholder="Min 6 caract." style="width:100%; padding: 8px; border-radius: 6px; border: 1px solid rgba(0,0,0,.15);">
        </div>
        <div>
            <label style="font-size: 0.85rem; font-weight: 600;">Rôle</label>
            <select name="role" style="width:100%; padding: 8px; border-radius: 6px; border: 1px solid rgba(0,0,0,.15);">
                <option value="client">Client</option>
                <option value="restaurateur">Restaurateur</option>
                <option value="livreur">Livreur</option>
                <option value="admin">Administrateur</option>
            </select>
        </div>
        <div>
            <button type="submit" class="btn btn-primary" style="background: var(--color-primary); color:#fff; width:100%; padding: 10px; border:none; border-radius:6px; font-weight:600; cursor:pointer;">
                Créer l'utilisateur
            </button>
        </div>
    </form>
</div>
    <!-- Tableau utilisateurs -->
    <div class="admin-table-wrap">
    <table class="admin-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Nom / Email</th>
                <th>Rôle</th>
                <th>Statut</th>
                <th>Niveau</th>
                <th>Remise</th>
                <th>Cmds</th>
                <th>Inscription</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($users_filtres as $u):
            $nb_cmds = count(array_filter($commandes, fn($c) => $c['client_id'] === $u['id']));
        ?>
        <tr class="<?= $u['statut'] === 'bloque' ? 'row-bloque' : '' ?>">
            <td><?= $u['id'] ?></td>
            <td>
                <strong><?= h($u['prenom'] . ' ' . $u['nom']) ?></strong><br>
                <small style="color:var(--color-muted);"><?= h($u['email']) ?></small>
            </td>
            <td><span class="role-badge role-<?= h($u['role']) ?>"><?= h(ucfirst($u['role'])) ?></span></td>
            <td>
                <?php if ($u['statut'] === 'bloque'): ?>
                    <span style="color:#c03030;font-weight:600;">🔒 Bloqué</span>
                <?php else: ?>
                    <span style="color:#3a9060;font-weight:600;">✅ Actif</span>
                <?php endif; ?>
            </td>
            <td><?= h($u['niveau'] ?? 'Standard') ?></td>
            <td><?= $u['remise'] ?? 0 ?>%</td>
            <td><?= $nb_cmds ?></td>
            <td><?= $u['date_inscription'] ?? '—' ?></td>
            <td>
                <div class="admin-actions">
                    <!-- Bloquer / Activer -->
                    <form action="actions/update_user.php" method="POST" style="display:inline;">
                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                        <?php if ($u['statut'] === 'actif'): ?>
                            <input type="hidden" name="action" value="bloquer">
                            <button type="submit" class="admin-btn admin-btn-danger" title="Bloquer">🔒</button>
                        <?php else: ?>
                            <input type="hidden" name="action" value="activer">
                            <button type="submit" class="admin-btn admin-btn-success" title="Activer">🔓</button>
                        <?php endif; ?>
                    </form>

                    <!-- Modifier niveau -->
                    <form action="actions/update_user.php" method="POST" style="display:inline;display:flex;gap:4px;align-items:center;">
                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                        <input type="hidden" name="action" value="set_niveau">
                        <select name="value" style="font-size:.8rem;padding:3px 6px;border-radius:6px;border:1px solid rgba(0,0,0,.15);">
                            <option value="Standard" <?= ($u['niveau']??'Standard')==='Standard'?'selected':'' ?>>Standard</option>
                            <option value="Premium"  <?= ($u['niveau']??'')==='Premium' ?'selected':'' ?>>Premium</option>
                            <option value="VIP"      <?= ($u['niveau']??'')==='VIP'     ?'selected':'' ?>>VIP</option>
                        </select>
                        <button type="submit" class="admin-btn" title="Changer niveau">✔</button>
                    </form>

                    <!-- Remise -->
                    <form action="actions/update_user.php" method="POST" style="display:inline;display:flex;gap:4px;align-items:center;">
                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                        <input type="hidden" name="action" value="set_remise">
                        <input type="number" name="value" value="<?= $u['remise']??0 ?>" min="0" max="50"
                               style="width:52px;font-size:.8rem;padding:3px 6px;border-radius:6px;border:1px solid rgba(0,0,0,.15);" title="Remise %">
                        <button type="submit" class="admin-btn" title="Appliquer remise">%</button>
                    </form>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>

    <div style="margin-top:30px;text-align:center;">
        <a href="restaurateur.php" class="btn btn-secondary" style="border-color:var(--color-primary);color:var(--color-primary);">🍳 Vue restaurateur</a>
    </div>
</section>
</main>

<?php include 'includes/footer.php'; ?>

