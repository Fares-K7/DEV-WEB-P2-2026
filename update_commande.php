<?php
require_once '../includes/config.php';
requireLogin('../connexion.php');

$user = currentUser();
if (!in_array($user['role'], ['admin', 'restaurateur', 'livreur'])) {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit;
}

$commande_id  = (int)($_POST['commande_id']  ?? 0);
$nouveau_statut = sanitize($_POST['statut']  ?? '');
$livreur_id   = isset($_POST['livreur_id']) ? (int)$_POST['livreur_id'] : null;

$statuts_valides = ['en_attente', 'en_preparation', 'pret', 'en_livraison', 'livre', 'abandonne', 'attente_paiement'];

if (!$commande_id || !in_array($nouveau_statut, $statuts_valides)) {
    setFlash('error', 'Paramètres invalides.');
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '../index.php'));
    exit;
}

$commandes = loadJSON(DATA_COMMANDES);
$updated = false;

foreach ($commandes as &$cmd) {
    if ($cmd['id'] === $commande_id) {
        if ($user['role'] === 'livreur' && $cmd['livreur_id'] !== $user['id']) {
            setFlash('error', 'Cette commande ne vous est pas attribuée.');
            header('Location: ../livreur.php');
            exit;
        }

        $cmd['statut'] = $nouveau_statut;

        if ($livreur_id && $user['role'] !== 'livreur') {
            $cmd['livreur_id'] = $livreur_id;
        }

        if ($nouveau_statut === 'livre') {
            $cmd['date_livraison_effective'] = date('Y-m-d H:i:s');
        }

        $updated = true;
        break;
    }
}
unset($cmd);

if ($updated) {
    saveJSON(DATA_COMMANDES, $commandes);
    setFlash('success', 'Statut mis à jour.');
} else {
    setFlash('error', 'Commande introuvable.');
}

if ($user['role'] === 'livreur') {
    header('Location: ../livreur.php');
} elseif ($user['role'] === 'restaurateur') {
    header('Location: ../restaurateur.php');
} else {
    header('Location: ../admin_users.php');
}
exit;
