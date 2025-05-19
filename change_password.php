<?php
require_once 'session.php';
require_once 'config.php';
secure_session_start();

if (!is_logged_in()) {
    header("Location: login.php");
    exit;
}

$error = '';
$success = '';

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Erreur DB : " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = $_POST['old_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (strlen($new) < 6) {
        $error = "Le nouveau mot de passe est trop court (min. 6 caractères).";
    } elseif ($new !== $confirm) {
        $error = "Les mots de passe ne correspondent pas.";
    } else {
        $stmt = $pdo->prepare("SELECT password FROM planner_users WHERE username = ?");
        $stmt->execute([$_SESSION['user']]);
        $user = $stmt->fetch();

        if ($user && password_verify($old, $user['password'])) {
            $newHash = password_hash($new, PASSWORD_DEFAULT);
            $update = $pdo->prepare("UPDATE planner_users SET password = ? WHERE username = ?");
            $update->execute([$newHash, $_SESSION['user']]);
            $success = "Mot de passe mis à jour avec succès.";
        } else {
            $error = "Ancien mot de passe incorrect.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Changer le mot de passe</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        :root {
            --bg: #e0e5ec;
            --shadow-light: #ffffff;
            --shadow-dark: #bec4d0;
            --text: #333;
            --accent: #3498db;
        }

        body {
            font-family: "Segoe UI", sans-serif;
            background: var(--bg);
            color: var(--text);
            margin: 0;
            padding: 20px;
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
        }

        .status {
            max-width: 500px;
            margin: 0 auto 20px;
            text-align: center;
            padding: 10px;
            border-radius: 12px;
        }

        .status.error {
            background: #ffdddd;
            color: #a33;
        }

        .status.success {
            background: #ddffdd;
            color: #2d7;
        }

        form {
            max-width: 500px;
            margin: auto;
            padding: 30px;
            background: var(--bg);
            border-radius: 20px;
            box-shadow: 8px 8px 16px var(--shadow-dark),
                        -8px -8px 16px var(--shadow-light);
        }

        label {
            display: block;
            margin: 15px 0 5px;
            font-weight: bold;
        }

        input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: none;
            border-radius: 12px;
            background: var(--bg);
            box-shadow: inset 3px 3px 6px var(--shadow-dark),
                        inset -3px -3px 6px var(--shadow-light);
            font-size: 16px;
        }

        button {
            width: 100%;
            padding: 14px;
            background: var(--bg);
            border: none;
            border-radius: 12px;
            box-shadow: 4px 4px 8px var(--shadow-dark),
                        -4px -4px 8px var(--shadow-light);
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.2s ease;
        }

        button:hover {
            box-shadow: inset 4px 4px 8px var(--shadow-dark),
                        inset -4px -4px 8px var(--shadow-light);
        }

        p {
            text-align: center;
            margin-top: 25px;
        }

        a {
            display: inline-block;
            padding: 10px 20px;
            background: var(--bg);
            border-radius: 12px;
            text-decoration: none;
            color: var(--accent);
            font-weight: bold;
            box-shadow: 4px 4px 8px var(--shadow-dark),
                        -4px -4px 8px var(--shadow-light);
            transition: 0.2s ease;
        }

        a:hover {
            box-shadow: inset 4px 4px 8px var(--shadow-dark),
                        inset -4px -4px 8px var(--shadow-light);
        }

        @media (max-width: 600px) {
            form {
                padding: 20px;
            }

            input, button {
                font-size: 15px;
            }

            h1 {
                font-size: 22px;
            }
        }
        *, *::before, *::after {
    box-sizing: border-box;
}
    </style>
</head>
<body>

<h1>🔒 Changer le mot de passe</h1>

<?php if ($error): ?>
    <div class="status error"><?= htmlspecialchars($error) ?></div>
<?php elseif ($success): ?>
    <div class="status success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<form method="post">
    <label for="old_password">Ancien mot de passe :</label>
    <input type="password" name="old_password" id="old_password" required>

    <label for="new_password">Nouveau mot de passe :</label>
    <input type="password" name="new_password" id="new_password" required>

    <label for="confirm_password">Confirmer le nouveau mot de passe :</label>
    <input type="password" name="confirm_password" id="confirm_password" required>

    <button type="submit">✅ Mettre à jour</button>
</form>

<p><a href="index.php">← Retour à l'accueil</a></p>

</body>
</html>

