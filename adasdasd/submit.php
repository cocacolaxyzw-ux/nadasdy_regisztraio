<?php
declare(strict_types=1);

session_start();

function redirectWithError(string $error): never
{
    header('Location: index.php?error=' . urlencode($error));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$csrf = $_POST['csrf_token'] ?? '';

if (
    !is_string($csrf) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $csrf)
) {
    redirectWithError('invalid');
}

$fullName = trim((string) ($_POST['full_name'] ?? ''));
$schoolName = trim((string) ($_POST['school_name'] ?? ''));
$trade = trim((string) ($_POST['interested_trade'] ?? ''));
$accommodation = $_POST['accommodation'] ?? '';
$graduating = $_POST['is_graduating'] ?? '';

$allowedTrades = [
    'Informatika',
    'Gépészet',
    'Kereskedelem',
    'Turizmus-vendéglátás',
    'Egyéb'
];

$allowedAccommodation = ['Kollégista', 'Bejárós'];

if (
    $fullName === '' ||
    mb_strlen($fullName) > 150 ||
    $schoolName === '' ||
    mb_strlen($schoolName) > 200 ||
    !in_array($trade, $allowedTrades, true) ||
    !in_array($accommodation, $allowedAccommodation, true) ||
    !in_array((string) $graduating, ['0', '1'], true)
) {
    redirectWithError('invalid');
}

require __DIR__ . '/config/database.php';

try {
    $stmt = $pdo->prepare(
        'INSERT INTO registrations
        (full_name, school_name, interested_trade,
         accommodation, is_graduating)
        VALUES
        (:full_name, :school_name, :trade,
         :accommodation, :graduating)'
    );

    $stmt->execute([
        'full_name' => $fullName,
        'school_name' => $schoolName,
        'trade' => $trade,
        'accommodation' => $accommodation,
        'graduating' => (int) $graduating
    ]);

    // Új token, hogy a régi űrlap ne legyen újra felhasználható.
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

    header('Location: index.php?success=1');
    exit;
} catch (PDOException $e) {
    error_log($e->getMessage());
    redirectWithError('database');
}