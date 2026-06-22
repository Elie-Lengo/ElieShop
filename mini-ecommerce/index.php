<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/csrf.php';
$stmt = $pdo->query('SELECT * FROM products ORDER BY created_at DESC');
$products = $stmt->fetchAll();
$added = isset($_GET['added']);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="ElieShop — boutique en ligne simple pour découvrir et acheter nos produits : mode, électronique, accessoires.">
    <link rel="icon" href="assets/favicon.svg" type="image/svg+xml">
    <title>ElieShop | Boutique</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

    <?php require __DIR__ . '/includes/site_header.php'; ?>

    <div class="mobile-search">
        <input type="text" id="searchInputMobile" placeholder="Rechercher...">
    </div>

    <section class="hero">
        <h1>Bienvenue sur ElieShop</h1>
        <p>Découvrez nos produits disponibles.</p>
    </section>

    <main class="container">

        <?php if ($added): ?>
            <div class="alert alert-success">Produit ajouté au panier ✅ — <a href="cart.php">Voir mon panier</a></div>
        <?php endif; ?>

        <div class="search-filters">
            <input type="text" id="searchInput" placeholder="Rechercher un produit...">
            <select id="categoryFilter">
                <option value="all">Toutes les catégories</option>
            </select>
        </div>

        <section class="grid" id="productGrid">
            <?php foreach ($products as $product): ?>
                <?php $inStock = (int)$product['stock'] > 0; ?>
                <article class="card">
                    <div class="card-img">
                        <?php if (!empty($product['image'])): ?>
                            <img src="assets/uploads/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <div class="category"><?= htmlspecialchars($product['category'] ?? 'Produit') ?></div>
                        <h3><?= htmlspecialchars($product['name']) ?></h3>
                        <p><?= htmlspecialchars($product['short_description'] ?? '') ?></p>
                        <div class="price">$<?= number_format((float)$product['price'], 2) ?></div>

                        <?php if (!$inStock): ?>
                            <span class="badge badge-danger" style="margin-bottom:0.6rem;">Rupture de stock</span>
                        <?php endif; ?>

                        <div class="card-actions">
                            <a class="btn btn-outline btn-sm" href="product.php?id=<?= (int)$product['id'] ?>">Voir détails</a>

                            <?php if ($inStock): ?>
                                <form method="POST" action="add_to_cart.php">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <input type="hidden" name="redirect_to" value="index.php">
                                    <button type="submit" class="btn btn-sm">🛒 Ajouter au panier</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
            <?php if (!$products): ?>
                <p style="color:#999;">Aucun produit disponible pour le moment.</p>
            <?php endif; ?>
        </section>
    </main>

    <?php include __DIR__ . '/includes/site_footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const searchInputMobile = document.getElementById('searchInputMobile');
            const searchInputPC = document.getElementById('searchInput');
            const categoryFilter = document.getElementById('categoryFilter');
            const products = document.querySelectorAll('.card');

            const categories = new Set();
            products.forEach(p => {
                const cat = p.querySelector('.category')?.textContent.trim();
                if (cat) categories.add(cat);
            });
            categories.forEach(cat => {
                const option = document.createElement('option');
                option.value = cat;
                option.textContent = cat;
                categoryFilter.appendChild(option);
            });

            function filterProducts() {
                const term = (searchInputMobile?.value || searchInputPC.value || '').toLowerCase();
                const cat = categoryFilter.value;

                products.forEach(p => {
                    const name = p.querySelector('h3').textContent.toLowerCase();
                    const pCat = p.querySelector('.category')?.textContent.trim() || '';
                    const match = name.includes(term) && (cat === 'all' || pCat === cat);
                    p.style.display = match ? '' : 'none';
                });
            }

            if (searchInputMobile) {
                searchInputMobile.addEventListener('input', () => {
                    searchInputPC.value = searchInputMobile.value;
                    filterProducts();
                });
            }
            if (searchInputPC) {
                searchInputPC.addEventListener('input', () => {
                    if (searchInputMobile) searchInputMobile.value = searchInputPC.value;
                    filterProducts();
                });
            }
            categoryFilter.addEventListener('change', filterProducts);
        });
    </script>
</body>

</html>