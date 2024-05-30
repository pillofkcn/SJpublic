<?php
session_start();
require_once 'AuthClass.php';
require_once 'ReservationsClass.php';

$auth = new AuthClass();
$reservationsClass = new ReservationsClass();

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

if (!isset($_SESSION['month'])) {
    $_SESSION['month'] = date('n');
}

if (isset($_GET['action'])) {
    if ($_GET['action'] == 'prev') {
        $_SESSION['month'] = $_SESSION['month'] == 1 ? 12 : $_SESSION['month'] - 1;
    } elseif ($_GET['action'] == 'next') {
        $_SESSION['month'] = $_SESSION['month'] == 12 ? 1 : $_SESSION['month'] + 1;
    }
}

if (isset($_GET['date']) && isset($_GET['reserve']) && $_GET['reserve'] == 1) {
    $date = $_GET['date'];
    $userId = $_SESSION['user']['id'];
    $reservationsClass->addReservation($userId, $date);
    header('Location: reservations.php'); // Redirect to avoid resubmission
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['updateReservation'])) {
        $reservationId = $_POST['id'];
        $newDate = $_POST['date'];
        $reservationsClass->updateReservation($reservationId, $newDate);
    }
    if (isset($_POST['deleteReservation'])) {
        $reservationId = $_POST['id'];
        $reservationsClass->deleteReservation($reservationId);
    }
}

$month = $_SESSION['month'];
$year = date('Y');
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
$firstDayOfMonth = date('w', mktime(0, 0, 0, $month, 1, $year));

$monthNames = [
    1 => "January", 2 => "February", 3 => "March", 4 => "April",
    5 => "May", 6 => "June", 7 => "July", 8 => "August",
    9 => "September", 10 => "October", 11 => "November", 12 => "December"
];

$reservations = $reservationsClass->getReservations();

function renderCalendar($daysInMonth, $firstDayOfMonth, $reservations, $month, $year) {
    $calendar = '<tr>';
    for ($i = 0; $i < $firstDayOfMonth; $i++) {
        $calendar .= '<td></td>';
    }
    for ($day = 1; $day <= $daysInMonth; $day++) {
        $date = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-' . str_pad($day, 2, '0', STR_PAD_LEFT);
        $isReserved = false;
        foreach ($reservations as $reservation) {
            if ($reservation['date'] == $date) {
                $isReserved = true;
                break;
            }
        }
        $bgColor = $isReserved ? 'red' : 'green';
        $calendar .= "<td style='background-color: $bgColor;'><a href='?date=$date&reserve=1' style='color: white; display: block; width: 100%; height: 100%;'>$day</a></td>";
        if (($firstDayOfMonth + $day) % 7 == 0) {
            $calendar .= '</tr><tr>';
        }
    }
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
                    <?php echo renderCalendar($daysInMonth, $firstDayOfMonth, $reservations, $month, $year); ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="user-panel">
        <h3>User Panel</h3>
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
                <?php foreach ($reservations as $reservation): 
                    $user = $auth->getUserById($reservation['user_id']);
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($reservation['id']); ?></td>
                    <td><?php echo htmlspecialchars($reservation['date']); ?></td>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td>
                        <form action="" method="post" style="display:inline-block;">
                            <input type="hidden" name="id" value="<?php echo $reservation['id']; ?>">
                            <input type="date" name="date" value="<?php echo htmlspecialchars($reservation['date']); ?>" required>
                            <input type="submit" name="updateReservation" value="Update">
                        </form>
                        <form action="" method="post" style="display:inline-block;">
                            <input type="hidden" name="id" value="<?php echo $reservation['id']; ?>">
                            <input type="submit" name="deleteReservation" value="Delete" onclick="return confirm('Are you sure?')">
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php include_once "comps/footer.php"; ?>
</body>
</html>
