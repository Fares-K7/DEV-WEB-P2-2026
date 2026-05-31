<?php
// suivi_commande.php
require_once 'includes/config.php';
requireRole('client', 'connexion.php');

$user = currentUser();
$id   = (int)($_GET['id'] ?? 0);

$commandes = loadJSON(DATA_COMMANDES);
$commande  = null;
foreach ($commandes as $c) {
    if ($c['id'] === $id && $c['client_id'] === $user['id']) {
        $commande = $c;
        break;
    }
}

if (!$commande) {
    setFlash('error', 'Commande introuvable.');
    header('Location: historique.php');
    exit;
}

$pageTitle  = 'CY-FAT — Suivi commande #' . $id;
$activePage = '';
include 'includes/header.php';

$statuts = [
    'attente_paiement' => ['label' => 'Attente paiement', 'icon' => '💳', 'step' => 0],
    'en_attente'       => ['label' => 'Commande reçue',   'icon' => '📥', 'step' => 1],
    'en_preparation'   => ['label' => 'En préparation',   'icon' => '👨‍🍳', 'step' => 2],
    'pret'             => ['label' => 'Prêt',             'icon' => '✅', 'step' => 3],
    'en_livraison'     => ['label' => 'En livraison',     'icon' => '🛵', 'step' => 4],
    'livre'            => ['label' => 'Livré',            'icon' => '🎉', 'step' => 5],
];
$currentStep = $statuts[$commande['statut']]['step'] ?? 0;
?>

<main>
<section class="section section-alt">
    <h2>Suivi commande #<?= $commande['id'] ?></h2>
    <p class="section-intro">Passée le <?= date('d/m/Y à H:i', strtotime($commande['date_commande'])) ?></p>

    <!-- Timeline statut -->
    <div class="statut-timeline">
        <?php foreach ($statuts as $key => $s): if ($s['step'] === 0) continue; ?>
        <div class="statut-step <?= $currentStep >= $s['step'] ? 'statut-done' : '' ?> <?= $commande['statut'] === $key ? 'statut-current' : '' ?>">
            <div class="statut-icon"><?= $s['icon'] ?></div>
            <div class="statut-label"><?= $s['label'] ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="suivi-grid">
        <!-- Détail articles -->
        <div class="suivi-card">
            <h3>Articles commandés</h3>
            <ul class="suivi-articles">
                <?php foreach ($commande['articles'] as $art): ?>
                <li>
                    <span><?= h($art['nom']) ?> × <?= $art['quantite'] ?></span>
                    <span class="price"><?= number_format($art['prix_unitaire'] * $art['quantite'], 2, ',', '') ?> €</span>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php if (($commande['remise_appliquee'] ?? 0) > 0): ?>
            <p style="color:var(--color-primary);font-size:.9rem;margin-top:8px;">
                🎁 Remise <?= $commande['remise_appliquee'] ?>% appliquée
            </p>
            <?php endif; ?>
            <div style="display:flex;justify-content:space-between;font-weight:700;margin-top:12px;padding-top:10px;border-top:1px solid rgba(0,0,0,.07);">
                <span>Total payé</span>
                <span class="price"><?= number_format($commande['total'], 2, ',', '') ?> €</span>
            </div>
        </div>

        <!-- Infos livraison -->
        <div class="suivi-card">
            <h3>Informations</h3>
            <p><strong>Mode :</strong> <?= h(ucfirst(str_replace('_', ' ', $commande['mode']))) ?></p>
            <?php if ($commande['adresse_livraison']): ?>
            <p><strong>Adresse :</strong> <?= h($commande['adresse_livraison']) ?></p>
            <?php if ($commande['code_interphone']): ?>
            <p><strong>Interphone :</strong> <?= h($commande['code_interphone']) ?></p>
            <?php endif; ?>
            <?php endif; ?>
            <?php if ($commande['date_livraison_prevue']): ?>
            <p><strong>Heure estimée :</strong> <?= date('H:i', strtotime($commande['date_livraison_prevue'])) ?></p>
            <?php endif; ?>
            <p><strong>Paiement :</strong>
                <?= $commande['paiement_statut'] === 'paye' ? '✅ Payé' : '⏳ En attente' ?>
                <?php if ($commande['transaction_id']): ?>
                <br><small style="color:var(--color-muted);"><?= h($commande['transaction_id']) ?></small>
                <?php endif; ?>
            </p>

            <!-- Bouton noter si livré sans note -->
            <?php if ($commande['statut'] === 'livre' && !$commande['note_commande']): ?>
            <div style="margin-top:16px;padding-top:12px;border-top:1px solid rgba(0,0,0,.07);">
                <h4>Noter cette commande</h4>
                <form action="actions/noter.php" method="POST" style="display:flex;flex-direction:column;gap:8px;margin-top:8px;">
                    <input type="hidden" name="commande_id" value="<?= $commande['id'] ?>">
                    <div class="stars-input" id="stars-input">
                        <?php for ($i=1; $i<=5; $i++): ?>
                        <button type="button" class="star-btn" data-value="<?= $i ?>">★</button>
                        <?php endfor; ?>
                    </div>
                    <input type="hidden" name="note" id="note-val" value="5">
                    <textarea name="commentaire" rows="2" placeholder="Votre avis..." style="padding:8px;border-radius:8px;border:1px solid rgba(0,0,0,.14);font-family:inherit;"></textarea>
                    <button type="submit" class="btn btn-primary" style="background:var(--color-primary);color:#fff;">Envoyer</button>
                </form>
            </div>
            <?php elseif ($commande['note_commande']): ?>
            <p style="margin-top:10px;">⭐ Note donnée : <?= $commande['note_commande']['note'] ?>/5</p>
            <?php endif; ?>
        </div>
    </div>

    <div style="text-align:center;margin-top:24px;">
        <a href="historique.php" class="btn btn-secondary" style="border-color:var(--color-primary);color:var(--color-primary);">← Historique</a>
    </div>
</section>
</main>

<?php include 'includes/footer.php'; ?>
<script>
const starBtns = document.querySelectorAll('.star-btn');
const noteVal  = document.getElementById('note-val');
if (starBtns.length) {
    function setNote(n) {
        noteVal.value = n;
        starBtns.forEach(b => b.classList.toggle('star-btn-active', +b.dataset.value <= n));
    }
    starBtns.forEach(b => b.addEventListener('click', () => setNote(+b.dataset.value)));
    setNote(5);
}
</script>
