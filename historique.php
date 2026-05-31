<?php
// historique.php
require_once 'includes/config.php';
requireRole('client', 'connexion.php');

$user      = currentUser();
$commandes = loadJSON(DATA_COMMANDES);
$plats     = loadJSON(DATA_PLATS);

// Filtrer les commandes du client, les plus récentes en premier
$mes_commandes = array_filter($commandes, fn($c) => $c['client_id'] === $user['id']);
usort($mes_commandes, fn($a,$b) => strtotime($b['date_commande']) - strtotime($a['date_commande']));

$pageTitle  = 'CY-FAT — Mes commandes';
$activePage = '';
include 'includes/header.php';

$badge = [
    'attente_paiement' => ['Attente paiement', '#e8a020'],
    'en_attente'       => ['Reçue',            '#3a8fc0'],
    'en_preparation'   => ['En préparation',   '#c06010'],
    'pret'             => ['Prête',            '#4a9060'],
    'en_livraison'     => ['En livraison',     '#7050c0'],
    'livre'            => ['Livrée',           '#3a9060'],
    'abandonne'        => ['Abandonnée',       '#c03030'],
];
?>

<main>
<section class="section section-alt">
    <h2>Mes commandes</h2>
    <p class="section-intro">
        Bonjour <strong><?= h($user['prenom']) ?></strong> — 
        <?= count($mes_commandes) ?> commande(s) passée(s). 
        <span style="color:var(--color-primary);">⭐ <?= $user['points_fidelite'] ?? 0 ?> points fidélité</span>
        <?php if ($user['remise'] > 0): ?>
        · <span style="color:var(--color-primary);">🎁 Remise <?= $user['remise'] ?>%</span>
        <?php endif; ?>
    </p>

    <?php if (empty($mes_commandes)): ?>
        <p class="cart-empty" style="text-align:center;padding:40px 0;">
            Vous n'avez pas encore de commandes. <a href="carte.php">Commander maintenant →</a>
        </p>
    <?php else: ?>
    <div class="historique-list">
        <?php foreach ($mes_commandes as $cmd): 
            [$badgeLabel, $badgeColor] = $badge[$cmd['statut']] ?? ['Inconnu', '#999'];
        ?>
        <div class="histo-card">
            <div class="histo-header">
                <div>
                    <strong>Commande #<?= $cmd['id'] ?></strong>
                    <span class="statut-badge" style="background:<?= $badgeColor ?>20;color:<?= $badgeColor ?>;border:1px solid <?= $badgeColor ?>40;">
                        <?= $badgeLabel ?>
                    </span>
                </div>
                <div style="text-align:right;">
                    <span class="price"><?= number_format($cmd['total'], 2, ',', '') ?> €</span><br>
                    <small style="color:var(--color-muted);"><?= date('d/m/Y H:i', strtotime($cmd['date_commande'])) ?></small>
                </div>
            </div>

            <div class="histo-articles">
                <?php foreach ($cmd['articles'] as $art): ?>
                <span class="histo-article"><?= h($art['nom']) ?> ×<?= $art['quantite'] ?></span>
                <?php endforeach; ?>
            </div>

            <div class="histo-footer">
                <span style="font-size:.85rem;color:var(--color-muted);">
                    <?= ucfirst(str_replace('_', ' ', $cmd['mode'])) ?>
                    <?= $cmd['transaction_id'] ? ' · ' . h($cmd['transaction_id']) : '' ?>
                    <?php if ($cmd['note_commande']): ?>
                    · ⭐ Noté <?= $cmd['note_commande']['note'] ?>/5
                    <?php endif; ?>
                </span>
                <div style="display:flex;gap:8px;">
                    <a href="suivi_commande.php?id=<?= $cmd['id'] ?>" class="btn btn-secondary" style="padding:6px 14px;font-size:.85rem;border-color:var(--color-primary);color:var(--color-primary);">
                        Voir détails
                    </a>
                    <?php if ($cmd['statut'] === 'livre' && !$cmd['note_commande']): ?>
                    <a href="suivi_commande.php?id=<?= $cmd['id'] ?>#noter" class="btn btn-primary" style="padding:6px 14px;font-size:.85rem;background:var(--color-primary);color:#fff;">
                        ⭐ Noter
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>
</main>

<?php include 'includes/footer.php'; ?>
