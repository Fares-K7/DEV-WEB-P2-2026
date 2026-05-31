<?php
// actions/register.php — Traitement inscription
require_once '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../connexion.php');
    exit;
}

$nom      = sanitize($_POST['signup-nom']     ?? '');
$prenom   = sanitize($_POST['signup-prenom']  ?? '');
$email    = filter_var(trim($_POST['signup-email'] ?? ''), FILTER_SANITIZE_EMAIL);
$tel      = sanitize($_POST['signup-tel']     ?? '');
$adresse  = sanitize($_POST['signup-adresse'] ?? '');
$password = $_POST['signup-password']         ?? '';
$confirm  = $_POST['signup-confirm']          ?? '';
$date_naissance = sanitize($_POST['signup-naissance'] ?? '');

// Validations
if (!$nom || !$prenom || !$email || !$password) {
    setFlash('error', 'Tous les champs obligatoires doivent être remplis.');
    header('Location: ../connexion.php');
    exit;
}

if (!$nom || !$prenom || !$email || !$password || !$date_naissance) {
    setFlash('error', 'Tous les champs obligatoires doivent être remplis.');
    header('Location: ../connexion.php');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    setFlash('error', 'Adresse email invalide.');
    header('Location: ../connexion.php');
    exit;
}

if (strlen($password) < 6) {
    setFlash('error', 'Le mot de passe doit contenir au moins 6 caractères.');
    header('Location: ../connexion.php');
    exit;
}

if ($password !== $confirm) {
    setFlash('error', 'Les mots de passe ne correspondent pas.');
    header('Location: ../connexion.php');
    exit;
}

$users = loadJSON(DATA_USERS);

// Email déjà utilisé ?
foreach ($users as $u) {
    if (strtolower($u['email']) === strtolower($email)) {
        setFlash('error', 'Cette adresse email est déjà utilisée.');
        header('Location: ../connexion.php');
        exit;
    }
}

// Créer le nouvel utilisateur
$newUser = [
    'id'                => nextId($users),
    'nom'               => $nom,
    'prenom'            => $prenom,
    'email'             => $email,
    'password'          => password_hash($password, PASSWORD_DEFAULT),
    'role'              => 'client',
    'statut'            => 'actif',
    'niveau'            => 'Standard',
    'remise'            => 0,
    'telephone'         => $tel,
    'adresse'           => $adresse,
    'date_naissance'    => $date_naissance, // <-- AJOUT ICI
    'date_inscription'  => date('Y-m-d'),
    'derniere_connexion'=> date('Y-m-d'),
    'points_fidelite'   => 0,
];

$users[] = $newUser;
saveJSON(DATA_USERS, $users);

// Connexion automatique
$_SESSION['user_id'] = $newUser['id'];

setFlash('success', 'Compte créé avec succès ! Bienvenue ' . $prenom . ' !');
header('Location: ../index.php');
exit;
