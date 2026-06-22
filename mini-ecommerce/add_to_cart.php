<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/cart.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
    header('Location: cart.php');
    exit;
}

$productId = (int)($_POST['product_id'] ?? 0);
$quantity = max(1, (int)($_POST['quantity'] ?? 1));
$redirectTo = $_POST['redirect_to'] ?? 'cart.php';

// Sécurité : on n'autorise jamais une redirection vers un site externe
if (strpos($redirectTo, '://') !== false || strpos($redirectTo, '//') === 0) {
    $redirectTo = 'cart.php';
}

$stmt = $pdo->prepare('SELECT stock FROM products WHERE id = ?');
$stmt->execute([$productId]);
$product = $stmt->fetch();

if ($product) {
    $alreadyInCart = $_SESSION['cart'][$productId] ?? 0;
    $maxAddable = max(0, (int)$product['stock'] - $alreadyInCart);
    $quantity = min($quantity, $maxAddable);

    if ($quantity > 0) {
        cart_add($productId, $quantity);
    }
}

$separator = (strpos($redirectTo, '?') !== false) ? '&' : '?';
header('Location: ' . $redirectTo . $separator . 'added=1');
exit;
