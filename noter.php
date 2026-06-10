<?php
require_once '../includes/config.php';
requireRole('client', '../connexion.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../historique.php');
    exit;
}

$user        = currentUser();
$commande_id = (int)($_POST['commande_id'] ?? 0);
$note        = (int)($_POST['note']        ?? 0);
$commentaire = sanitize($_POST['commentaire'] ?? '');

if ($note < 1 || $note > 5) {
    setFlash('error', 'Note invalide (1 à 5).');
    header('Location: ../historique.php');
    exit;
}

$commandes = loadJSON(DATA_COMMANDES);
$updated   = false;

foreach ($commandes as &$cmd) {
    if ($cmd['id'] === $commande_id && $cmd['client_id'] === $user['id'] && $cmd['statut'] === 'livre') {
        $cmd['note_commande'] = [
            'note'        => $note,
            'commentaire' => $commentaire,
            'date'        => date('Y-m-d'),
        ];
        $updated = true;
        break;
    }
}
unset($cmd);

if ($updated) {
    saveJSON(DATA_COMMANDES, $commandes);
    setFlash('success', 'Merci pour votre avis !');
} else {
    setFlash('error', 'Impossible de noter cette commande.');
}

header('Location: ../historique.php');
exit;
