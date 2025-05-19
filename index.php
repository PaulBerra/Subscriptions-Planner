<?php
require_once 'session.php';
secure_session_start();

if (!is_logged_in()) {
    header("Location: login.php");
    exit;
}

require_once 'config.php';

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    $stmt = $pdo->prepare("SELECT * FROM planner_events WHERE user = ? AND valide = 1 ORDER BY date ASC");
    $stmt->execute([$_SESSION['user']]);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur DB : " . $e->getMessage());
}

// Préparer les événements par date
$eventMap = [];
foreach ($events as $event) {
    $eventMap[$event['date']][] = $event;
}

// Date actuelle
$year = date('Y');
$month = date('m');
$firstDay = date('N', strtotime("$year-$month-01")); // 1 (lundi) à 7 (dimanche)
$daysInMonth = date('t');

$today = date('Y-m-d');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Planning du mois</title>
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

    p {
        text-align: center;
        margin: 10px;
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

    table.calendar {
        border-collapse: collapse;
        width: 100%;
        max-width: 1000px;
        margin: 0 auto;
        background: #e0e5ec;
        border-radius: 20px;
        box-shadow: inset 8px 8px 16px #bec4d0,
                    inset -8px -8px 16px #ffffff;
    }

    table.calendar th,
    table.calendar td {
        width: 14.28%;
        min-width: 100px;
        height: 120px;
        vertical-align: top;
        padding: 10px;
        border-radius: 12px;
        background: #e0e5ec;
        box-shadow: 4px 4px 8px #bec4d0,
                    -4px -4px 8px #ffffff;
        transition: all 0.2s ease;
    }

    table.calendar th {
        background: #d1d9e6;
        font-weight: bold;
        color: #555;
        text-align: center;
    }

    .today {
        background: #ffd369 !important;
        box-shadow: inset 4px 4px 10px #d6b347,
                    inset -4px -4px 10px #fff6b0;
    }

    .event {
        background-color: #d0ebff;
        color: #0366d6;
        margin-top: 6px;
        padding: 6px;
        font-size: 13px;
        border-left: 4px solid #3498db;
        border-radius: 6px;
        box-shadow: inset 2px 2px 4px #b0d0ec,
                    inset -2px -2px 4px #ffffff;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
    }

    @media (max-width: 768px) {
        table.calendar th,
        table.calendar td {
            min-width: 60px;
            height: 90px;
            font-size: 12px;
            padding: 5px;
        }

        .event {
            font-size: 11px;
        }

        .links a {
            font-size: 14px;
            padding: 8px 14px;
        }
    }

    @media (max-width: 480px) {
        .event {
            font-size: 10px;
        }

        table.calendar th,
        table.calendar td {
            min-width: 50px;
            height: 80px;
        }

        h1 {
            font-size: 20px;
        }

        h2 {
            font-size: 18px;
        }
    }
    /* Cache le bouton par défaut */
    .event .delete-form {
    display: none;
    text-align: right;
    margin-top: 5px;
}

/* Affiche le bouton uniquement au survol (desktop) */
.event:hover .delete-form {
    display: block;
}

/* Pour mobile/touch : afficher en toggle via .show-delete */
.event.show-delete .delete-form {
    display: block;
}

.del-btn {
    background: none;
    border: none;
    color: #c00;
    font-size: 14px;
    cursor: pointer;
    padding: 2px 4px;
    border-radius: 6px;
    transition: color 0.2s ease;
}

.del-btn:hover {
    color: #900;
}
*, *::before, *::after {
    box-sizing: border-box;
}

</style>

</head>
<body>

<h1>Bienvenue, <?= htmlspecialchars($_SESSION['user']) ?> !</h1>

<div class="links">
    <a href="add_event.php">➕ Ajouter un événement</a>
    <a href="change_password.php">🔒 Changer mon mot de passe</a>
    <a href="stats.php">📊 Statistiques</a>
    <a href="logout.php">🚪 Se déconnecter</a>
</div>

<h2>📅 Calendrier – <?= date('F Y') ?></h2>
<table class="calendar">
    <tr>
        <th>Lun</th><th>Mar</th><th>Mer</th><th>Jeu</th><th>Ven</th><th>Sam</th><th>Dim</th>
    </tr>
    <tr>
    <?php
    $day = 1;
    $datePointer = 1;
    $cellCount = 0;

    // Cases vides avant le 1er du mois
    for ($i = 1; $i < $firstDay; $i++) {
        echo "<td></td>";
        $cellCount++;
    }

    // Remplissage des jours du mois
    while ($day <= $daysInMonth) {
        $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
        $class = ($date === $today) ? 'today' : '';
        echo "<td class='$class'><strong>$day</strong>";

        if (isset($eventMap[$date])) {
            foreach ($eventMap[$date] as $e) {
                $titre = htmlspecialchars($e['titre']);
                $id = (int)$e['id'];
                echo "
                <div class='event' onclick='this.classList.toggle(\"show-delete\")'>
                    $titre
                    <form method='post' action='delete_event.php' class='delete-form'>
                        <input type='hidden' name='event_id' value='$id'>
                        <button type='submit' class='del-btn' title='Supprimer'>🗑️</button>
                    </form>
                </div>";
            }
            
        }

        echo "</td>";
        $day++;
        $cellCount++;

        // Nouvelle ligne chaque dimanche
        if ($cellCount % 7 === 0 && $day <= $daysInMonth) {
            echo "</tr><tr>";
        }
    }

    // Cases vides après le dernier jour pour compléter la dernière ligne
    while ($cellCount % 7 !== 0) {
        echo "<td></td>";
        $cellCount++;
    }

    echo "</tr>";
    ?>
</table>

</body>
</html>
