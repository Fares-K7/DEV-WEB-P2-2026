<?php
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

$card_num      = sanitize($_POST['card_number'] ?? '');
$card_exp      = sanitize($_POST['card_exp']    ?? '');
$card_cvv      = sanitize($_POST['card_cvv']    ?? '');

$articles = json_decode($articles_raw, true);
if (empty($articles)) {
    setFlash('error', 'Votre panier est vide.');
    header('Location: ../panier.php');
    exit;
}

$total = 0;
$plats = loadJSON(DATA_PLATS);
$menus = loadJSON(DATA_MENUS); 

foreach ($articles as &$art) {
    if ($art['type'] === 'plat') {
        foreach ($plats as $p) {
            if ($p['id'] == $art['id']) {
                $art['prix'] = $p['prix']; 
                $art['nom']  = $p['nom'];
                $total      += $p['prix'] * $art['quantite'];
            }
        }
    } 
    unset($m); 
    if ($art['type'] === 'menu') {
        foreach ($menus as $m) {
            if ($m['id'] == $art['id']) {
                $art['prix'] = $m['prix']; 
                $art['nom']  = $m['nom'];
                $total      += $m['prix'] * $art['quantite']; 
            }
        }
    }
}
unset($art); 

$remise_pct = $user['remise'] ?? 0;
$total_final = round($total * (1 - $remise_pct / 100), 2);

$paiement_statut = 'en_attente';
$transaction_id  = null;

if (!empty($card_num)) {
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
