<?php
require_once 'includes/config.php';
requireRole('admin', 'connexion.php');

$users     = loadJSON(DATA_USERS);
$commandes = loadJSON(DATA_COMMANDES);

$stats_globales = [
    'Tous les clients' => ['nb_users' => 0, 'nb_commandes' => 0, 'total_depense' => 0]
];

$user_tranche_map = [];

foreach ($users as $u) {
    if ($u['role'] !== 'client') continue; 

    $tranche = 'Non renseigné';
    
    foreach ($users as $u) {
    if ($u['role'] !== 'client') continue;
    
    $stats_globales['Tous les clients']['nb_users']++;
    $user_tranche_map[$u['id']] = 'Tous les clients';
    }
}

foreach ($commandes as $cmd) {
    $clientId = $cmd['client_id'];
    if (in_array($cmd['statut'], ['en_attente', 'en_preparation', 'pret', 'en_livraison', 'livre'])) {
        $tranche = $user_tranche_map[$clientId] ?? 'Non renseigné';
        $stats_ages[$tranche]['nb_commandes']++;
        $stats_ages[$tranche]['total_depense'] += $cmd['total'];
    }
}

$pageTitle  = 'CY-FAT — Admin — Statistiques';
$activePage = '';
include 'includes/header.php';
?>
