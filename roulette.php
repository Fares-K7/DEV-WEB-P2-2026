<?php
// roulette.php
require_once 'includes/config.php';

// 1. Chargement des menus depuis le fichier JSON
$menus_roulette = file_exists(DATA_MENUS) ? loadJSON(DATA_MENUS) : [];
$items_roulette = [];

foreach ($menus_roulette as $m) {
    // Si le menu n'est pas disponible, on l'ignore
    if (($m['disponible'] ?? true) === false) {
        continue;
    }

    // 🌟 CORRECTION : On utilise la clé exacte de ton JSON "prix_total"
    $prix_final = isset($m['prix_total']) ? floatval($m['prix_total']) : 0.00;

    $items_roulette[] = [
        'nom'  => $m['nom'] ?? 'Menu Sans Nom',
        'prix' => $prix_final,
        'type' => 'une formule menu'
    ];
}

$pageTitle  = 'CY-FAT — Roulette Anti-Indécision';
$activePage = 'roulette.php';
include 'includes/header.php';
?>

<main>
    <section class="section" style="padding: 60px 20px;">
        <div style="background: var(--color-surface); padding: 40px 30px; border-radius: 15px; box-shadow: var(--shadow-soft); max-width: 550px; margin: 0 auto; text-align: center; border: 3px dashed var(--color-primary);">
            
            <h2 style="color: var(--color-accent); margin-bottom: 10px; font-size: 2rem;">🎰 La Roulette Anti-Indécision</h2>
            <p style="color: var(--color-muted); font-size: 0.95rem; margin-bottom: 30px;">Trouve la formule menu parfaite selon ton budget maximum !</p>
            
            <div style="margin-bottom: 25px;">
                <label style="display:block; font-weight:bold; margin-bottom:10px; color: var(--color-dark);">Mon budget maximum :</label>
                <div style="display: flex; justify-content: center; align-items: center; gap: 10px;">
                    <input type="number" id="budget-input" value="15" min="2" max="50" step="0.5" 
                           style="width: 110px; padding: 12px; border: 2px solid var(--color-secondary); border-radius: 8px; text-align: center; font-size: 1.2rem; font-weight: bold; color: var(--color-dark); background: var(--color-bg);">
                    <span style="font-size: 1.4rem; font-weight: bold; color: var(--color-dark);">€</span>
                </div>
            </div>

            <button type="button" id="btn-roulette" class="btn btn-primary" style="width: 100%; padding: 15px; font-size: 1.1rem; font-weight: bold; cursor: pointer; border: none; border-radius: 8px;">
                Trouver mon menu ! 🚀
            </button>

            <div id="roulette-result" style="margin-top: 30px; min-height: 100px; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                <p style="color: var(--color-muted); font-style: italic;">Le destin attend que tu cliques...</p>
            </div>


            <div style="margin-top: 25px; padding-top: 15px; border-top: 1px solid #eee;">
                <a href="carte.php" style="color: var(--color-primary); text-decoration: none; font-weight: 600; font-size: 0.9rem;">← Voir toute la carte</a>
            </div>
        </div>
    </section>
</main>

<script>
// Transfert du catalogue de menus PHP vers JavaScript
const DATAS_ROULETTE = <?php echo json_encode($items_roulette); ?>;

document.getElementById('btn-roulette').addEventListener('click', function() {
    const budgetMax = parseFloat(document.getElementById('budget-input').value) || 0;
    const resultDiv = document.getElementById('roulette-result');
    const btn = this;
    
    // Filtrage pour ne garder que les menus inférieurs ou égaux au budget
    const optionsValides = DATAS_ROULETTE.filter(item => item.prix <= budgetMax);
    
    // Si aucun menu ne rentre dans le budget
    if (optionsValides.length === 0) {
        resultDiv.innerHTML = `
            <div style="animation: popIn 0.3s ease; background: #fdecea; color: #c03030; padding: 15px; border-radius: 8px; width: 100%; border-left: 5px solid #c03030; font-weight: bold; text-align: center;">
                ❌ Dommage ! Aucun menu n'est disponible pour moins de ${budgetMax.toFixed(2)} €. <br>
                <span style="font-size: 0.85rem; font-weight: normal; display: block; margin-top: 5px;">Essaie d'augmenter légèrement ton budget max.</span>
            </div>
        `;
        return;
    }

    // Effet visuel de défilement (Faux suspense)
    btn.disabled = true;
    let compteur = 0;
    const interval = setInterval(() => {
        const itemFaux = optionsValides[Math.floor(Math.random() * optionsValides.length)];
        resultDiv.innerHTML = `<span style="font-size: 1.3rem; font-weight: bold; color: var(--color-muted); opacity: 1;">${itemFaux.nom}</span>`;
        compteur++;
        
        if (compteur > 20) { 
            clearInterval(interval);
            
            // Sélection finale parmi les menus filtrés
            const gagnant = optionsValides[Math.floor(Math.random() * optionsValides.length)];
            
            // Affichage propre du menu gagnant
            resultDiv.innerHTML = `
                <div style="animation: popIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); background: #fff5eb; padding: 20px; border-radius: 10px; width: 100%; border: 2px solid var(--color-secondary); box-shadow: inset 0 0 10px rgba(0,0,0,0.02);">
                    <span style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; color: var(--color-primary); font-weight: bold; display: block; margin-bottom: 5px;">
                        🎯 Le destin a choisi (${gagnant.type}) :
                    </span>
                    <h4 style="margin: 5px 0 10px 0; font-size: 1.4rem; color: var(--color-dark); font-weight: 800;">${gagnant.nom}</h4>
                    <span style="background: var(--color-primary); color: #fff; padding: 5px 15px; border-radius: 20px; font-size: 1.2rem; font-weight: bold; display: inline-block;">
                        ${gagnant.prix.toFixed(2)} €
                    </span>
                </div>
            `;
            btn.disabled = false;
        }
    }, 70); 
});
</script>

<style>
@keyframes popIn {
    0% { transform: scale(0.8); opacity: 0; }
    100% { transform: scale(1); opacity: 1; }
}
</style>

<?php include 'includes/footer.php'; ?>
