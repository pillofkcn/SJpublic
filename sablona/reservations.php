<?php
require_once 'classes/Auth.php';
require_once 'classes/Reservations.php';

session_start();

$auth = new Auth();
$reservationsClass = new Reservations();

// kontrola, ci je uzivatel prihlaseny
$isLoggedIn = isset($_SESSION['user']);

// ak nie je v session nastaveny mesiac (co nie je), nastavi sa na aktualny
if (!isset($_SESSION['month'])) {
    $_SESSION['month'] = date('n');
}

if (isset($_GET['action'])) {
    // ak ideme na predchadzajuci mesiac a je nastaveny januar, skocime na december
    if ($_GET['action'] == 'prev') {
        $_SESSION['month'] = $_SESSION['month'] == 1 ? 12 : $_SESSION['month'] - 1;
    // ak ideme na nasledujuci mesiac a je nastaveny december, skocime na januar
    } elseif ($_GET['action'] == 'next') {
        $_SESSION['month'] = $_SESSION['month'] == 12 ? 1 : $_SESSION['month'] + 1;
    }
}

// ak je uzivatel prihlaseny a klikol na policko kalendara
if ($isLoggedIn && isset($_GET['date']) && isset($_GET['reserve']) && $_GET['reserve'] == 1) {
    $date = $_GET['date'];
    $userId = $_SESSION['user']['id'];
    // vytvorenie rezervacie
    $reservationsClass->addReservation($userId, $date);
    header('Location: reservations.php');
    exit();
}

// ak je uzivatel prihlaseny
if ($isLoggedIn && $_SERVER['REQUEST_METHOD'] == 'POST') {
    // a klikne na update, updatuje rezervaciu
    if (isset($_POST['updateReservation'])) {
        $reservationId = $_POST['id'];
        $newDate = $_POST['date'];
        $reservationsClass->updateReservation($reservationId, $newDate);
    }
    // a klikne na delete, zmaze rezervaciu
    if (isset($_POST['deleteReservation'])) {
        $reservationId = $_POST['id'];
        $reservationsClass->deleteReservation($reservationId);
    }
}

// nastavenie aktualneho mesiaca a roka
$month = $_SESSION['month'];
$year = date('Y');

// funkcia na ziskanie poctu dni mesiacov
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
// funkcia na ziskanie prveho dna v mesiaci (po-ne, vrati cislo 1-7)
$firstDayOfMonth = date('w', mktime(0, 0, 0, $month, 1, $year));


$monthNames = [
    1 => "Jan", 2 => "Feb", 3 => "Mar", 4 => "Apr",
    5 => "Máj", 6 => "Jún", 7 => "Júl", 8 => "Aug",
    9 => "Sep", 10 => "Oct", 11 => "Nov", 12 => "Dec"
];

// ziskanie vsetkych rezervacii
$reservations = $reservationsClass->getReservations();

// vutvorenie kalendara
function renderCalendar($daysInMonth, $firstDayOfMonth, $reservations, $month, $year, $isLoggedIn) {
    $calendar = '<tr>';
    // vlozenie prazdnych buniek pred prvy den mesiaca
    for ($i = 0; $i < $firstDayOfMonth; $i++) {
        $calendar .= '<td></td>';
    }
    
    for ($day = 1; $day <= $daysInMonth; $day++) {
        // zostavenie datumu pre kazdy den YYYY-MM-DD
        $date = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-' . str_pad($day, 2, '0', STR_PAD_LEFT);
        // predpoklad, ze datum nie je rezervovany, flag false
        $isReserved = false;
        // prejdeme rezervacie, ak sa zhoduju s aktualnym datumom v kalendari, nastavime flag na true
        foreach ($reservations as $reservation) {
            if ($reservation['date'] == $date) {
                $isReserved = true;
                break;
            }
        }
        // nastavenie farby podla flagu
        $bgColor = $isReserved ? 'red' : 'green';
        $calendar .= "<td style='background-color: $bgColor;'>";
        // ak je uzivael prihlaseny, vie na policka klikat a rezervovat dni v kalendari
        if ($isLoggedIn) {
            $calendar .= "<a href='?date=$date&reserve=1' style='color: white; display: block; width: 100%; height: 100%;'>$day</a>";
        // ak nie je prihlaseny, vie len prehliadat kalendar
        } else {
            $calendar .= "<span style='color: white; display: block; width: 100%; height: 100%;'>$day</span>";
        }
        $calendar .= "</td>";
        // rozsirenie kalendara o novy riadok ak je to potrebne
        if (($firstDayOfMonth + $day) % 7 == 0) {
            $calendar .= '</tr><tr>';
        }
    }
    // pridanie prazdnych buniek na koniec
    $remainingDays = (7 - ($firstDayOfMonth + $daysInMonth) % 7) % 7;
    for ($i = 0; $i < $remainingDays; $i++) {
        $calendar .= '<td></td>';
    }
    $calendar .= '</tr>';
    return $calendar;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation System</title>
    <link rel="stylesheet" href="css/reservations.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include_once "comps/navbar.php"; ?>

    <div class="calendar-container">
        <div class="navigation">
            <a href="?action=prev">&#60;</a>
            <!-- zobrazeny mesiac a rok, buttony -->
            <h2><?php echo $monthNames[$month] . " " . $year; ?></h2>
            <a href="?action=next">&#62;</a>
        </div>
        <div class="calendar-wrapper">
            <table class="calendar">
                <thead>
                    <tr>
                        <th>Sun</th>
                        <th>Mon</th>
                        <th>Tue</th>
                        <th>Wed</th>
                        <th>Thu</th>
                        <th>Fri</th>
                        <th>Sat</th>
                    </tr>
                </thead>
                <tbody>
                    <?php echo renderCalendar($daysInMonth, $firstDayOfMonth, $reservations, $month, $year, $isLoggedIn); ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ak je uzivatel prihlaseny (neprihlaseny panel nevidi) -->
    <?php if ($isLoggedIn): ?>
        <div class="user-panel">
            <!-- admin panel ak je prihlaseny user admin, inak user panel -->
            <h3><?php echo $_SESSION['user']['is_admin'] == 1 ? 'Admin Panel' : 'User Panel'; ?></h3>
            <table class="user-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Username</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- ziskanie userov rezervacii -->
                    <?php foreach ($reservations as $reservation): 
                        $user = $auth->getUserById($reservation['user_id']);
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($reservation['id']); ?></td>
                        <td><?php echo htmlspecialchars($reservation['date']); ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td>
                            <!-- ak je prihlaseny user admin, alebo ide o rezervacie prihlaseneho usera -->
                            <?php if ($isLoggedIn && ($_SESSION['user']['is_admin'] == 1 || $reservation['user_id'] == $_SESSION['user']['id'])): ?>
                            <!-- moze aktualizovat -->
                            <form action="" method="post" style="display:inline-block;">
                                <input type="hidden" name="id" value="<?php echo $reservation['id']; ?>">
                                <input type="date" name="date" value="<?php echo htmlspecialchars($reservation['date']); ?>" required>
                                <input type="submit" name="updateReservation" value="Update">
                            </form>
                            <!-- moze mazat -->
                            <form action="" method="post" style="display:inline-block;">
                                <input type="hidden" name="id" value="<?php echo $reservation['id']; ?>">
                                <input type="submit" name="deleteReservation" value="Delete" onclick="return confirm('Are you sure?')">
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <?php include_once "comps/footer.php"; ?>
</body>
</html>
