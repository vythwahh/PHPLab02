<?php
require __DIR__ . '/lib/bootstrap.php';

$ref = (string) ($_GET['ref'] ?? '');
$index = find_booking_index_by_ref($ref);
$booking = ($index !== null) ? $_SESSION['bookings'][$index] : null;
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Booking Details</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="<?= e($theme) ?>">
<main class="wrap">
    <header>
        <h1>Booking Details</h1>
        <nav>
            <a href="index.php">Catalog</a>
            <a href="dashboard.php">My bookings</a>
        </nav>
    </header>

    <?php if (!$booking): ?>
        <div class="panel">
            <h2>Booking not found</h2>
            <p>The booking reference you provided does not exist or has been cleared.</p>
            <a class="btn" href="dashboard.php">Back to My Bookings</a>
        </div>
    <?php else: ?>
        <div class="panel">
            <p><strong>Booking Reference:</strong> <?= e($booking['ref']) ?></p>
            <p><strong>Attendee:</strong> <?= e($booking['full_name']) ?></p>
            <p><strong>Email:</strong> <?= e($booking['email']) ?></p>
            <p><strong>Workshop:</strong> <?= e($booking['workshop']) ?></p>
            <p><strong>Quantity:</strong> <?= e($booking['quantity']) ?> tickets</p>
            <p><strong>Unit Fee:</strong> <?= number_format($booking['unit_fee'] ?? ($booking['amount'] / $booking['quantity'])) ?> VND</p>
            <p><strong>Total Amount:</strong> <?= number_format($booking['amount']) ?> VND</p>
            <hr>
            <p>
                <a class="btn" href="booking_edit.php?ref=<?= urlencode($booking['ref']) ?>">Edit this booking</a> 
                <a href="dashboard.php" style="margin-left: 10px;">Back to My Bookings</a>
            </p>
            <p class="muted"><small>Booking information is read from the current PHP session.</small></p>
        </div>
    <?php endif; ?>
</main>
</body>
</html>