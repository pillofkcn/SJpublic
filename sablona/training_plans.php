<?php session_start();?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moja stránka</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/treningovy_plan.css">
    <link rel="stylesheet" href="css/banner.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>

<body>
    <?php
        include_once "comps/navbar.php";
    ?>

    <main>
        <section class="banner">
            <div class="container text-white">
                <h1>Tréningové plány</h1>
            </div>
        </section>
        <section class="container">
            <div class="row">
                <div class="col-25 portfolio text-white text-center" id="portfolio-1">
                    Tréningový plán 1
                </div>
                <div class="col-25 portfolio text-white text-center" id="portfolio-2">
                    Tréningový plán 2
                </div>
                <div class="col-25 portfolio text-white text-center" id="portfolio-3">
                    Tréningový plán 3
                </div>
                <div class="col-25 portfolio text-white text-center" id="portfolio-4">
                    Tréningový plán 4
                </div>
            </div>
        </section>

    </main>
    <?php
            include_once "comps/footer.php";
        ?>
    <script src="js/menu.js"></script>
</body>

</html>