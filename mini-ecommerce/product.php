<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/csrf.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    http_response_code(404);
?>
    <!DOCTYPE html>
    <html lang="fr">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Produit introuvable | ElieShop</title>
        <link rel="stylesheet" href="assets/css/style.css">
    </head>

    <body>
        <?php require __DIR__ . '/includes/site_header.php'; ?>
        <main class="container">
            <div class="error-page">
                <h1>404</h1>
                <p>Ce produit n'existe pas ou a été supprimé.</p>
                <a class="btn" href="index.php" style="display:inline-block; width:auto;">Retour à la boutique</a>
            </div>
        </main>
        <?php include __DIR__ . '/includes/site_footer.php'; ?>
    </body>

    </html>
<?php
    exit;
}

$inStock = (int)$product['stock'] > 0;
$added = isset($_GET['added']);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= htmlspecialchars($product['short_description'] ?? $product['name']) ?>">
    <link rel="icon" href="assets/favicon.svg" type="image/svg+xml">
    <title><?= htmlspecialchars($product['name']) ?> | ElieShop</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

    <?php require __DIR__ . '/includes/site_header.php'; ?>

    <main class="container">
        <a href="index.php" class="back-btn">← Retour</a>

        <?php if ($added): ?>
            <div class="alert alert-success">Produit ajouté au panier ✅ — <a href="cart.php">Voir mon panier</a></div>
        <?php endif; ?>

        <div class="product-detail">
            <div class="pd-image">
                <?php if (!empty($product['image'])): ?>
                    <img src="assets/uploads/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                <?php endif; ?>
            </div>
            <div class="pd-info">
                <h1><?= htmlspecialchars($product['name']) ?></h1>
                <div class="pd-price">$<?= number_format((float)$product['price'], 2) ?></div>

                <?php if ($inStock): ?>
                    <span class="badge badge-success" style="margin-bottom:1rem; width:fit-content;"><?= (int)$product['stock'] ?> en stock</span>
                <?php else: ?>
                    <span class="badge badge-danger" style="margin-bottom:1rem; width:fit-content;">Rupture de stock</span>
                <?php endif; ?>

                <p class="pd-short"><?= htmlspecialchars($product['short_description'] ?? '') ?></p>
                <p class="pd-desc"><?= nl2br(htmlspecialchars($product['description'] ?? '')) ?></p>

                <?php if ($inStock): ?>
                    <form method="POST" action="add_to_cart.php" class="add-to-cart-form">
                        <?= csrf_field() ?>
                        <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
                        <input type="hidden" name="redirect_to" value="product.php?id=<?= (int)$product['id'] ?>">
                        <label for="quantity" style="font-weight:600; font-size:0.9rem;">Quantité</label>
                        <input class="form-control" type="number" id="quantity" name="quantity"
                            value="1" min="1" max="<?= (int)$product['stock'] ?>" style="width:90px;">
                        <button type="submit" class="btn">🛒 Ajouter au panier</button>
                    </form>
                <?php else: ?>
                    <button class="btn" disabled>Indisponible</button>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/includes/site_footer.php'; ?>
</body>

</html>