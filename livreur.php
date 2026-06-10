<?php
require_once 'includes/config.php';
requireRole('livreur', 'connexion.php');

$user      = currentUser();
$commandes = loadJSON(DATA_COMMANDES);
$users     = loadJSON(DATA_USERS);

$mes_livraisons = array_filter($commandes, fn($c) => $c['livreur_id'] === $user['id']);
usort($mes_livraisons, fn($a,$b) => strtotime($b['date_commande']) - strtotime($a['date_commande']));

$en_cours  = array_filter($mes_livraisons, fn($c) => $c['statut'] === 'en_livraison');
$terminees = array_filter($mes_livraisons, fn($c) => in_array($c['statut'], ['livre','abandonne']));

function getUserById(array $users, int $id): ?array {
    foreach ($users as $u) if ($u['id'] === $id) return $u;
    return null;
}

$pageTitle  = 'CY-FAT — Livreur';
$activePage = '';
include 'includes/header.php';
?>

<main>
<section class="section section-alt" style="max-width:600px;">
    <h2>🛵 Mes livraisons</h2>
    <p class="section-intro">Bonjour <?= h($user['prenom']) ?> — <?= count($en_cours) ?> en cours, <?= count($terminees) ?> terminée(s).</p>

    <?php if (empty($en_cours)): ?>
    <div style="text-align:center;padding:40px;background:#fff;border-radius:16px;box-shadow:var(--shadow-soft);">
        <div style="font-size:3rem;margin-bottom:10px;">😴</div>
        <p>Aucune livraison en cours.<br>Attendez l'attribution par le restaurateur.</p>
    </div>
    <?php endif; ?>

    <?php foreach ($en_cours as $cmd):
        $client = getUserById($users, $cmd['client_id']);
    ?>
    <div class="livraison-detail-card">
        <div class="livraison-detail-header">
            <strong>Commande #<?= $cmd['id'] ?></strong>
            <span class="statut-badge" style="background:#7050c020;color:#7050c0;border:1px solid #7050c040;">🛵 En livraison</span>
        </div>

        <div class="livraison-adresse-block">
            <div class="livraison-adresse">📍 <?= h($cmd['adresse_livraison'] ?? 'Sur place') ?></div>
            <?php if ($cmd['code_interphone']): ?>
            <div class="livraison-interphone">🔑 Interphone : <strong><?= h($cmd['code_interphone']) ?></strong></div>
            <?php endif; ?>
        </div>

        <?php if ($client): ?>
        <div style="font-size:.9rem;color:var(--color-muted);margin:10px 0;">
            👤 <?= h($client['prenom'].' '.$client['nom']) ?>
            <?php if (!empty($client['telephone'])): ?>
            · 📞 <a href="tel:<?= h($client['telephone']) ?>" style="color:var(--color-primary);"><?= h($client['telephone']) ?></a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div style="background:rgba(0,0,0,.03);border-radius:10px;padding:10px 14px;margin:8px 0;">
            <?php foreach ($cmd['articles'] as $art): ?>
            <div style="display:flex;justify-content:space-between;font-size:.9rem;padding:2px 0;">
                <span><?= h($art['nom']) ?> ×<?= $art['quantite'] ?></span>
                <span class="price"><?= number_format($art['prix_unitaire']*$art['quantite'],2,',','') ?> €</span>
            </div>
            <?php endforeach; ?>
            <div style="display:flex;justify-content:space-between;font-weight:700;margin-top:6px;padding-top:6px;border-top:1px solid rgba(0,0,0,.06);">
                <span>Total</span>
                <span class="price"><?= number_format($cmd['total'],2,',','') ?> €</span>
            </div>
        </div>

        <?php if ($cmd['date_livraison_prevue']): ?>
        <p style="font-size:.85rem;color:var(--color-muted);">
            ⏰ Livraison prévue à <?= date('H:i', strtotime($cmd['date_livraison_prevue'])) ?>
        </p>
        <?php endif; ?>

        <?php if ($cmd['adresse_livraison']): ?>
        <a href="https://maps.google.com/?q=<?= urlencode($cmd['adresse_livraison']) ?>"
           target="_blank"
           class="btn btn-secondary livraison-maps-btn">
            🗺️ Ouvrir dans Maps
        </a>
        <?php endif; ?>

        <div class="livraison-btns">
            <form action="actions/update_commande.php" method="POST" style="flex:1;">
                <input type="hidden" name="commande_id" value="<?= $cmd['id'] ?>">
                <input type="hidden" name="statut" value="livre">
                <button type="submit" class="livraison-big-btn btn-livre">
                    ✅ Livraison terminée
                </button>
            </form>
            <form action="actions/update_commande.php" method="POST" style="flex:1;">
                <input type="hidden" name="commande_id" value="<?= $cmd['id'] ?>">
                <input type="hidden" name="statut" value="abandonne">
                <button type="submit" class="livraison-big-btn btn-abandonne"
                        onclick="return confirm('Confirmer abandon de la livraison ?')">
                    ❌ Adresse introuvable
                </button>
            </form>
        </div>
    </div>
    <?php endforeach; ?>

    <?php if (!empty($terminees)): ?>
    <h3 style="margin:30px 0 12px;color:var(--color-accent);">Livraisons terminées</h3>
    <?php foreach ($terminees as $cmd): ?>
    <div class="histo-card" style="opacity:.75;">
        <div class="histo-header">
            <div>
                <strong>Cmd #<?= $cmd['id'] ?></strong>
                <span class="statut-badge" style="background:<?= $cmd['statut']==='livre'?'#3a906020':'#c0303020' ?>;color:<?= $cmd['statut']==='livre'?'#3a9060':'#c03030' ?>;border:1px solid currentColor;">
                    <?= $cmd['statut'] === 'livre' ? '✅ Livré' : '❌ Abandonné' ?>
                </span>
            </div>
            <div style="text-align:right;">
                <span class="price"><?= number_format($cmd['total'],2,',','') ?> €</span><br>
                <small style="color:var(--color-muted);"><?= date('d/m H:i', strtotime($cmd['date_commande'])) ?></small>
            </div>
        </div>
        <p style="font-size:.85rem;color:var(--color-muted);margin-top:4px;">
            📍 <?= h($cmd['adresse_livraison'] ?? '—') ?>
        </p>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</section>
</main>

<?php include 'includes/footer.php'; ?>
<script>
setTimeout(() => location.reload(), 20000);
</script>
