<?php
// index.php
require_once 'includes/config.php';

$pageTitle  = 'CY-FAT — Accueil';
$activePage = 'index.php';
$menus      = loadJSON(DATA_MENUS);
$commandes  = loadJSON(DATA_COMMANDES);

// Avis réels (commandes notées)
$avis_reels = [];
foreach ($commandes as $cmd) {
    if ($cmd['note_commande']) {
        $avis_reels[] = $cmd['note_commande'];
    }
}

include 'includes/header.php';
?>

<main>
    <!-- HERO -->
    <section class="hero">
        <div class="hero-content">
            <h1>Bienvenue chez CY-FAT</h1>
            <p>Un restaurant chaleureux pour bien manger entre deux cours, sur place, à emporter ou en livraison.</p>
            <div class="hero-buttons">
                <a href="carte.php" class="btn btn-primary">Découvrir la carte</a>
                <?php if (!isLoggedIn()): ?>
                <a href="connexion.php" class="btn btn-secondary">Se connecter</a>
                <?php else: ?>
                <a href="panier.php" class="btn btn-secondary">Mon panier 🛒</a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Menus phares -->
    <section class="section" style="padding-top:50px;">
        <h2>Nos formules du moment</h2>
        <p class="section-intro">Des menus pensés pour les étudiants : rapide, bon et pas cher.</p>
        <div class="menus-grid">
            <?php foreach ($menus as $m): if (!$m['disponible']) continue; ?>
            <article class="menu-card">
                <div class="menu-card-body">
                    <h3><?= h($m['nom']) ?></h3>
                    <p><?= h($m['description']) ?></p>
                    <p style="color:var(--color-muted);font-size:.85rem;">🕐 <?= h($m['horaires']) ?></p>
                    <p class="price" style="font-size:1.2rem;margin-top:8px;"><?= number_format($m['prix_total'],2,',','') ?> €</p>
                    <a href="carte.php" class="btn btn-primary" style="margin-top:10px;display:inline-block;background:var(--color-primary);color:#fff;">
                        Voir la carte →
                    </a>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </section>
</main>

<!-- SECTION AVIS -->
<section class="section section-alt" id="avis">
    <h2>Avis des clients</h2>

    <div class="reviews-top">
        <div class="reviews-score">
            <span class="score" id="avg-score">—</span>
            <span class="stars" id="avg-stars">★★★★★</span>
            <span class="count" id="reviews-count">Chargement…</span>
        </div>
    </div>

    <div class="reviews-grid" id="reviews-grid"></div>
</section>

<?php include 'includes/footer.php'; ?>

<script>
// Avis réels depuis PHP + avis générés
const avisReels = <?= json_encode($avis_reels) ?>;

function stars(n) {
    return "★★★★★☆☆☆☆☆".slice(5-n, 10-n);
}
function randInt(min,max){ return Math.floor(Math.random()*(max-min+1))+min; }
function pick(arr){ return arr[randInt(0,arr.length-1)]; }
function daysAgo(d){ const dt=new Date(); dt.setDate(dt.getDate()-d); return `${String(dt.getDate()).padStart(2,'0')}/${String(dt.getMonth()+1).padStart(2,'0')}/${dt.getFullYear()}`; }

function generateFakeReviews(count) {
    const names  = ["Inès B.","Yanis D.","Sofia K.","Nolan M.","Lina R.","Mehdi S.","Camille T.","Adam L."];
    const titles = ["Très bon et rapide","Parfait entre deux cours","Bon rapport qualité/prix","Je recommande","Super expérience"];
    const texts  = [
        "Portions généreuses et service rapide, idéal quand on est pressé.",
        "Le burger est vraiment bon, et les frites maison font la diff.",
        "J'ai pris un bowl, c'était frais et bien assaisonné.",
        "Livraison arrivée chaude, emballage propre, rien à dire.",
        "Prix corrects pour un resto étudiant, je reviendrai.",
    ];
    return Array.from({length:count}, () => ({
        name: pick(names), rating: pick([5,5,4,4,4,3]),
        title: pick(titles), text: pick(texts), date: daysAgo(randInt(1,60))
    }));
}

function renderReviews() {
    const grid     = document.getElementById('reviews-grid');
    const avgScore = document.getElementById('avg-score');
    const avgStars = document.getElementById('avg-stars');
    const countEl  = document.getElementById('reviews-count');

    const reels = avisReels.map(a => ({
        name: 'Client vérifié ✅', rating: a.note,
        title: 'Avis vérifié', text: a.commentaire || '', date: a.date
    }));
    const fakes   = generateFakeReviews(Math.max(4, 8 - reels.length));
    const reviews = [...reels, ...fakes];

    grid.innerHTML = '';
    let sum = 0;
    reviews.forEach(r => {
        sum += r.rating;
        const card = document.createElement('article');
        card.className = 'review-card';
        card.innerHTML = `
            <div class="review-head">
                <div class="review-name">${r.name}</div>
                <div class="review-rating">${stars(r.rating)}</div>
            </div>
            <div class="review-title">${r.title}</div>
            <div class="review-text">${r.text}</div>
            <div class="review-date">${r.date}</div>`;
        grid.appendChild(card);
    });

    const avg = sum / reviews.length;
    avgScore.textContent = String(Math.round(avg*10)/10).replace('.',',');
    avgStars.textContent = stars(Math.round(avg));
    countEl.textContent  = `Basé sur ${reviews.length} avis`;
}

document.addEventListener('DOMContentLoaded', renderReviews);
</script>
