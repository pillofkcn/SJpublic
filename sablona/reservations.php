<?php
// Start session and include necessary classes
session_start();
require_once 'AuthClass.php';
require_once 'ReservationsClass.php';

// Initialize authentication and reservation objects
$auth = new AuthClass();
$reservationsClass = new ReservationsClass();

// Redirect to login page if user is not logged in
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

// Set the current month if it's not already set in the session
if (!isset($_SESSION['month'])) {
    $_SESSION['month'] = date('n');
}

// Handle month navigation actions
if (isset($_GET['action'])) {
    if ($_GET['action'] == 'prev') {
        $_SESSION['month'] = $_SESSION['month'] == 1 ? 12 : $_SESSION['month'] - 1;
    } elseif ($_GET['action'] == 'next') {
        $_SESSION['month'] = $_SESSION['month'] == 12 ? 1 : $_SESSION['month'] + 1;
    }
}

// Handle reservation request
if (isset($_GET['date']) && isset($_GET['reserve']) && $_GET['reserve'] == 1) {
    $date = $_GET['date'];
    $userId = $_SESSION['user']['id'];
    $reservationsClass->addReservation($userId, $date);
    header('Location: reservations.php'); // Redirect to avoid form resubmission
    exit();
}

// Handle form submissions for updating and deleting reservations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['updateReservation'])) {
        $reservationId = $_POST['id'];
        $newDate = $_POST['date'];
        $reservation = $reservationsClass->getReservationById($reservationId);
        if ($_SESSION['user']['is_admin'] == 1 || $reservation['user_id'] == $_SESSION['user']['id']) {
            $reservationsClass->updateReservation($reservationId, $newDate);
        }
    }
    if (isset($_POST['deleteReservation'])) {
        $reservationId = $_POST['id'];
        $reservation = $reservationsClass->getReservationById($reservationId);
        if ($_SESSION['user']['is_admin'] == 1 || $reservation['user_id'] == $_SESSION['user']['id']) {
            $reservationsClass->deleteReservation($reservationId);
        }
    }
}

// Set the current month and year
$month = $_SESSION['month'];
$year = date('Y');

// Get the number of days in the current month and the first day of the month
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
$firstDayOfMonth = date('w', mktime(0, 0, 0, $month, 1, $year));

// Month names array
$monthNames = [
    1 => "Jan", 2 => "Feb", 3 => "Mar", 4 => "Apr",
    5 => "Máj", 6 => "Jún", 7 => "Júl", 8 => "Aug",
    9 => "Sep", 10 => "Oct", 11 => "Nov", 12 => "Dec"
];

// Fetch all reservations
$reservations = $reservationsClass->getReservations();

// Function to render the calendar
function renderCalendar($daysInMonth, $firstDayOfMonth, $reservations, $month, $year) {
    $calendar = '<tr>';
    // Add empty cells before the first day of the month
    for ($i = 0; $i < $firstDayOfMonth; $i++) {
        $calendar .= '<td></td>';
    }
    // Add days to the calendar
    for ($day = 1; $day <= $daysInMonth; $day++) {
        $date = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-' . str_pad($day, 2, '0', STR_PAD_LEFT);
        $isReserved = false;
        foreach ($reservations as $reservation) {
            if ($reservation['date'] == $date) {
                $isReserved = true;
                break;
            }
        }
        // Set background color based on reservation status
        $bgColor = $isReserved ? 'red' : 'green';
        $calendar .= "<td style='background-color: $bgColor;'><a href='?date=$date&reserve=1' style='color: white; display: grid; width: 100%; height: 100%;'>$day</a></td>";
        // Add a new row at the end of the week
        if (($firstDayOfMonth + $day) % 7 == 0) {
            $calendar .= '</tr><tr>';
        }
    }
    // Add empty cells at the end of the month
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
            <a href="?action=prev"><</a>
            <h2><?php echo $monthNames[$month] . " " . $year; ?></h2>
            <a href="?action=next">></a>
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
                <?php foreach ($reservations as $reservation) :
                    $user = $auth->getUserById($reservation['user_id']);
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($reservation['id']); ?></td>
                        <td><?php echo htmlspecialchars($reservation['date']); ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td>
                            <?php if ($_SESSION['user']['is_admin'] == 1 || $reservation['user_id'] == $_SESSION['user']['id']) : ?>
                                <form action="" method="post" style="display:inline-block;">
                                    <input type="hidden" name="id" value="<?php echo $reservation['id']; ?>">
                                    <input type="date" name="date" value="<?php echo htmlspecialchars($reservation['date']); ?>" required>
                                    <input type="submit" name="updateReservation" value="Update">
                                </form>
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

    <?php include_once "comps/footer.php"; ?>
</body>

</html>
