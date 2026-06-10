<?php
// actions/commander.php — Valider le panier et créer la commande
require_once '../includes/config.php';
requireRole('client', '../connexion.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../panier.php');
    exit;
}

$user = currentUser();

$articles_raw  = $_POST['articles']         ?? '[]';
$mode          = sanitize($_POST['mode']    ?? 'sur_place');
$adresse       = sanitize($_POST['adresse'] ?? $user['adresse'] ?? '');
$interphone    = sanitize($_POST['interphone'] ?? $user['code_interphone'] ?? '');
$date_souhait  = sanitize($_POST['date_souhait'] ?? '');

// Paiement CYBank
$card_num      = sanitize($_POST['card_number'] ?? '');
$card_exp      = sanitize($_POST['card_exp']    ?? '');
$card_cvv      = sanitize($_POST['card_cvv']    ?? '');

// Décoder les articles
$articles = json_decode($articles_raw, true);
if (empty($articles)) {
    setFlash('error', 'Votre panier est vide.');
    header('Location: ../panier.php');
    exit;
}

$total = 0;
$plats = loadJSON(DATA_PLATS);
$menus = loadJSON(DATA_MENUS); // On charge bien les menus depuis le JSON

foreach ($articles as &$art) {
    if ($art['type'] === 'plat') {
        foreach ($plats as $p) {
            if ($p['id'] == $art['id']) {
                $art['prix'] = $p['prix']; // On attache le prix unitaire
                $art['nom']  = $p['nom'];
                $total      += $p['prix'] * $art['quantite'];
            }
        }
    } 
    // === AJOUTEZ OU VÉRIFIEZ CE BLOC POUR LES MENUS ===
    unset($m); // Sécurité PHP
    if ($art['type'] === 'menu') {
        foreach ($menus as $m) {
            if ($m['id'] == $art['id']) {
                $art['prix'] = $m['prix']; // On récupère les 10.00 € du menu
                $art['nom']  = $m['nom'];
                $total      += $m['prix'] * $art['quantite']; // On l'ajoute au total
            }
        }
    }
}
unset($art); // Optionnel mais recommandé après une boucle par référence (&)

// Appliquer remise fidélité
$remise_pct = $user['remise'] ?? 0;
$total_final = round($total * (1 - $remise_pct / 100), 2);

// === Simulation API CYBank ===
$paiement_statut = 'en_attente';
$transaction_id  = null;

if (!empty($card_num)) {
    // Simulation : on accepte si numéro commence par 4 ou 5 (Visa/MC)
    $card_clean = preg_replace('/\s+/', '', $card_num);
    if (strlen($card_clean) === 16 && (str_starts_with($card_clean, '4') || str_starts_with($card_clean, '5'))) {
        $paiement_statut = 'paye';
        $transaction_id  = 'CYB-' . date('Ymd') . '-' . rand(100, 999);
    } else {
        setFlash('error', 'Paiement refusé. Vérifiez les informations de votre carte.');
        header('Location: ../panier.php');
        exit;
    }
}

// Créer la commande
$commandes = loadJSON(DATA_COMMANDES);

$date_prevue = null;
if ($mode === 'livraison') {
    $date_prevue = date('Y-m-d H:i:s', strtotime('+35 minutes'));
} elseif ($mode === 'a_emporter' && $date_souhait) {
    $date_prevue = date('Y-m-d H:i:s', strtotime($date_souhait));
}

$nouvelle_commande = [
    'id'                      => nextId($commandes),
    'client_id'               => $user['id'],
    'articles'                => $articles,
    'total'                   => $total_final,
    'remise_appliquee'        => $remise_pct,
    'mode'                    => $mode,
    'adresse_livraison'       => $mode === 'livraison' ? $adresse : null,
    'code_interphone'         => $mode === 'livraison' ? $interphone : null,
    'statut'                  => $paiement_statut === 'paye' ? 'en_attente' : 'attente_paiement',
    'livreur_id'              => null,
    'date_commande'           => date('Y-m-d H:i:s'),
    'date_livraison_prevue'   => $date_prevue,
    'date_livraison_effective'=> null,
    'paiement_statut'         => $paiement_statut,
    'paiement_methode'        => $paiement_statut === 'paye' ? 'CYBank' : null,
    'transaction_id'          => $transaction_id,
    'note_commande'           => null,
];

$commandes[] = $nouvelle_commande;
saveJSON(DATA_COMMANDES, $commandes);

// Points fidélité +1 par euro dépensé
$users = loadJSON(DATA_USERS);
$users = array_map(function($u) use ($user, $total_final) {
    if ($u['id'] === $user['id']) {
        $u['points_fidelite'] = ($u['points_fidelite'] ?? 0) + (int)$total_final;
    }
    return $u;
}, $users);
saveJSON(DATA_USERS, $users);

$_SESSION['last_order_id'] = $nouvelle_commande['id'];
setFlash('success', 'Commande #' . $nouvelle_commande['id'] . ' passée avec succès ! ' . ($transaction_id ? "Transaction : $transaction_id" : ''));
header('Location: ../suivi_commande.php?id=' . $nouvelle_commande['id']);
exit;
