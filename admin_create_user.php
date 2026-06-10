<?php
require_once '../includes/config.php';
requireRole('admin', '../index.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../admin_users.php');
    exit;
}

$prenom   = sanitize($_POST['prenom'] ?? '');
$nom      = sanitize($_POST['nom'] ?? '');
$email    = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$password = $_POST['password'] ?? '';
$role     = sanitize($_POST['role'] ?? 'client');

if (!$prenom || !$nom || !$email || !$password) {
    setFlash('error', 'Tous les champs sont requis.');
    header('Location: ../admin_users.php');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    setFlash('error', 'Adresse email invalide.');
    header('Location: ../admin_users.php');
    exit;
}

if (strlen($password) < 6) {
    setFlash('error', 'Le mot de passe doit contenir au moins 6 caractères.');
    header('Location: ../admin_users.php');
    exit;
}

$users = loadJSON(DATA_USERS);

foreach ($users as $u) {
    if (strtolower($u['email']) === strtolower($email)) {
        setFlash('error', 'Cet email est déjà associé à un compte.');
        header('Location: ../admin_users.php');
        exit;
    }
}

$newUser = [
    'id'                 => nextId($users),
    'nom'                => $nom,
    'prenom'             => $prenom,
    'email'              => $email,
    'password'           => password_hash($password, PASSWORD_DEFAULT),
    'role'               => $role,
    'statut'             => 'actif',
    'niveau'             => 'Standard',
    'remise'             => 0,
    'telephone'          => '',
    'adresse'            => '',
    'date_inscription'   => date('Y-m-d'),
    'derniere_connexion' => '',
];

if ($role === 'client') {
    $newUser['points_fidelite'] = 0;
}

$users[] = $newUser;

if (saveJSON(DATA_USERS, $users)) {
    setFlash('success', "Le compte de " . h($prenom) . " (" . h($role) . ") a été créé.");
} else {
    setFlash('error', "Erreur lors de la création du compte.");
}

header('Location: ../admin_users.php');
exit;
