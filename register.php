<?php
require_once 'session.php';
require_once 'config.php';
secure_session_start();

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
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (strlen($username) < 3 || strlen($password) < 6) {
        $error = "Nom d'utilisateur ou mot de passe trop court.";
    } elseif ($password !== $confirm) {
        $error = "Les mots de passe ne correspondent pas.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM planner_users WHERE username = ?");
        $stmt->execute([$username]);

        if ($stmt->fetch()) {
            $error = "Nom d'utilisateur déjà pris.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO planner_users (username, password, role) VALUES (?, ?, 'user')");
            $stmt->execute([$username, $hash]);
            $success = "Inscription réussie. <a href='login.php'>Se connecter</a>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription</title>
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

        input[type="text"],
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
            color: var(--accent);
            font-weight: bold;
            text-decoration: none;
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

<h1>📝 Créer un compte</h1>

<?php if ($error): ?>
    <div class="status error"><?= htmlspecialchars($error) ?></div>
<?php elseif ($success): ?>
    <div class="status success"><?= $success ?></div>
<?php endif; ?>

<form method="post">
    <label for="username">Nom d'utilisateur :</label>
    <input type="text" name="username" id="username" required>

    <label for="password">Mot de passe :</label>
    <input type="password" name="password" id="password" required>

    <label for="confirm_password">Confirmer le mot de passe :</label>
    <input type="password" name="confirm_password" id="confirm_password" required>

    <button type="submit">✅ S'inscrire</button>
</form>

<p><a href="login.php">← Déjà inscrit ? Connexion</a></p>

</body>
</html>
