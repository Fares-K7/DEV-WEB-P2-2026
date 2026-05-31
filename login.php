<?php
// actions/login.php — Traitement connexion
require_once '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../connexion.php');
    exit;
}

$email    = filter_var(trim($_POST['login-email'] ?? ''), FILTER_SANITIZE_EMAIL);
$password = $_POST['login-password'] ?? '';

if (!$email || !$password) {
    setFlash('error', 'Email et mot de passe requis.');
    header('Location: ../connexion.php');
    exit;
}

$users = loadJSON(DATA_USERS);
$found = null;

foreach ($users as $u) {
    if (strtolower($u['email']) === strtolower($email)) {
        $found = $u;
        break;
    }
}

if (!$found) {
    setFlash('error', 'Identifiants incorrects.');
    header('Location: ../connexion.php');
    exit;
}

// Vérifier mot de passe
// Note : les mots de passe de démo sont hachés avec password_hash('password', PASSWORD_DEFAULT)
// Pour les tests : tous les comptes ont le mot de passe "password"
// Le compte admin a aussi Admin2026 (vérifié en clair pour compatibilité phase 1)
$passwordOk = password_verify($password, $found['password']);
// Compatibilité : ancien admin hard-codé
if (!$passwordOk && $found['email'] === 'admin.cyfat@gmail.com' && $password === 'Admin2026') {
    $passwordOk = true;
}

if (!$passwordOk) {
    setFlash('error', 'Identifiants incorrects.');
    header('Location: ../connexion.php');
    exit;
}

if ($found['statut'] === 'bloque') {
    setFlash('error', 'Votre compte a été bloqué. Contactez l\'administrateur.');
    header('Location: ../connexion.php');
    exit;
}

// Mise à jour dernière connexion
$users = array_map(function($u) use ($found) {
    if ($u['id'] === $found['id']) {
        $u['derniere_connexion'] = date('Y-m-d');
    }
    return $u;
}, $users);
saveJSON(DATA_USERS, $users);

// Créer la session
$_SESSION['user_id'] = $found['id'];

setFlash('success', 'Connexion réussie ! Bienvenue ' . $found['prenom'] . ' !');

// Redirection selon rôle
switch ($found['role']) {
    case 'admin':        header('Location: ../admin_users.php'); break;
    case 'restaurateur': header('Location: ../restaurateur.php'); break;
    case 'livreur':      header('Location: ../livreur.php'); break;
    default:             header('Location: ../index.php');
}
exit;
