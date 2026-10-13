<?php
declare(strict_types=1);

session_start();

if (!empty($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

require __DIR__ . '/../config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    $stmt = $pdo->prepare(
        'SELECT id, username, password_hash
         FROM admins WHERE username = ?'
    );
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = (int) $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['admin_csrf'] = bin2hex(random_bytes(32));

        header('Location: index.php');
        exit;
    }

    $error = 'Hibás felhasználónév vagy jelszó.';
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin belépés</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<main class="container">
    <section class="form-card">
        <header class="school-header">
            <img class="school-logo" src="../assets/logo.png"
                 alt="Iskolai logó">
            <h1>Admin belépés</h1>
        </header>

        <?php if ($error !== ''): ?>
            <div class="alert error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <label for="username">Felhasználónév</label>
            <input id="username" name="username"
                   type="text" autocomplete="username" required>

            <label for="password">Jelszó</label>
            <input id="password" name="password"
                   type="password" autocomplete="current-password" required>

            <button type="submit" class="submit-button">
                Belépés
            </button>
        </form>
    </section>
</main>
</body>
</html>