<?php
require_once 'session.php';
require_once 'config.php';
secure_session_start();

if (!is_logged_in()) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['event_id'])) {
    $eventId = (int) $_POST['event_id'];

    try {
        $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        // Vérifie que l'événement appartient bien à l'utilisateur
        $check = $pdo->prepare("SELECT id FROM planner_events WHERE id = ? AND user = ?");
        $check->execute([$eventId, $_SESSION['user']]);

        if ($check->fetch()) {
            $update = $pdo->prepare("UPDATE planner_events SET valide = 0 WHERE id = ?");
            $update->execute([$eventId]);
        }
    } catch (PDOException $e) {
        // log ou ignorer
    }
}

header("Location: index.php");
exit;
