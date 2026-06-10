<?php
require_once 'includes/config.php';
$pageTitle  = 'CY-FAT — Avis';
$activePage = 'avis.php';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom  = sanitize($_POST['r-name'] ?? '');
    $note = (int)($_POST['r-rating'] ?? 5);
    $text = sanitize($_POST['r-text'] ?? '');
    if ($text && $note >= 1 && $note <= 5) {
        $avisFile = DATA_DIR . 'avis.json';
        $avis = file_exists($avisFile) ? loadJSON($avisFile) : [];
        $avis[] = [
            'name'   => $nom ?: 'Client',
            'rating' => $note,
            'text'   => $text,
            'date'   => date('d/m/Y'),
        ];
        saveJSON($avisFile, $avis);
        $msg = 'Merci ! Votre avis a été publié.';
    }
}

$avisFile = DATA_DIR . 'avis.json';
$avis_publics = file_exists($avisFile) ? loadJSON($avisFile) : [];
$avis_publics = array_reverse($avis_publics);

include 'includes/header.php';
?>
<main>
    <section class="section section-alt">
        <h2>Avis des clients</h2>
        <p class="section-intro">Donnez votre avis sur votre expérience chez CY-FAT.</p>

        <div class="rating-layout">
            <form class="rating-card" method="POST" action="avis.php">
                <label for="r-name">Nom (optionnel)</label>
                <input id="r-name" name="r-name" type="text" placeholder="Ex : Lina B.">

                <label>Note</label>
                <div class="stars-input" id="stars-input">
                    <?php for ($i=1;$i<=5;$i++): ?>
                    <button type="button" class="star-btn" data-value="<?= $i ?>">★</button>
                    <?php endfor; ?>
                </div>
                <input type="hidden" id="r-rating" name="r-rating" value="5">

                <label for="r-text">Commentaire *</label>
                <textarea id="r-text" name="r-text" rows="4" placeholder="Votre avis..." required></textarea>

                <button type="submit" class="btn btn-primary" style="background:var(--color-primary);color:#fff;">Publier</button>

                <?php if ($msg): ?>
                <p style="color:var(--color-primary);font-weight:600;"><?= h($msg) ?></p>
                <?php endif; ?>
            </form>

            <div class="rating-card">
                <h3>Avis publiés (<?= count($avis_publics) ?>)</h3>
                <div class="reviews-grid" style="grid-template-columns:1fr;">
                    <?php if (empty($avis_publics)): ?>
                    <p style="color:var(--color-muted);">Aucun avis pour le moment.</p>
                    <?php else: ?>
                    <?php foreach ($avis_publics as $a): ?>
                    <article class="review-card">
                        <div class="review-head">
                            <div class="review-name"><?= h($a['name']) ?></div>
                            <div class="review-rating"><?= str_repeat('★',$a['rating']).str_repeat('☆',5-$a['rating']) ?></div>
                        </div>
                        <div class="review-text"><?= h($a['text']) ?></div>
                        <div class="review-date"><?= h($a['date']) ?></div>
                    </article>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</main>
<?php include 'includes/footer.php'; ?>
<script>
const starBtns = document.querySelectorAll('.star-btn');
const ratingInput = document.getElementById('r-rating');
function setRating(n) {
    ratingInput.value = n;
    starBtns.forEach(b => b.classList.toggle('star-btn-active', +b.dataset.value <= n));
}
starBtns.forEach(b => b.addEventListener('click', () => setRating(+b.dataset.value)));
setRating(5);
</script>
