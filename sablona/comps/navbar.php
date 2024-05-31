<?php
?>
<header class="container main-header">
    <div>
        <a href="index.php">
            <img src="img/logo.png" height="90">
        </a>
    </div>
    <nav class="main-nav">
        <ul class="main-menu" id="main-menu">
            <li><a href="index.php">Domov</a></li>
            <li><a href="reservations.php">Rezervácia</a></li>
            <li><a href="jedalnicek.php">Jedálničky</a></li>
            <li><a href="treningovy_plan.php">Tréningové plány</a></li>
            <li><a href="qna.php">Q&A</a></li>
            <li><a class="KontaktSpace" href="kontakt.php">Kontakt</a></li>
            <?php if (isset($_SESSION['user'])): ?>
                <li>Logged in as <?php echo htmlspecialchars($_SESSION['user']['username']); ?></li>
                <li><a href="auth.php?action=logout">Logout</a></li>
            <?php else: ?>
                <li><a href="auth.php">Login & Registrácia</a></li>

            <?php endif; ?>
        </ul>
        <a class="hamburger" id="hamburger">
            <i class="fa fa-bars"></i>
        </a>
    </nav>
</header>
