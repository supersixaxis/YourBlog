<?php
session_start();

$error_message = '';

if (isset($_POST['identifier']) && isset($_POST['password'])) {
    $identifier = trim($_POST["identifier"]);
    $password = trim($_POST["password"]);

    // Charger les utilisateurs existants
    $users = [];
    $file = 'users.txt';
    if (file_exists($file)) {
        $users = json_decode(file_get_contents($file), true);
    }

    // Vérifier les informations d'identification
    $user_found = false;
    foreach ($users as $user) {
        if (($user['pseudo'] === $identifier || $user['email'] === $identifier) && password_verify($password, $user['password'])) {
            $user_found = true;
            $_SESSION['user'] = $user;
            break;
        }
    }

    if ($user_found) {
        header("Location: http://localhost/phpcloudcampus/projet/profil.php");
        exit;
    } else {
        $error_message = "Identifiant ou mot de passe incorrect.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <h2 class="mt-5">Connexion</h2>
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger mt-3"><?php echo $error_message; ?></div>
                <?php endif; ?>
                <form action="connexion.php" method="post" class="mt-3">
                    <div class="form-group">
                        <label for="identifier">Pseudo ou Email:</label>
                        <input type="text" class="form-control" id="identifier" name="identifier" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Mot de passe:</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Login</button>
                </form>
                <a href="inscription.php" class="btn btn-link mt-3">Vous n'avez pas de compte ? Inscrivez-vous ici</a>
            </div>
        </div>
    </div>
</body>
</html>
