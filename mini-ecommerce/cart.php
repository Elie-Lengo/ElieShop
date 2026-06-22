<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/cart.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify()) {
    $action = $_POST['action'] ?? '';
    $productId = (int)($_POST['product_id'] ?? 0);

    if ($action === 'update') {
        cart_set_quantity($productId, max(0, (int)($_POST['quantity'] ?? 0)));
    } elseif ($action === 'remove') {
        cart_remove($productId);
    }
    header('Location: cart.php');
    exit;
}

$items = cart_items($pdo);
$total = 0.0;
foreach ($items as $it) {
    $total += $it['subtotal'];
}
$added = isset($_GET['added']);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>Mon panier | ElieShop</title>
    <link rel="icon" href="assets/favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

    <?php require __DIR__ . '/includes/site_header.php'; ?>

    <main class="container">
        <h1 class="page-title">Mon panier</h1>

        <?php if ($added): ?>
            <div class="alert alert-success">Produit ajouté au panier ✅</div>
        <?php endif; ?>

        <?php if (!$items): ?>
            <p style="color:#999;">Ton panier est vide pour le moment.
                <a href="index.php">Continuer mes achats →</a>
            </p>
        <?php else: ?>
            <div class="table-responsive" style="margin-bottom:1.5rem;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Produit</th>
                            <th>Prix</th>
                            <th>Quantité</th>
                            <th>Sous-total</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $it): $p = $it['product']; ?>
                            <tr>
                                <td>
                                    <div style="display:flex; align-items:center; gap:0.6rem;">
                                        <?php if (!empty($p['image'])): ?>
                                            <img class="thumb" src="assets/uploads/<?= htmlspecialchars($p['image']) ?>" alt="">
                                        <?php else: ?>
                                            <div class="thumb-placeholder">No img</div>
                                        <?php endif; ?>
                                        <a href="product.php?id=<?= (int)$p['id'] ?>"><?= htmlspecialchars($p['name']) ?></a>
                                    </div>
                                </td>
                                <td>$<?= number_format((float)$p['price'], 2) ?></td>
                                <td>
                                    <form method="POST" style="display:flex; gap:0.5rem; align-items:center;">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
                                        <input class="form-control" type="number" name="quantity"
                                            value="<?= $it['quantity'] ?>" min="0" max="<?= (int)$p['stock'] ?>"
                                            style="width:70px; padding:0.4rem;">
                                        <button type="submit" class="btn btn-sm btn-outline">OK</button>
                                    </form>
                                </td>
                                <td>$<?= number_format($it['subtotal'], 2) ?></td>
                                <td>
                                    <form method="POST">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
                                        <button type="submit" class="link-action danger">Retirer</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div style="display:flex; justify-content:flex-end; align-items:center; gap:1.5rem; flex-wrap:wrap;">
                <div style="font-size:1.3rem;"><strong>Total : $<?= number_format($total, 2) ?></strong></div>
                <a class="btn" href="checkout.php" style="display:inline-block; width:auto;">Passer la commande</a>
            </div>
        <?php endif; ?>
    </main>

    <?php include __DIR__ . '/includes/site_footer.php'; ?>
</body>

</html>