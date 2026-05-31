<?php
// actions/save_plat.php
require_once '../includes/config.php';

// Sécurité
$user = currentUser();
if (!$user || !in_array($user['role'], ['restaurateur', 'admin'])) {
    header('Location: ../index.php');
    exit;
}

$action = $_POST['action'] ?? '';
$id = (int)($_POST['id'] ?? 0);
$plats = loadJSON(DATA_PLATS);

if ($action === 'add') {
    $newPlat = [
        'id'          => nextId($plats),
        'nom'         => sanitize($_POST['nom']),
        'description' => sanitize($_POST['description']),
        'prix'        => (float)$_POST['prix'],
        'categorie'   => sanitize($_POST['categorie']),
        'allergenes'  => [], // Optionnel : à améliorer plus tard
        'disponible'  => true,
        'image'       => !empty($_POST['image']) ? sanitize($_POST['image']) : 'default.jpg'
    ];
    $plats[] = $newPlat;
    setFlash('success', 'Nouveau plat ajouté !');

} elseif ($action === 'toggle_dispo') {
    foreach ($plats as &$p) {
        if ($p['id'] === $id) {
            $p['disponible'] = !$p['disponible'];
            break;
        }
    }
    setFlash('success', 'Disponibilité mise à jour.');

} elseif ($action === 'delete') {
    $plats = array_filter($plats, fn($p) => $p['id'] !== $id);
    // Ré-indexer le tableau après filtrage
    $plats = array_values($plats);
    setFlash('success', 'Plat supprimé de la carte.');
}

saveJSON(DATA_PLATS, $plats);
header('Location: ../admin_carte.php');
exit;
