<?php
require_once 'includes/config.php';
requireRole('client', 'connexion.php');

$user = currentUser();
$pageTitle  = 'CY-FAT — Mon profil';
$activePage = '';
include 'includes/header.php';

$commandes    = loadJSON(DATA_COMMANDES);
$mes_commandes = array_filter($commandes, fn($c) => $c['client_id'] === $user['id']);
$nb_cmds      = count($mes_commandes);
$total_depense = array_sum(array_column(array_values($mes_commandes), 'total'));
?>

<main>
<section class="section section-alt">
    <h2>Mon profil</h2>
    <p class="section-intro">Bienvenue, <strong><?= h($user['prenom'] . ' ' . $user['nom']) ?></strong> !</p>

    <div class="profil-layout">

        <div class="profil-card">
    <div class="profil-card-header">
        <h3>Informations personnelles</h3>
    </div>

    <?php if ($flash = getFlash()): ?>
        <div class="flash flash-<?= $flash['type'] ?>" style="padding: 10px; margin-bottom: 15px; border-radius: 6px; background: <?= $flash['type'] === 'success' ? '#e6f7ee' : '#fdecea' ?>; color: <?= $flash['type'] === 'success' ? '#2d7a4f' : '#c03030' ?>;">
            <?= h($flash['msg']) ?>
        </div>
    <?php endif; ?>

    <form action="actions/update_profile.php" method="POST" style="display: flex; flex-direction: column; gap: 12px;">
        <div class="profil-field" style="display: flex; flex-direction: column;">
            <label style="font-weight: 600; margin-bottom: 4px;">Prénom *</label>
            <input type="text" name="prenom" value="<?= h($user['prenom']) ?>" required style="padding: 8px; border-radius: 6px; border: 1px solid rgba(0,0,0,.15);">
        </div>
        
        <div class="profil-field" style="display: flex; flex-direction: column;">
            <label style="font-weight: 600; margin-bottom: 4px;">Nom *</label>
            <input type="text" name="nom" value="<?= h($user['nom']) ?>" required style="padding: 8px; border-radius: 6px; border: 1px solid rgba(0,0,0,.15);">
        </div>
        
        <div class="profil-field" style="display: flex; flex-direction: column;">
            <label style="font-weight: 600; margin-bottom: 4px;">Téléphone</label>
            <input type="tel" name="telephone" value="<?= h($user['telephone'] ?? '') ?>" style="padding: 8px; border-radius: 6px; border: 1px solid rgba(0,0,0,.15);">
        </div>
        
        <div class="profil-field" style="display: flex; flex-direction: column;">
            <label style="font-weight: 600; margin-bottom: 4px;">Adresse de livraison</label>
            <input type="text" name="adresse" value="<?= h($user['adresse'] ?? '') ?>" style="padding: 8px; border-radius: 6px; border: 1px solid rgba(0,0,0,.15);">
        </div>
        
        <div class="profil-field" style="display: flex; flex-direction: column;">
            <label style="font-weight: 600; margin-bottom: 4px;">Code interphone</label>
            <input type="text" name="code_interphone" value="<?= h($user['code_interphone'] ?? '') ?>" style="padding: 8px; border-radius: 6px; border: 1px solid rgba(0,0,0,.15);">
        </div>

        <hr style="border: 0; border-top: 1px solid rgba(0,0,0,.1); margin: 10px 0;">
        <p style="font-size: 0.9rem; color: var(--color-muted);">Laissez les champs suivants vides si vous ne souhaitez pas modifier votre mot de passe.</p>

        <div class="profil-field" style="display: flex; flex-direction: column;">
            <label style="font-weight: 600; margin-bottom: 4px;">Nouveau mot de passe</label>
            <input type="password" name="new_password" placeholder="Minimum 6 caractères" style="padding: 8px; border-radius: 6px; border: 1px solid rgba(0,0,0,.15);">
        </div>

        <button type="submit" class="btn btn-primary" style="background: var(--color-primary); color: #fff; padding: 10px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; margin-top: 10px;">
            💾 Enregistrer les modifications
        </button>
    </form>
</div>

        <div class="profil-card">
            <h3>Compte fidélité</h3>

            <div class="fidelite-badge">
                <div class="fidelite-niveau niveau-<?= strtolower($user['niveau']) ?>">
                    <?= match($user['niveau']) {
                        'VIP'     => '👑 VIP',
                        'Premium' => '⭐ Premium',
                        default   => '🙋 Standard',
                    } ?>
                </div>
                <?php if ($user['remise'] > 0): ?>
                <div class="fidelite-remise">🎁 Remise permanente : <strong><?= $user['remise'] ?>%</strong></div>
                <?php endif; ?>
            </div>

            <div class="fidelite-points">
                <span class="points-score"><?= $user['points_fidelite'] ?? 0 ?></span>
                <span class="points-label">points fidélité</span>
            </div>

            <div style="margin-top:16px;padding-top:14px;border-top:1px solid rgba(0,0,0,.06);">
                <h4 style="margin-bottom:10px;color:var(--color-accent);">Mes statistiques</h4>
                <div class="profil-stats">
                    <div class="stat-item">
                        <span class="stat-val"><?= $nb_cmds ?></span>
                        <span class="stat-lbl">Commandes</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-val"><?= number_format($total_depense, 2, ',', '') ?> €</span>
                        <span class="stat-lbl">Total dépensé</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-val"><?= $nb_cmds > 0 ? number_format($total_depense/$nb_cmds, 2, ',', '') . ' €' : '—' ?></span>
                        <span class="stat-lbl">Panier moyen</span>
                    </div>
                </div>
            </div>

            <div style="margin-top:16px;">
                <a href="historique.php" class="btn btn-primary" style="background:var(--color-primary);color:#fff;width:100%;display:block;text-align:center;">
                    📋 Voir mes commandes
                </a>
                <a href="carte.php" class="btn btn-secondary" style="margin-top:8px;border-color:var(--color-primary);color:var(--color-primary);width:100%;display:block;text-align:center;">
                    🍔 Commander
                </a>
            </div>
        </div>
    </div>
</section>
</main>

<?php include 'includes/footer.php'; ?>
