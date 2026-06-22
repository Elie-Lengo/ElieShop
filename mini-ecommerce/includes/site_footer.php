<?php
require_once __DIR__ . '/customer_auth.php';
$__footerCustomer = current_customer();
// $footerBase permet d'inclure ce footer depuis un sous-dossier (ex: account/)
// en gardant des liens corrects. Définir $footerBase = '../' avant l'include
// si le fichier appelant n'est pas à la racine du site.
$__base = $footerBase ?? '';
?>
<footer class="site-footer">
    <div class="footer-grid">
        <div>
            <h4>ElieShop</h4>
            <p>Votre boutique en ligne simple et rapide pour faire vos achats du quotidien.</p>
        </div>
        <div>
            <h4>Navigation</h4>
            <ul>
                <li><a href="<?= $__base ?>index.php">Boutique</a></li>
                <li><a href="<?= $__base ?>cart.php">Mon panier</a></li>
                <?php if ($__footerCustomer): ?>
                    <li><a href="<?= $__base ?>account/index.php">Mon compte</a></li>
                <?php else: ?>
                    <li><a href="<?= $__base ?>account/login.php">Connexion</a></li>
                <?php endif; ?>
            </ul>
        </div>
        <div>
            <h4>Contact</h4>
            <ul>
                <li><a href="mailto:contact@elieshop.test">elielengo007@gmail.com</a></li>
                <li>+243 891 814 0022</li>
                <li>Kinshasa, RDC</li>
            </ul>
        </div>
        <div>
            <h4>Paiement</h4>
            <p>Mobile Money &amp; carte bancaire acceptés à la livraison ou en ligne.</p>
        </div>
    </div>
    <div class="footer-bottom">
        &copy; <?= date('Y') ?> ElieShop. Tous droits réservés.
    </div>
</footer>