<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_login();

$error = "";

// Extensions autorisées et leur vrai type MIME attendu (on ne fait pas
// confiance au nom du fichier envoyé par le navigateur)
$allowedMime = [
    'jpg'  => ['image/jpeg'],
    'jpeg' => ['image/jpeg'],
    'png'  => ['image/png'],
    'webp' => ['image/webp'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $error = "Session expirée, merci de réessayer.";
    } else {
        $name = trim($_POST['name'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        $stock = (int)($_POST['stock'] ?? 0);
        $category = trim($_POST['category'] ?? '');
        $short_description = trim($_POST['short_description'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $imageName = null;

        if ($name === '') {
            $error = "Le nom du produit est obligatoire.";
        } elseif ($price <= 0) {
            $error = "Le prix doit être supérieur à 0.";
        } elseif ($stock < 0) {
            $error = "Le stock ne peut pas être négatif.";
        }

        if (!$error && !empty($_FILES['image']['name'])) {
            $extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $realMime = $finfo->file($_FILES['image']['tmp_name']);

            if (!isset($allowedMime[$extension]) || !in_array($realMime, $allowedMime[$extension], true)) {
                $error = "Format d'image non valide (jpg, png ou webp uniquement).";
            } else {
                $uploadDir = __DIR__ . '/../assets/uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $imageName = uniqid('product_', true) . '.' . $extension;
                move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $imageName);
            }
        }

        if (!$error) {
            $stmt = $pdo->prepare('INSERT INTO products (name, price, category, short_description, description, image, stock) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$name, $price, $category, $short_description, $description, $imageName, $stock]);
            header('Location: index.php?success=1');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>Ajouter un produit | ElieShop Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

    <header class="admin-topbar">
        <div class="brand">ElieShop <span>Admin</span></div>
        <nav>
            <a href="index.php">Retour admin</a>
            <span class="admin-user">👤 <?= htmlspecialchars(current_admin_username() ?? '') ?></span>
            <a href="logout.php">Déconnexion</a>
        </nav>
    </header>

    <main class="container">
        <div class="form-card">
            <h1 class="page-title" style="font-size:1.6rem;">Ajouter un nouveau produit</h1>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label for="name">Nom du produit</label>
                    <input class="form-control" type="text" id="name" name="name" required
                        value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="price">Prix ($)</label>
                    <input class="form-control" type="number" id="price" name="price" step="0.01" min="0.01" required
                        value="<?= htmlspecialchars($_POST['price'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="stock">Stock disponible</label>
                    <input class="form-control" type="number" id="stock" name="stock" min="0" step="1"
                        value="<?= htmlspecialchars($_POST['stock'] ?? '10') ?>">
                </div>

                <div class="form-group">
                    <label for="category">Catégorie</label>
                    <input class="form-control" type="text" id="category" name="category"
                        value="<?= htmlspecialchars($_POST['category'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="short_description">Petite description</label>
                    <input class="form-control" type="text" id="short_description" name="short_description"
                        value="<?= htmlspecialchars($_POST['short_description'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="description">Description complète</label>
                    <textarea class="form-control" id="description" name="description" rows="5"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="image">Image</label>
                    <input type="file" id="image" name="image" accept="image/*">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-block">Enregistrer le produit</button>
                </div>
            </form>
        </div>
    </main>

</body>

</html>