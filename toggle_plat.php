<?php
// actions/toggle_plat.php
require_once '../includes/config.php';

$user = currentUser();
if (!in_array($user['role'], ['restaurateur', 'admin'])) {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../restaurateur.php');
    exit;
}

$plat_id = (int)($_POST['plat_id'] ?? 0);
$plats = loadJSON(DATA_PLATS);
$updated = false;

foreach ($plats as &$p) {
    if ($p['id'] === $plat_id) {
        // On inverse l'état de disponibilité boolean
        $p['disponible'] = !$p['disponible'];
        $updated = true;
        break;
    }
}
unset($p);

if ($updated) {
    saveJSON(DATA_PLATS, $plats);
    setFlash('success', 'La disponibilité du plat a bien été modifiée.');
} else {
    setFlash('error', 'Plat introuvable.');
}

header('Location: ../restaurateur.php');
exit;
