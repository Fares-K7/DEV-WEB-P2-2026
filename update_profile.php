<?php
require_once '../includes/config.php';
requireRole('client', '../connexion.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../profil.php');
    exit;
}

$user = currentUser();

$prenom    = sanitize($_POST['prenom'] ?? '');
$nom       = sanitize($_POST['nom'] ?? '');
$telephone = sanitize($_POST['telephone'] ?? '');
$adresse   = sanitize($_POST['adresse'] ?? '');
$interphone = sanitize($_POST['code_interphone'] ?? '');
$new_pwd   = $_POST['new_password'] ?? '';

if (!$prenom || !$nom) {
    setFlash('error', 'Le prénom et le nom sont obligatoires.');
    header('Location: ../profil.php');
    exit;
}

$users = loadJSON(DATA_USERS);

foreach ($users as &$u) {
    if ($u['id'] === $user['id']) {
        $u['prenom'] = $prenom;
        $u['nom']    = $nom;
        $u['telephone'] = $telephone;
        $u['adresse']   = $adresse;
        $u['code_interphone'] = $interphone;

        if (!empty($new_pwd)) {
            if (strlen($new_pwd) < 6) {
                setFlash('error', 'Le nouveau mot de passe doit faire au moins 6 caractères.');
                header('Location: ../profil.php');
                exit;
            }
            $u['password'] = password_hash($new_pwd, PASSWORD_DEFAULT);
        }
        break;
    }
}
unset($u);

if (saveJSON(DATA_USERS, $users)) {
    setFlash('success', 'Votre profil a été mis à jour avec succès.');
} else {
    setFlash('error', 'Erreur lors de la sauvegarde des modifications.');
}

header('Location: ../profil.php');
exit;
