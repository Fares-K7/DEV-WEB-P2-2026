<?php
// contact.php
require_once 'includes/config.php';
$pageTitle  = 'CY-FAT — Contact';
$activePage = 'contact.php';
include 'includes/header.php';
?>
<main>
    <section id="contact" class="section section-alt">
        <h2>Contact & horaires</h2>
        <div class="contact-grid">
            <div>
                <h3>Nous trouver</h3>
                <p>CY-FAT, Campus CY Cergy-Pontoise<br>95000 Cergy</p>
                <p>Lundi – Vendredi<br>11h30 – 14h30 / 18h30 – 22h00</p>
            </div>
            <div>
                <h3>Nous écrire</h3>
                <form class="contact-form" id="contact-form">
                    <label for="nom">Nom</label>
                    <input type="text" id="nom" name="nom" placeholder="Votre nom"
                           value="<?= isLoggedIn() ? h(currentUser()['prenom'].' '.currentUser()['nom']) : '' ?>">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="votre.email@exemple.com"
                           value="<?= isLoggedIn() ? h(currentUser()['email']) : '' ?>">
                    <label for="message">Message</label>
                    <textarea id="message" name="message" rows="4" placeholder="Votre message"></textarea>
                    <button type="submit" class="btn btn-primary" style="background:var(--color-primary);color:#fff;">Envoyer</button>
                    <p id="contact-msg" style="color:var(--color-primary);display:none;">✅ Message envoyé (démo)</p>
                </form>
            </div>
        </div>
    </section>
</main>
<?php include 'includes/footer.php'; ?>
<script>
document.getElementById('contact-form').addEventListener('submit', function(e) {
    e.preventDefault();
    document.getElementById('contact-msg').style.display = 'block';
    this.reset();
});
</script>
