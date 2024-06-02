<header class="container main-header">
    <div>
        <a href="index.php">
            <img src="img/logo.png" height="90">
        </a>
    </div>
    <nav class="main-nav">
        <ul class="main-menu" id="main-menu">
            <li><a href="index.php">DOMOV</a></li>
            <li><a href="reservations.php">REZERVÁCIA</a></li>
            <li><a href="trainings.php">TRÉNINGY</a></li>
            <li><a class="KontaktSpace" href="contact.php">KONTAKT</a></li>
            <?php if (isset($_SESSION['user'])): ?>
                <li>Ste prihlásený ako [<?php echo htmlspecialchars($_SESSION['user']['username']); ?>]</li>
                <li><a href="auth.php?action=logout">Odhlásiť sa</a></li>
            <?php else: ?>
                <li><a href="auth.php">Prihlásenie & Registrácia</a></li>

            <?php endif; ?>
        </ul>
        <a class="hamburger" id="hamburger">
            <i class="fa fa-bars"></i>
        </a>
    </nav>
</header>
