<?php
// connexion.php
require_once 'includes/config.php';

// Déjà connecté → rediriger
if (isLoggedIn()) {
    $user = currentUser();
    switch ($user['role']) {
        case 'admin':        header('Location: admin_users.php'); exit;
        case 'restaurateur': header('Location: restaurateur.php'); exit;
        case 'livreur':      header('Location: livreur.php'); exit;
        default:             header('Location: index.php'); exit;
    }
}

$pageTitle  = 'CY-FAT — Connexion';
$activePage = 'connexion.php';
include 'includes/header.php';
?>

<main>
    <section class="section section-connexion">
        <h2>Espace de connexion</h2>
        <p class="section-intro">Connectez-vous ou créez un compte pour accéder à toutes les fonctionnalités.</p>

        <div class="connexion-layout">

            <!-- Formulaires -->
            <div class="connexion-form-card">
                <div class="login-tabs">
                    <button class="tab-btn tab-btn-active" id="tab-login">Connexion</button>
                    <button class="tab-btn" id="tab-signup">Inscription</button>
                </div>

                <!-- Connexion -->
                <form class="login-form" id="login-form" action="actions/login.php" method="POST">
                    <h3>Connexion</h3>
                    <label for="login-email">Adresse email *</label>
                    <input type="email" id="login-email" name="login-email" placeholder="email@exemple.com" required>

                    <label for="login-password">Mot de passe *</label>
                    <input type="password" id="login-password" name="login-password" placeholder="Votre mot de passe" required>

                    <button type="submit" class="btn btn-primary" style="color:#fff;background:var(--color-primary);">Se connecter</button>

                    <p class="login-note">
                        <strong>Comptes démo :</strong><br>
                        Admin : admin.cyfat@gmail.com / Admin2026<br>
                        Admin2 : admin2@cyfat.fr / password<br>
                        Client : client@gmail.com / password<br>
                        Restaurateur : chef@cyfat.fr / password<br>
                        Livreur : livreur@cyfat.fr / password
                    </p>
                </form>

                <!-- Inscription -->
                <form class="signup-form" id="signup-form" action="actions/register.php" method="POST" style="display:none;">
                    <h3>Créer un compte</h3>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                        <div>
                            <label for="signup-prenom">Prénom *</label>
                            <input type="text" id="signup-prenom" name="signup-prenom" placeholder="Prénom" required>
                        </div>
                        <div>
                            <label for="signup-nom">Nom *</label>
                            <input type="text" id="signup-nom" name="signup-nom" placeholder="Nom" required>
                        </div>
                    </div>

                    <label for="signup-email">Email *</label>
                    <input type="email" id="signup-email" name="signup-email" placeholder="email@exemple.com" required>

                    <label for="signup-tel">Téléphone</label>
                    <input type="tel" id="signup-tel" name="signup-tel" placeholder="06 XX XX XX XX">

                    <label for="signup-adresse">Adresse de livraison</label>
                    <input type="text" id="signup-adresse" name="signup-adresse" placeholder="Rue, ville">
                    
                    <label for=\"signup-naissance\">Date de naissance *</label>
                    <input type="date" id="signup-naissance" name="signup-naissance" required>
                    
                    <label for="signup-password">Mot de passe * (min. 6 car.)</label>
                    <input type="password" id="signup-password" name="signup-password" placeholder="Choisissez un mot de passe" required>

                    <label for="signup-confirm">Confirmer le mot de passe *</label>
                    <input type="password" id="signup-confirm" name="signup-confirm" placeholder="Répétez le mot de passe" required>

                    <button type="submit" class="btn btn-secondary" style="border-color:var(--color-primary);color:var(--color-primary);">Créer mon compte</button>
                </form>
            </div>

            <!-- Rôles -->
            <div class="connexion-roles-card">
                <h3>Rôles et droits</h3>

                <div class="role-block role-admin">
                    <h4>👑 Administrateur</h4>
                    <ul>
                        <li>Gérer les utilisateurs (bloquer, modifier statut, remise)</li>
                        <li>Accéder à toutes les pages du site</li>
                        <li>Consulter toutes les commandes</li>
                    </ul>
                </div>

                <div class="role-block role-client">
                    <h4>🙋 Client</h4>
                    <ul>
                        <li>Commander en ligne (livraison, sur place, à emporter)</li>
                        <li>Suivre ses commandes en temps réel</li>
                        <li>Accéder à l'historique et noter ses commandes</li>
                        <li>Bénéficier de remises et points fidélité</li>
                    </ul>
                </div>

                <div class="role-block" style="background:rgba(100,170,100,0.08);border-left:3px solid #4a9060;padding:14px;border-radius:10px;margin-top:10px;">
                    <h4>🍳 Restaurateur</h4>
                    <ul>
                        <li>Voir les commandes à préparer</li>
                        <li>Changer le statut des commandes</li>
                        <li>Attribuer les livraisons aux livreurs</li>
                    </ul>
                </div>

                <div class="role-block" style="background:rgba(100,100,200,0.08);border-left:3px solid #4a5090;padding:14px;border-radius:10px;margin-top:10px;">
                    <h4>🛵 Livreur</h4>
                    <ul>
                        <li>Voir les livraisons assignées</li>
                        <li>Accéder aux détails (adresse, interphone…)</li>
                        <li>Indiquer la livraison terminée</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>

<script>
    const loginTab  = document.getElementById('tab-login');
    const signupTab = document.getElementById('tab-signup');
    const loginForm  = document.getElementById('login-form');
    const signupForm = document.getElementById('signup-form');

    loginTab.addEventListener('click', () => {
        loginForm.style.display  = 'flex';
        signupForm.style.display = 'none';
        loginTab.classList.add('tab-btn-active');
        signupTab.classList.remove('tab-btn-active');
    });

    signupTab.addEventListener('click', () => {
        loginForm.style.display  = 'none';
        signupForm.style.display = 'flex';
        signupTab.classList.add('tab-btn-active');
        loginTab.classList.remove('tab-btn-active');
    });
</script>
