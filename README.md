## Installation

1. Copier tous les fichiers dans votre répertoire web (ex: `htdocs/cyfat/`)
2. Lancer le serveur :
   ```bash
   php -S localhost:8000
   ```
3. Ouvrir `http://localhost:8000/index.php`

---

## Structure des fichiers

```
cyfat/
├── data/                    ← Données JSON (ne pas modifier manuellement)
│   ├── users.json           ← Utilisateurs
│   ├── plats.json           ← Plats (18 plats)
│   ├── menus.json           ← Menus (4 menus)
│   ├── commandes.json       ← Commandes
│   └── avis.json            ← Avis publics (créé automatiquement)
│
├── includes/                ← Bibliothèques PHP partagées
│   ├── config.php           ← Configuration, fonctions utilitaires, auth
│   ├── header.php           ← Header HTML commun
│   └── footer.php           ← Footer HTML commun
│
├── actions/                 ← Scripts PHP de traitement (POST)
│   ├── login.php            ← Traitement connexion
│   ├── register.php         ← Traitement inscription
│   ├── logout.php           ← Déconnexion
│   ├── commander.php        ← Validation commande + paiement CYBank
│   ├── update_commande.php  ← Changement statut commande
│   ├── update_user.php      ← Actions admin sur utilisateurs
│   └── noter.php            ← Soumettre une note de commande
│
├── index.php                ← Accueil
├── connexion.php            ← Connexion + inscription
├── carte.php                ← Carte des plats (filtrée dynamiquement)
├── panier.php               ← Panier + formulaire commande + paiement
├── roulette.php             ← Fonctionnalité innovante 
├── suivi_commande.php       ← Suivi temps réel d'une commande
├── historique.php           ← Historique commandes client
├── profil.php               ← Profil client + fidélité
├── admin_users.php          ← Page admin gestion utilisateurs
├── restaurateur.php         ← Tableau Kanban restaurateur
├── livreur.php              ← Interface livreur (mobile-first)
├── apropos.php              ← À propos
├── contact.php              ← Contact
├── livraison.php            ← Informations livraison
├── avis.php                 ← Avis clients (stockage JSON)
├── style.css                ← CSS complet (phase 1 + phase 2)
└── README.md                ← Ce fichier
```

---

## Comptes de test

| Rôle          | Email                        | Mot de passe |
|---------------|------------------------------|-------------|
| Admin         | admin.cyfat@gmail.com        | Admin2026   |
| Admin 2       | admin2@cyfat.fr              | password    |
| Client        | client@gmail.com             | password    |
| Restaurateur  | chef@cyfat.fr                | password    |
| Livreur       | livreur@cyfat.fr             | password    |

---

## Format des données JSON

### users.json
```json
{
  "id": 1,
  "nom": "...", "prenom": "...", "email": "...",
  "password": "hash bcrypt",
  "role": "admin|client|restaurateur|livreur",
  "statut": "actif|bloque",
  "niveau": "Standard|Premium|VIP",
  "remise": 0,
  "telephone": "...", "adresse": "...",
  "date_inscription": "YYYY-MM-DD",
  "derniere_connexion": "YYYY-MM-DD",
  "points_fidelite": 0
}
```

### plats.json
```json
{
  "id": 1, "nom": "...", "description": "...", "prix": 9.90,
  "categorie": "entree|burger|plat|bowl|dessert|boisson",
  "allergenes": ["gluten", ...],
  "infos_nutritionnelles": {"calories":0, "proteines":0, "glucides":0, "lipides":0},
  "disponible": true
}
```

### menus.json
```json
{
  "id": 1, "nom": "...", "description": "...", "prix_total": 13.90,
  "plats_ids": [1, 2, ...],
  "creneau": "midi|soir|toute_la_journee",
  "horaires": "...", "disponible": true
}
```

### commandes.json
```json
{
  "id": 1, "client_id": 3,
  "articles": [{"type":"plat|menu","id":1,"nom":"...","quantite":1,"prix_unitaire":9.90}],
  "total": 9.90, "remise_appliquee": 0,
  "mode": "sur_place|a_emporter|livraison",
  "adresse_livraison": "...", "code_interphone": "...",
  "statut": "en_attente|en_preparation|pret|en_livraison|livre|abandonne",
  "livreur_id": null,
  "date_commande": "YYYY-MM-DD HH:MM:SS",
  "paiement_statut": "paye|en_attente",
  "paiement_methode": "CYBank",
  "transaction_id": "CYB-YYYYMMDD-XXX",
  "note_commande": {"note":5,"commentaire":"...","date":"YYYY-MM-DD"}
}
```

---

## Simulation API CYBank
- Cartes **Visa** (commence par 4) ou **Mastercard** (commence par 5), 16 chiffres → Paiement accepté
- Toute autre carte → Refus
- N° de test : `4111 1111 1111 1111` (Visa) ou `5500 0000 0000 0004` (MC)
- CVV + expiration : valeurs quelconques

---


