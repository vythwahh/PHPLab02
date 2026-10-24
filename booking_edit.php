<?php
require __DIR__ . '/lib/bootstrap.php';

$ref = (string) ($_REQUEST['ref'] ?? '');
$index = find_booking_index_by_ref($ref);

if ($index === null) {
    http_response_code(404);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Booking Not Found</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="<?= e($theme) ?>">
<main class="wrap">
    <div class="panel">
        <h2>Booking not found</h2>
        <p>The requested booking reference was not found in your session.</p>
        <a href="dashboard.php">Back to My Bookings</a>
    </div>
</main>
</body>
</html>
<?php
    exit;
}

$booking = $_SESSION['bookings'][$index];
$errors = [];
$formData = [
    'full_name' => $booking['full_name'],
    'email' => $booking['email'],
    'tickets' => $booking['quantity']
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid($_POST)) {
        http_response_code(403);
        exit('Invalid CSRF token');
    }

    $formData['full_name'] = $_POST['full_name'] ?? '';
    $formData['email'] = $_POST['email'] ?? '';
    $formData['tickets'] = $_POST['tickets'] ?? '';

    $postPayload = $formData;
    $postPayload['workshop_id'] = $booking['workshop_id'] ?? '';

    $valResult = validate_booking($postPayload, $workshops);
    $errors = $valResult['errors'];

    if (empty($errors)) {
        $wsId = $booking['workshop_id'] ?? '';
        $unitFee = isset($workshops[$wsId]) ? $workshops[$wsId]['fee'] : ($booking['unit_fee'] ?? 0);
        $ticketsCount = (int) $valResult['data']['quantity'];

        $_SESSION['bookings'][$index]['full_name'] = $valResult['data']['full_name'];
        $_SESSION['bookings'][$index]['email'] = $valResult['data']['email'];
        $_SESSION['bookings'][$index]['quantity'] = $ticketsCount;
        $_SESSION['bookings'][$index]['unit_fee'] = $unitFee;
        $_SESSION['bookings'][$index]['amount'] = $unitFee * $ticketsCount;

        $_SESSION['flash'] = 'Booking updated successfully.';
        redirect_to('booking_detail.php?ref=' . urlencode($ref));
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Booking</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="<?= e($theme) ?>">
<main class="wrap">
    <header>
        <h1>Edit Booking</h1>
        <nav>
            <a href="index.php">Catalog</a>
            <a href="dashboard.php">My bookings</a>
        </nav>
    </header>

    <form method="post" action="booking_edit.php?ref=<?= urlencode($ref) ?>" class="panel form-stack" novalidate>
        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">

        <label>Booking Reference
            <input type="text" value="<?= e($booking['ref']) ?>" disabled>
        </label>

        <label>Workshop
            <input type="text" value="<?= e($booking['workshop']) ?>" disabled>
        </label>

        <label>Full Name
            <input name="full_name" value="<?= e($formData['full_name']) ?>">
        </label>
        <small class="error"><?= e($errors['full_name'] ?? '') ?></small>

        <label>Email
            <input name="email" value="<?= e($formData['email']) ?>">
        </label>
        <small class="error"><?= e($errors['email'] ?? '') ?></small>

        <label>Tickets (1-4)
            <input name="tickets" type="number" value="<?= e($formData['tickets']) ?>">
        </label>
        <small class="error"><?= e($errors['quantity'] ?? '') ?></small>

        <p><strong>Current total: <?= number_format($booking['amount']) ?> VND</strong></p>
        <p class="muted"><small>Unit fee: <?= number_format($booking['unit_fee'] ?? 0) ?> VND - Total will be recalculated after saving.</small></p>

        <div>
            <button type="submit">Save Changes</button>
            <a class="btn" style="background:#a9b8ca;" href="booking_detail.php?ref=<?= urlencode($ref) ?>">Cancel</a>
        </div>
    </form>

    <?php if (!empty($errors)): ?>
        <p class="error">Invalid data is not saved to the Session.</p>
    <?php endif; ?>

    <p class="muted"><small>The server must validate name, email and ticket quantity, verify the CSRF token and update the correct booking in $_SESSION.</small></p>
</main>
</body>
</html>