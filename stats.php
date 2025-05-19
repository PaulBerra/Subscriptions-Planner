<?php
require_once 'session.php';
require_once 'config.php';
secure_session_start();

if (!is_logged_in()) {
    header("Location: login.php");
    exit;
}

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    $username = $_SESSION['user'];

    // Total général
    $stmt = $pdo->prepare("SELECT SUM(prix) AS total FROM planner_events WHERE user = ? AND valide = 1");
    $stmt->execute([$username]);
    $total = $stmt->fetchColumn();

    // Total par catégorie
    $stmt = $pdo->prepare("SELECT categorie, SUM(prix) AS total FROM planner_events WHERE user = ? AND valide = 1 GROUP BY categorie");
    $stmt->execute([$username]);
    $byCategorie = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    // Total par mois (YYYY-MM)
    $stmt = $pdo->prepare("
    SELECT DATE_FORMAT(date, '%Y-%m') AS mois, SUM(prix) AS total
    FROM planner_events
    WHERE user = ? AND valide = 1
    GROUP BY mois
    ORDER BY mois ASC
    ");
    $stmt->execute([$username]);
    $byMonth = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

} catch (PDOException $e) {
    die("Erreur DB : " . $e->getMessage());
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Statistiques</title>
    <script src="assets/js/chart.min.js"></script>
    <style>
    body {
        font-family: "Segoe UI", sans-serif;
        background: #e0e5ec;
        color: #333;
        margin: 0;
        padding: 20px;
    }

    h1, h2 {
        text-align: center;
    }

    a {
        color: #3498db;
        text-decoration: none;
        font-weight: bold;
    }

    p, ul {
        text-align: center;
    }

    .box {
        max-width: 600px;
        margin: 20px auto;
        padding: 20px;
        background: #e0e5ec;
        border-radius: 20px;
        box-shadow: 8px 8px 16px #bec4d0,
                    -8px -8px 16px #ffffff;
    }

    ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    li {
        padding: 10px;
        margin: 6px auto;
        background: #e0e5ec;
        border-radius: 12px;
        width: 90%;
        box-shadow: inset 3px 3px 6px #bec4d0,
                    inset -3px -3px 6px #ffffff;
    }

    canvas {
        display: block;
        margin: 30px auto;
        width: 100%;
        max-width: 500px;
        height: auto;
        background: #e0e5ec;
        border-radius: 20px;
        box-shadow: inset 8px 8px 16px #bec4d0,
                    inset -8px -8px 16px #ffffff;
    }

    .links {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 15px;
        margin-bottom: 20px;
    }

    .links a {
        padding: 10px 20px;
        background: #e0e5ec;
        border-radius: 12px;
        box-shadow: 4px 4px 8px #bec4d0,
                    -4px -4px 8px #ffffff;
        transition: 0.2s ease;
    }

    .links a:hover {
        box-shadow: inset 4px 4px 8px #bec4d0,
                    inset -4px -4px 8px #ffffff;
    }

    @media (max-width: 600px) {
        h1 {
            font-size: 22px;
        }

        h2 {
            font-size: 18px;
        }

        li {
            font-size: 15px;
        }

        .links a {
            font-size: 14px;
            padding: 8px 16px;
        }
    }
    *, *::before, *::after {
    box-sizing: border-box;
}
</style>

</head>
<body>

<h1>📊 Statistiques pour <?= htmlspecialchars($_SESSION['user']) ?></h1>

<div class="links">
    <a href="index.php">← Retour au calendrier</a>
    <a href="logout.php">🚪 Se déconnecter</a>
</div>

<div class="box">
    <h2>💰 Total des dépenses</h2>
    <p style="text-align:center; font-size: 14px; color:#888;">(Seuls les événements actifs sont pris en compte)</p>
    <p><strong><?= number_format($total, 2) ?> €</strong></p>
</div>

<div class="box">
    <h2>📂 Dépenses par catégorie</h2>
    <ul>
        <?php foreach ($byCategorie as $cat => $val): ?>
            <li><?= ucfirst($cat) ?> : <?= number_format($val, 2) ?> €</li>
        <?php endforeach; ?>
    </ul>
</div>

<canvas id="chartCat"></canvas>

<div class="box">
    <h2>📅 Dépenses par mois</h2>
    <ul>
        <?php foreach ($byMonth as $mois => $val): ?>
            <li><?= $mois ?> : <?= number_format($val, 2) ?> €</li>
        <?php endforeach; ?>
    </ul>
</div>

<script>
const ctx = document.getElementById('chartCat').getContext('2d');
const chart = new Chart(ctx, {
    type: 'pie',
    data: {
        labels: <?= json_encode(array_keys($byCategorie)) ?>,
        datasets: [{
            label: 'Répartition par catégorie',
            data: <?= json_encode(array_values($byCategorie)) ?>,
            backgroundColor: ['#36A2EB', '#FF6384', '#FFCE56', '#4BC0C0'],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>

</body>
</html>
