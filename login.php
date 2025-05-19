<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'session.php';
require_once 'config.php';
secure_session_start();

$error = '';

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

    if ($username && $password) {
        $stmt = $pdo->prepare("SELECT * FROM planner_users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user['username'];
            $_SESSION['role'] = $user['role'] ?? 'user';
            header("Location: index.php");
            exit;
        } else {
            $error = "Identifiants incorrects.";
        }
    } else {
        $error = "Tous les champs sont requis.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
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
        font-size: 28px;
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

    form {
    max-width: 500px;
    margin: auto;
    padding: 30px;
    background: var(--bg);
    border-radius: 20px;
    box-shadow: 8px 8px 16px var(--shadow-dark),
                -8px -8px 16px var(--shadow-light);
    display: flex;
    flex-direction: column;
    align-items: stretch; /* permet aux champs de remplir toute la largeur */
    gap: 10px; /* espace entre les champs */
}

label {
    display: flex;
    flex-direction: column;
    font-weight: bold;
    margin: 0;
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
        font-size: 16px;
    }

    a {
        color: var(--accent);
        font-weight: bold;
        text-decoration: none;
    }

    a:hover {
        text-decoration: underline;
    }

    @media (max-width: 600px) {
        body {
            padding: 10px;
        }

        form {
            padding: 20px;
            border-radius: 15px;
        }

        input, button {
            font-size: 15px;
        }

        h1 {
            font-size: 22px;
        }

        p {
            font-size: 14px;
        }
    }
    *, *::before, *::after {
    box-sizing: border-box;
}
</style>

</head>
<body>

<h1>🔐 Connexion</h1>

<?php if ($error): ?>
    <div class="status error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="post" action="login.php">
    <label for="username">Nom d'utilisateur :
        <input type="text" name="username" id="username" required>
    </label>

    <label for="password">Mot de passe :
        <input type="password" name="password" id="password" required>
    </label>

    <button type="submit">✅ Se connecter</button>
</form>


<p><a href="register.php">Créer un compte</a></p>

</body>
</html>
