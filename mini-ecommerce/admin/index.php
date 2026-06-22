<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_login();

$stmt = $pdo->query('SELECT * FROM products ORDER BY created_at DESC');
$products = $stmt->fetchAll();
$success = isset($_GET['success']);
$updated = isset($_GET['updated']);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>Admin - Gestion des produits | ElieShop</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

    <header class="admin-topbar">
        <div class="brand">ElieShop <span>Admin</span></div>
        <nav>
            <a href="../index.php">Voir la boutique</a>
            <a href="index.php">Produits</a>
            <a href="orders.php">Commandes</a>
            <a href="add_product.php" class="btn btn-sm">+ Ajouter un produit</a>
            <span class="admin-user">👤 <?= htmlspecialchars(current_admin_username() ?? '') ?></span>
            <a href="logout.php">Déconnexion</a>
        </nav>
    </header>

    <main class="container">
        <h1 class="page-title">Gestion des produits</h1>
        <p class="page-subtitle"><?= count($products) ?> produit(s) au total.</p>

        <?php if ($success): ?>
            <div class="alert alert-success">Action effectuée avec succès.</div>
        <?php endif; ?>
        <?php if ($updated): ?>
            <div class="alert alert-success">Produit mis à jour avec succès.</div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Nom</th>
                        <th>Catégorie</th>
                        <th>Prix</th>
                        <th>Stock</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td>
                                <?php if (!empty($product['image'])): ?>
                                    <img class="thumb" src="../assets/uploads/<?= htmlspecialchars($product['image']) ?>" alt="">
                                <?php else: ?>
                                    <div class="thumb-placeholder">No img</div>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($product['name']) ?></td>
                            <td><?= htmlspecialchars($product['category'] ?? '-') ?></td>
                            <td>$<?= number_format((float)$product['price'], 2) ?></td>
                            <td>
                                <?php if ((int)$product['stock'] <= 0): ?>
                                    <span class="badge badge-danger">Rupture</span>
                                <?php elseif ((int)$product['stock'] <= 3): ?>
                                    <span class="badge badge-muted"><?= (int)$product['stock'] ?> restants</span>
                                <?php else: ?>
                                    <span class="badge badge-success"><?= (int)$product['stock'] ?> en stock</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="actions-cell">
                                    <a href="edit_product.php?id=<?= (int)$product['id'] ?>">Modifier</a>
                                    <form action="delete_product.php" method="POST"
                                        onsubmit="return confirm('Supprimer définitivement « <?= htmlspecialchars(addslashes($product['name'])) ?> » ?');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= (int)$product['id'] ?>">
                                        <button type="submit" class="link-action danger">Supprimer</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$products): ?>
                        <tr>
                            <td colspan="6" style="text-align:center; color:#999; padding:2rem;">Aucun produit pour le moment.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

</body>

</html>