<?php
// livraison.php
require_once 'includes/config.php';
$pageTitle  = 'CY-FAT — Livraison';
$activePage = 'livraison.php';
include 'includes/header.php';
?>
<main>
    <section class="section section-alt">
        <h2>Livraison CY-FAT</h2>
        <p class="section-intro">Livraison rapide sur Cergy-Pontoise et environs. Commandez en ligne, livré en 25-35 min !</p>
        <div class="livraison-grid">
            <div class="livraison-card">
                <h3>Zone de livraison</h3>
                <ul>
                    <li>✅ Cergy-Pontoise</li>
                    <li>✅ Pontoise</li>
                    <li>✅ Cergy-Préfecture</li>
                    <li>✅ Université Cergy</li>
                    <li>€2 frais (gratuit &gt; 15€)</li>
                </ul>
            </div>
            <div class="livraison-card">
                <h3>Horaires</h3>
                <ul>
                    <li>11h30 – 14h00 (midi)</li>
                    <li>18h30 – 22h00 (soir)</li>
                    <li>Fermé dimanche</li>
                </ul>
            </div>
            <div class="livraison-card">
                <h3>Commander</h3>
                <p>Choisissez depuis la <a href="carte.php">Carte</a>, ajoutez au panier, et validez votre adresse.</p>
                <?php if (isLoggedIn() && currentUser()['role']==='client'): ?>
                <a href="carte.php" class="btn btn-primary" style="margin-top:12px;display:inline-block;background:var(--color-primary);color:#fff;">
                    🛒 Commander maintenant
                </a>
                <?php else: ?>
                <a href="connexion.php" class="btn btn-secondary" style="margin-top:12px;display:inline-block;border-color:var(--color-primary);color:var(--color-primary);">
                    Se connecter pour commander
                </a>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>
<?php include 'includes/footer.php'; ?>
