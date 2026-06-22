<?php
// Panier basé sur la session PHP : $_SESSION['cart'] = [product_id => quantite].

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

function cart_add(int $productId, int $qty = 1): void
{
    $qty = max(1, $qty);
    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId] += $qty;
    } else {
        $_SESSION['cart'][$productId] = $qty;
    }
}

function cart_set_quantity(int $productId, int $qty): void
{
    if ($qty <= 0) {
        unset($_SESSION['cart'][$productId]);
    } else {
        $_SESSION['cart'][$productId] = $qty;
    }
}

function cart_remove(int $productId): void
{
    unset($_SESSION['cart'][$productId]);
}

function cart_clear(): void
{
    $_SESSION['cart'] = [];
}

function cart_count(): int
{
    return array_sum($_SESSION['cart'] ?? []);
}

/**
 * Retourne les articles du panier enrichis avec les infos produit actuelles.
 * Les quantités sont automatiquement plafonnées au stock disponible, et les
 * produits supprimés depuis sont silencieusement retirés du panier.
 */
function cart_items(PDO $pdo): array
{
    if (empty($_SESSION['cart'])) {
        return [];
    }

    $ids = array_map('intval', array_keys($_SESSION['cart']));
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->execute($ids);

    $productsById = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $p) {
        $productsById[(int)$p['id']] = $p;
    }

    $items = [];
    foreach ($_SESSION['cart'] as $productId => $qty) {
        $productId = (int)$productId;

        if (!isset($productsById[$productId])) {
            unset($_SESSION['cart'][$productId]);
            continue;
        }

        $product = $productsById[$productId];
        $available = max(0, (int)$product['stock']);
        $qty = min((int)$qty, $available);

        if ($qty <= 0) {
            unset($_SESSION['cart'][$productId]);
            continue;
        }

        $_SESSION['cart'][$productId] = $qty;
        $items[] = [
            'product' => $product,
            'quantity' => $qty,
            'subtotal' => (float)$product['price'] * $qty,
        ];
    }
    return $items;
}

function cart_total(PDO $pdo): float
{
    $total = 0.0;
    foreach (cart_items($pdo) as $item) {
        $total += $item['subtotal'];
    }
    return $total;
}
