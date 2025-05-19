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
} catch (PDOException $e) {
    die("Erreur DB : " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("
        INSERT INTO planner_events (user, date, type, titre, prix, rappel, categorie, valide)
        VALUES (?, ?, ?, ?, ?, ?, ?, 1)
    ");

    $stmt->execute([
        $_SESSION['user'],
        $_POST['date'],
        $_POST['type'],
        $_POST['titre'],
        $_POST['prix'],
        isset($_POST['rappel']) ? 1 : 0,
        $_POST['categorie']
    ]);

    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Nouvel événement</title>
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

        input[type="date"],
        input[type="text"],
        input[type="number"],
        select {
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

        input[type="checkbox"] {
            transform: scale(1.2);
            margin-right: 10px;
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
                border-radius: 15px;
            }

            input, select, button {
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

<h1>➕ Ajouter un événement</h1>

<form method="post">
    <label for="date">Date :</label>
    <div class="input-wrapper" >
        <input type="date" name="date" id="date"  required>
    </div>

    <label for="type">Type :</label>
    <select name="type" id="type" required>
        <option value="">-- Sélectionner --</option>
        <option value="abonnement">Abonnement</option>
        <option value="loyer">Loyer</option>
        <option value="echeance credit">Échéance crédit</option>
    </select>

    <label for="categorie">Catégorie :</label>
    <select name="categorie" id="categorie" required>
        <option value="vie courante">Vie courante</option>
        <option value="travail">Travail</option>
        <option value="amusement">Amusement</option>
        <option value="investissement">Investissement</option>
    </select>

    <label for="titre">Titre :</label>
    <input type="text" name="titre" id="titre" required>

    <label for="prix">Prix :</label>
    <input type="number" step="0.01" name="prix" id="prix">

    <label>
        <input type="checkbox" name="rappel"> Activer un rappel
    </label>

    <button type="submit">✅ Ajouter l'événement</button>
</form>

<p><a href="index.php">← Retour au calendrier</a></p>

</body>
</html>

