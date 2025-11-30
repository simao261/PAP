<?php
// Configuração da Base de Dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'lojabuildlab');
define('DB_USER', 'root');
define('DB_PASS', '');

// Configurações do Site
define('SITE_NAME', 'BuildLab');
define('SITE_URL', 'http://localhost/buildlab');

// Configurações de Sessão
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Mude para 1 em produção com HTTPS

// Iniciar sessão
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Conexão à Base de Dados
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Erro na conexão à base de dados: " . $e->getMessage());
}

// Funções Auxiliares
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: index.php');
        exit();
    }
}

function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function formatPrice($price) {
    return number_format($price, 2, ',', ' ') . ' €';
}

function getCartCount() {
    return isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
}
?>
