<?php
// admin_stats.php
require_once 'includes/config.php';
requireRole('admin', 'connexion.php');

$users     = loadJSON(DATA_USERS);
$commandes = loadJSON(DATA_COMMANDES);

$stats_globales = [
    'Tous les clients' => ['nb_users' => 0, 'nb_commandes' => 0, 'total_depense' => 0]
];

// Mapper pour retrouver facilement la tranche d'un utilisateur lors du calcul des commandes
$user_tranche_map = [];

foreach ($users as $u) {
    if ($u['role'] !== 'client') continue; // On ne compte que les clients

    $tranche = 'Non renseigné';
    
    foreach ($users as $u) {
    if ($u['role'] !== 'client') continue;
    
    // On incrémente juste le compteur global des clients
    $stats_globales['Tous les clients']['nb_users']++;
    $user_tranche_map[$u['id']] = 'Tous les clients';
    }
}

// Associer les commandes et dépenses aux tranches d'âge
foreach ($commandes as $cmd) {
    $clientId = $cmd['client_id'];
    // On ne prend en compte que les commandes payées/livrées (pas abandonnées ou attente paiement)
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
