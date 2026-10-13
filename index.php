<?php
session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$success = isset($_GET['success']);
$error = isset($_GET['error']);
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jelentkezés – Nádasdy Tamás Technikum és Kollégium</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<main class="container">
    <section class="form-card">
        <header class="school-header">
            <img
                src="assets/logo.png"
                alt="Nádasdy Tamás Technikum és Kollégium logója"
                class="school-logo"
            >
            <h1>Regisztráció</h1>
            <p>Nádasdy Tamás Technikum és Kollégium</p>
            <p class="subtitle">
                Add meg az adataidat és az érdeklődési körödet!
            </p>
        </header>

        <?php if ($success): ?>
            <div class="alert success">
                Sikeres regisztráció! Köszönjük a jelentkezésed.
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert error">
                <?= htmlspecialchars(
                    $_GET['error'] === 'invalid'
                        ? 'Ellenőrizd a megadott adatokat!'
                        : 'Hiba történt a mentés során. Próbáld újra!'
                ) ?>
            </div>
        <?php endif; ?>

        <form action="submit.php" method="POST">
            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>"
            >

            <label for="full_name">Teljes név *</label>
            <input
                type="text"
                id="full_name"
                name="full_name"
                maxlength="150"
                autocomplete="name"
                required
            >

            <label for="school_name">Melyik iskolából érkezel? *</label>
            <input
                type="text"
                id="school_name"
                name="school_name"
                maxlength="200"
                required
            >

            <label for="interested_trade">Melyik szak érdekel? *</label>
            <select id="interested_trade" name="interested_trade" required>
                <option value="">Válassz szakot!</option>
                <option value="Informatika">Informatika</option>
                <option value="Gépészet">Gépészet</option>
                <option value="Kereskedelem">Kereskedelem</option>
                <option value="Turizmus-vendéglátás">
                    Turizmus-vendéglátás
                </option>
                <option value="Egyéb">Egyéb</option>
            </select>

            <fieldset>
                <legend>Kollégista vagy bejárós lennél? *</legend>

                <label class="radio-label">
                    <input type="radio" name="accommodation"
                           value="Kollégista" required>
                    Kollégista
                </label>

                <label class="radio-label">
                    <input type="radio" name="accommodation"
                           value="Bejárós" required>
                    Bejárós
                </label>
            </fieldset>

            <fieldset>
                <legend>Végzős vagy? *</legend>

                <label class="radio-label">
                    <input type="radio" name="is_graduating"
                           value="1" required>
                    Igen
                </label>

                <label class="radio-label">
                    <input type="radio" name="is_graduating"
                           value="0" required>
                    Nem
                </label>
            </fieldset>

            <button type="submit" class="submit-button">
                Regisztráció elküldése
            </button>

            <p class="required-note">* Kötelező mezők</p>
        </form>
    </section>
</main>
</body>
</html>