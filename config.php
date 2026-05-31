<?php
// ========================================================
// includes/config.php — Configuration & fonctions globales
// ========================================================

// Démarrage de session sécurisé
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Chemins vers les fichiers de données
define('DATA_DIR', __DIR__ . '/../data/');
define('DATA_USERS',     DATA_DIR . 'users.json');
define('DATA_PLATS',     DATA_DIR . 'plats.json');
define('DATA_MENUS',     DATA_DIR . 'menus.json');
define('DATA_COMMANDES', DATA_DIR . 'commandes.json');

// ---- Helpers JSON ----

function loadJSON(string $path): array {
    if (!file_exists($path)) return [];
    $content = file_get_contents($path);
    return json_decode($content, true) ?? [];
}

function saveJSON(string $path, array $data): bool {
    return file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false;
}

// ---- Auth ----

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function currentUser(): ?array {
    if (!isLoggedIn()) return null;
    $users = loadJSON(DATA_USERS);
    foreach ($users as $u) {
        if ($u['id'] === $_SESSION['user_id']) return $u;
    }
    return null;
}

function requireLogin(string $redirect = 'connexion.php'): void {
    if (!isLoggedIn()) {
        header("Location: $redirect");
        exit;
    }
}

function requireRole(string $role, string $redirect = 'index.php'): void {
    requireLogin();
    $user = currentUser();
    if (!$user || $user['role'] !== $role) {
        header("Location: $redirect");
        exit;
    }
}

function hasRole(string $role): bool {
    $user = currentUser();
    return $user && $user['role'] === $role;
}

// ---- Sécurité ----

function h(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function sanitize(string $str): string {
    return trim(strip_tags($str));
}

// ---- Génération ID ----

function nextId(array $items): int {
    if (empty($items)) return 1;
    return max(array_column($items, 'id')) + 1;
}

// ---- Flash messages ----

function setFlash(string $type, string $msg): void {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function renderFlash(): string {
    $flash = getFlash();
    if (!$flash) return '';
    $cls = $flash['type'] === 'success' ? 'flash-success' : 'flash-error';
    return '<div class="' . $cls . '">' . h($flash['msg']) . '</div>';
}
