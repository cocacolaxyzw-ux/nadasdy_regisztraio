<?php
declare(strict_types=1);

require __DIR__ . '/../config/database.php';

$message = '';

$count = (int) $pdo->query(
    'SELECT COUNT(*) FROM admins'
)->fetchColumn();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $count === 0) {
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if (
        strlen($username) < 3 ||
        strlen($username) > 100 ||
        strlen($password) < 12
    ) {
        $message = 'A felhasználónév legalább 3 karakter,
                    a jelszó legalább 12 karakter legyen.';
    } else {
        $stmt = $pdo->prepare(
            'INSERT INTO admins (username, password_hash)
             VALUES (?, ?)'
        );

        $stmt->execute([
            $username,
            password_hash($password, PASSWORD_DEFAULT)
        ]);

        $message = 'Az adminfiók elkészült. Töröld a setup.php fájlt!';
        $count = 1;
    }
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin beállítása</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<main class="container">
    <section class="form-card">
        <h1>Adminfiók létrehozása</h1>

        <?php if ($message !== ''): ?>
            <p><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <?php if ($count === 0): ?>
            <form method="POST">
                <label for="username">Felhasználónév</label>
                <input id="username" name="username"
                       type="text" minlength="3" maxlength="100" required>

                <label for="password">Jelszó (minimum 12 karakter)</label>
                <input id="password" name="password"
                       type="password" minlength="12" required>

                <button class="submit-button" type="submit">
                    Admin létrehozása
                </button>
            </form>
        <?php else: ?>
            <p>Már létezik adminfiók. Új fiók itt nem hozható létre.</p>
            <p><a href="login.php">Tovább a belépéshez</a></p>
        <?php endif; ?>
    </section>
</main>
</body>
</html>