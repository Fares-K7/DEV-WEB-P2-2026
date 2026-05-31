<?php
// actions/update_user.php — Modifier un utilisateur (admin)
require_once '../includes/config.php';
requireRole('admin', '../index.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../admin_users.php');
    exit;
}

$target_id = (int)($_POST['user_id']  ?? 0);
$action    = sanitize($_POST['action'] ?? '');
$value     = sanitize($_POST['value']  ?? '');

if (!$target_id) {
    setFlash('error', 'Utilisateur introuvable.');
    header('Location: ../admin_users.php');
    exit;
}

$users = loadJSON(DATA_USERS);
$updated = false;

foreach ($users as &$u) {
    if ($u['id'] === $target_id) {
        switch ($action) {
            case 'bloquer':
                $u['statut'] = 'bloque';
                $updated = true;
                break;
            case 'activer':
                $u['statut'] = 'actif';
                $updated = true;
                break;
            case 'set_niveau':
                if (in_array($value, ['Standard', 'Premium', 'VIP'])) {
                    $u['niveau'] = $value;
                    $updated = true;
                }
                break;
            case 'set_remise':
                $remise = (int)$value;
                if ($remise >= 0 && $remise <= 50) {
                    $u['remise'] = $remise;
                    $updated = true;
                }
                break;
            case 'set_role':
                if (in_array($value, ['client', 'admin', 'restaurateur', 'livreur'])) {
                    $u['role'] = $value;
                    $updated = true;
                }
                break;
        }
        break;
    }
}
unset($u);

if ($updated) {
    saveJSON(DATA_USERS, $users);
    setFlash('success', 'Utilisateur mis à jour.');
} else {
    setFlash('error', 'Action invalide ou utilisateur introuvable.');
}

header('Location: ../admin_users.php');
exit;
