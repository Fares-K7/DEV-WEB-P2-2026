<?php
// admin_stats.php
require_once 'includes/config.php';
requireRole('admin', 'connexion.php');

$users     = loadJSON(DATA_USERS);
$commandes = loadJSON(DATA_COMMANDES);

// Initialisation des compteurs par tranche d'âge
$stats_ages = [
    'Moins de 18 ans' => ['nb_users' => 0, 'nb_commandes' => 0, 'total_depense' => 0],
    '18 - 25 ans'     => ['nb_users' => 0, 'nb_commandes' => 0, 'total_depense' => 0],
    '26 - 35 ans'     => ['nb_users' => 0, 'nb_commandes' => 0, 'total_depense' => 0],
    'Plus de 35 ans'  => ['nb_users' => 0, 'nb_commandes' => 0, 'total_depense' => 0],
    'Non renseigné'   => ['nb_users' => 0, 'nb_commandes' => 0, 'total_depense' => 0],
];

// Mapper pour retrouver facilement la tranche d'un utilisateur lors du calcul des commandes
$user_tranche_map = [];

foreach ($users as $u) {
    if ($u['role'] !== 'client') continue; // On ne compte que les clients

    $tranche = 'Non renseigné';
    
    if (!empty($u['date_naissance'])) {
        // Calcul de l'âge
        $birthDate = new DateTime($u['date_naissance']);
        $today     = new DateTime('today');
        $age       = $birthDate->diff($today)->y;

        if ($age < 18) {
            $tranche = 'Moins de 18 ans';
        } elseif ($age <= 25) {
            $tranche = '18 - 25 ans';
        } elseif ($age <= 35) {
            $tranche = '26 - 35 ans';
        } else {
            $tranche = 'Plus de 35 ans';
        }
    }

    $stats_ages[$tranche]['nb_users']++;
    $user_tranche_map[$u['id']] = $tranche;
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

<main>
<section class="section section-alt">
    <h2>📊 Statistiques Marketing (Tranches d'âge)</h2>
    <p class="section-intro">Analyse des comportements d'achat et segmentation de la clientèle.</p>

    <div style="overflow-x:auto; margin-top:20px;">
        <table class="admin-table" style="width:100%; border-collapse: collapse; background:#fff; border-radius:8px; overflow:hidden; box-shadow: var(--shadow-soft);">
            <thead>
                <tr style="background:var(--color-accent); color:#fff; text-align:left;">
                    <th style="padding:12px;">Tranche d'âge</th>
                    <th style="padding:12px;">Nombre de clients</th>
                    <th style="padding:12px;">Commandes passées</th>
                    <th style="padding:12px;">Total dépensé</th>
                    <th style="padding:12px;">Panier moyen</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($stats_ages as $tranche => $data): 
                    $panier_moyen = $data['nb_commandes'] > 0 ? $data['total_depense'] / $data['nb_commandes'] : 0;
                ?>
                <tr style="border-bottom:1px solid rgba(0,0,0,.05);">
                    <td style="padding:12px;"><strong><?= $tranche ?></strong></td>
                    <td style="padding:12px;"><?= $data['nb_users'] ?></td>
                    <td style="padding:12px;"><?= $data['nb_commandes'] ?></td>
                    <td style="padding:12px; font-weight:600; color:var(--color-primary);"><?= number_format($data['total_depense'], 2, ',', '') ?> €</td>
                    <td style="padding:12px;"><?= number_format($panier_moyen, 2, ',', '') ?> €</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div style="margin-top:20px; text-align:center;">
        <a href="admin_users.php" class="btn btn-secondary">← Retour Gestion Utilisateurs</a>
    </div>
</section>
</main>

<?php include 'includes/footer.php'; ?>
