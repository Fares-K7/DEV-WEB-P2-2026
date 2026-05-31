<?php
// panier.php
require_once 'includes/config.php';
requireRole('client', 'connexion.php');

$pageTitle  = 'CY-FAT — Mon panier';
$activePage = '';
$user       = currentUser();

include 'includes/header.php';
?>

<main>
<section class="section section-alt">
    <h2>🛒 Mon panier</h2>
    <p class="section-intro">Vérifiez votre commande avant de payer.</p>

    <div class="panier-layout">

        <!-- Panier dynamique (JS) -->
        <div class="panier-items" id="panier-items">
            <p class="cart-empty" id="empty-msg">Votre panier est vide. <a href="carte.php">Voir la carte</a></p>
        </div>

        <!-- Formulaire commande + paiement -->
        <form class="panier-form" id="order-form" action="actions/commander.php" method="POST">
            <input type="hidden" name="articles" id="articles-input">

            <h3>Options de commande</h3>

            <label>Mode</label>
            <select name="mode" id="mode-select">
                <option value="sur_place">Sur place</option>
                <option value="a_emporter">À emporter</option>
                <option value="livraison">Livraison</option>
            </select>

            <div id="livraison-fields" style="display:none;">
                <label for="adresse">Adresse de livraison</label>
                <input type="text" id="adresse" name="adresse" value="<?= h($user['adresse'] ?? '') ?>" placeholder="Votre adresse">

                <label for="interphone">Code interphone</label>
                <input type="text" id="interphone" name="interphone" value="<?= h($user['code_interphone'] ?? '') ?>" placeholder="Ex : 1234">
            </div>

            <div id="emporter-fields" style="display:none;">
                <label for="date_souhait">Date/heure souhaitée</label>
                <input type="datetime-local" id="date_souhait" name="date_souhait">
            </div>

            <hr style="margin:16px 0;border:none;border-top:1px solid rgba(0,0,0,.08);">

            <h3>Paiement — CYBank</h3>
            <?php if ($user['remise'] > 0): ?>
            <p style="color:var(--color-primary);font-size:.9rem;">🎁 Remise <?= $user['remise'] ?>% (<?= $user['niveau'] ?>) appliquée.</p>
            <?php endif; ?>

            <label for="card_number">Numéro de carte *</label>
            <input type="text" id="card_number" name="card_number" placeholder="4XXX XXXX XXXX XXXX"
                   maxlength="19" required pattern="\d{4}[\s]?\d{4}[\s]?\d{4}[\s]?\d{4}">

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                <div>
                    <label for="card_exp">Expiration *</label>
                    <input type="text" id="card_exp" name="card_exp" placeholder="MM/AA" maxlength="5" required>
                </div>
                <div>
                    <label for="card_cvv">CVV *</label>
                    <input type="text" id="card_cvv" name="card_cvv" placeholder="123" maxlength="3" required>
                </div>
            </div>

            <p class="login-note">Simulation CYBank : carte Visa (commence par 4) ou Mastercard (commence par 5), 16 chiffres.</p>

            <div class="panier-total" id="panier-total">Total : 0,00 €</div>

            <button type="submit" class="btn btn-primary" id="order-btn"
                    style="background:var(--color-primary);color:#fff;width:100%;font-size:1rem;padding:14px;">
                ✅ Valider et payer
            </button>
        </form>
    </div>
</section>
</main>

<?php include 'includes/footer.php'; ?>

<script>
const remise = <?= $user['remise'] ?? 0 ?>;

function renderPanier() {
    const cart = JSON.parse(localStorage.getItem('cyfatCart') || '[]');
    const container = document.getElementById('panier-items');
    const emptyMsg  = document.getElementById('empty-msg');
    const totalEl   = document.getElementById('panier-total');

    if (cart.length === 0) {
        container.innerHTML = '<p class="cart-empty">Votre panier est vide. <a href="carte.php">Voir la carte</a></p>';
        totalEl.textContent  = 'Total : 0,00 €';
        return;
    }

    let html  = '';
    let total = 0;

    cart.forEach((item, idx) => {
        const sous_total = item.prix * item.quantite;
        total += sous_total;
        html += `
        <div class="panier-item">
            <div class="panier-item-info">
                <strong>${item.nom}</strong>
                <span style="font-size:.85rem;color:var(--color-muted);">${item.prix.toFixed(2).replace('.',',')} € / unité</span>
            </div>
            <div class="panier-item-qty">
                <button type="button" class="qty-btn" onclick="changeQty(${idx}, -1)">−</button>
                <span>${item.quantite}</span>
                <button type="button" class="qty-btn" onclick="changeQty(${idx}, 1)">+</button>
            </div>
            <div class="panier-item-price">${sous_total.toFixed(2).replace('.',',')} €</div>
            <button type="button" class="remove-btn" onclick="removeItem(${idx})">🗑</button>
        </div>`;
    });

    container.innerHTML = html;

    const total_final = total * (1 - remise / 100);
    let totalText = `Total : ${total_final.toFixed(2).replace('.',',')} €`;
    if (remise > 0) totalText += ` <small style="color:var(--color-muted);">(avant remise : ${total.toFixed(2).replace('.',',')} €)</small>`;
    totalEl.innerHTML = totalText;

    // Mettre à jour l'input caché
    document.getElementById('articles-input').value = JSON.stringify(
        cart.map(i => ({ id: i.id, type: i.type, quantite: i.quantite }))
    );

    updateCartCount();
}

function changeQty(idx, delta) {
    let cart = JSON.parse(localStorage.getItem('cyfatCart') || '[]');
    cart[idx].quantite = Math.max(1, cart[idx].quantite + delta);
    localStorage.setItem('cyfatCart', JSON.stringify(cart));
    renderPanier();
}

function removeItem(idx) {
    let cart = JSON.parse(localStorage.getItem('cyfatCart') || '[]');
    cart.splice(idx, 1);
    localStorage.setItem('cyfatCart', JSON.stringify(cart));
    renderPanier();
}

// Afficher/masquer champs selon mode
document.getElementById('mode-select').addEventListener('change', function() {
    document.getElementById('livraison-fields').style.display = this.value === 'livraison' ? 'block' : 'none';
    document.getElementById('emporter-fields').style.display  = this.value === 'a_emporter' ? 'block' : 'none';
});

// Formatage numéro carte
document.getElementById('card_number').addEventListener('input', function() {
    this.value = this.value.replace(/\D/g,'').replace(/(.{4})/g,'$1 ').trim().slice(0,19);
});

// Vider panier après commande réussie
document.getElementById('order-form').addEventListener('submit', function() {
    setTimeout(() => localStorage.removeItem('cyfatCart'), 500);
});

renderPanier();
</script>
