<?php
declare(strict_types=1);

session_start();

// Bejelentkezés ellenőrzése
if (empty($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// CSRF-token létrehozása
if (empty($_SESSION['admin_csrf'])) {
    $_SESSION['admin_csrf'] = bin2hex(random_bytes(32));
}

// Adatbázis-kapcsolat
require __DIR__ . '/../config/database.php';

// Összes jelentkező
$total = (int) $pdo->query(
    'SELECT COUNT(*) FROM registrations'
)->fetchColumn();

// Végzősök száma
$graduates = (int) $pdo->query(
    'SELECT COUNT(*) FROM registrations WHERE is_graduating = 1'
)->fetchColumn();

// Kollégisták száma
$boarders = (int) $pdo->query(
    "SELECT COUNT(*) FROM registrations
     WHERE accommodation = 'Kollégista'"
)->fetchColumn();

// Bejárósok száma
$commuters = (int) $pdo->query(
    "SELECT COUNT(*) FROM registrations
     WHERE accommodation = 'Bejárós'"
)->fetchColumn();

// Jelentkezők szakok szerint
$tradeStats = $pdo->query(
    'SELECT interested_trade, COUNT(*) AS total
     FROM registrations
     GROUP BY interested_trade
     ORDER BY total DESC, interested_trade ASC'
)->fetchAll(PDO::FETCH_ASSOC);

// Összes regisztráció
$registrations = $pdo->query(
    'SELECT id, full_name, school_name, interested_trade,
            accommodation, is_graduating, created_at
     FROM registrations
     ORDER BY created_at DESC, id DESC'
)->fetchAll(PDO::FETCH_ASSOC);

// Biztonságos HTML-kiírás
function e(mixed $value): string
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Adminpanel – Jelentkezések</title>

    <link rel="stylesheet" href="../assets/style.css">

    <style>
        body {
            padding: 20px;
        }

        .dashboard {
            max-width: 1400px;
            margin: auto;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 14px;
            margin: 24px 0;
        }

        .stat {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 15px #17345f12;
        }

        .stat strong {
            display: block;
            font-size: 30px;
            color: #17345f;
            margin-top: 8px;
        }

        .table-wrap {
            overflow-x: auto;
            background: white;
            border-radius: 12px;
            margin: 20px 0;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            min-width: 850px;
        }

        th,
        td {
            text-align: left;
            padding: 12px;
            border-bottom: 1px solid #e2e7ef;
        }

        th {
            background: #17345f;
            color: white;
        }

        tbody tr:hover {
            background: #f5f7fb;
        }

        .delete-button {
            background: #a32121;
            color: white;
            border: 0;
            border-radius: 6px;
            padding: 8px 12px;
            cursor: pointer;
        }

        .delete-button:hover {
            background: #7e1919;
        }

        .logout {
            display: inline-block;
            background: #17345f;
            color: white;
            padding: 10px 14px;
            text-decoration: none;
            border-radius: 7px;
        }

        .logout:hover {
            background: #244b7e;
        }

        h2 {
            color: #17345f;
        }

        @media (max-width: 600px) {
            body {
                padding: 10px;
            }

            .stat {
                padding: 15px;
            }

            .stat strong {
                font-size: 25px;
            }
        }
    </style>
</head>

<body>

<main class="dashboard">

    <header class="topbar">
        <div>
            <h1>Jelentkezések kezelése</h1>

            <p>
                Bejelentkezve:
                <?= e($_SESSION['admin_username'] ?? 'admin') ?>
            </p>
        </div>

        <a class="logout" href="logout.php">
            Kijelentkezés
        </a>
    </header>

    <!-- Statisztikák -->
    <section class="stats">

        <div class="stat">
            <span>Összes jelentkező</span>
            <strong><?= $total ?></strong>
        </div>

        <div class="stat">
            <span>Végzősök</span>
            <strong><?= $graduates ?></strong>
        </div>

        <div class="stat">
            <span>Kollégisták</span>
            <strong><?= $boarders ?></strong>
        </div>

        <div class="stat">
            <span>Bejárósok</span>
            <strong><?= $commuters ?></strong>
        </div>

    </section>

    <!-- Szakok szerinti statisztika -->
    <h2>Jelentkezők szakonként</h2>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Szak neve</th>
                    <th>Jelentkezők száma</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($tradeStats as $stat): ?>
                    <tr>
                        <td>
                            <?= e($stat['interested_trade']) ?>
                        </td>

                        <td>
                            <?= (int) $stat['total'] ?> fő
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (empty($tradeStats)): ?>
                    <tr>
                        <td colspan="2">
                            Még nincs jelentkező.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Összes regisztráció -->
    <h2>Összes regisztráció</h2>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Teljes név</th>
                    <th>Korábbi iskola</th>
                    <th>Érdekelt szak</th>
                    <th>Elhelyezés</th>
                    <th>Végzős?</th>
                    <th>Beküldés ideje</th>
                    <th>Művelet</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($registrations as $r): ?>
                    <tr>
                        <td>
                            <?= (int) $r['id'] ?>
                        </td>

                        <td>
                            <?= e($r['full_name']) ?>
                        </td>

                        <td>
                            <?= e($r['school_name']) ?>
                        </td>

                        <td>
                            <?= e($r['interested_trade']) ?>
                        </td>

                        <td>
                            <?= e($r['accommodation']) ?>
                        </td>

                        <td>
                            <?= (int) $r['is_graduating'] === 1
                                ? 'Igen'
                                : 'Nem' ?>
                        </td>

                        <td>
                            <?= e($r['created_at']) ?>
                        </td>

                        <td>
                            <form
                                action="delete.php"
                                method="POST"
                                onsubmit="return confirm('Biztosan törlöd ezt a regisztrációt?');"
                            >
                                <input
                                    type="hidden"
                                    name="csrf_token"
                                    value="<?= e($_SESSION['admin_csrf']) ?>"
                                >

                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= (int) $r['id'] ?>"
                                >

                                <button
                                    type="submit"
                                    class="delete-button"
                                >
                                    Törlés
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (empty($registrations)): ?>
                    <tr>
                        <td colspan="8">
                            Még nincs regisztráció.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</main>

</body>
</html>