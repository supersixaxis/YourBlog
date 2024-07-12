<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: http://localhost/phpcloudcampus/projet/connexion.php");
    exit;
}

$user = $_SESSION['user'];
$users_file = 'users.txt'; 
$users = [];

// Charger les utilisateurs existants
if (file_exists($users_file)) {
    $users = json_decode(file_get_contents($users_file), true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_biographie'])) {
    $biographie = trim($_POST['biographie']);
    
    // Mettre à jour la biographie de l'utilisateur
    foreach ($users as &$stored_user) {
        if ($stored_user['email'] === $user['email']) {
            $stored_user['biographie'] = $biographie;
            break;
        }
    }

    // Sauvegarder les utilisateurs mis à jour dans le fichier
    file_put_contents($users_file, json_encode($users));

    // Mettre à jour la session utilisateur
    $_SESSION['user']['biographie'] = $biographie;
    
    // Redirection pour éviter la soumission multiple du formulaire
    header("Location: profil.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<?php include 'header.php'; ?>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <h2 class="mt-5">Profil</h2>
                <div class="card mt-3">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($user['pseudo']); ?></h5>
                        <img src="<?php echo htmlspecialchars($user['avatar']); ?>" alt="User Avatar" class="img-fluid">
                        <p class="card-text"><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                        <p class="card-text"><strong>Adresse:</strong> <?php echo htmlspecialchars($user['address']); ?></p>
                        <p class="card-text"><strong>Code Postal:</strong> <?php echo htmlspecialchars($user['cp']); ?></p>
                        <p class="card-text"><strong>Ville:</strong> <?php echo htmlspecialchars($user['city']); ?></p>
                        <p class="card-text"><strong>Status:</strong> <?php echo htmlspecialchars($user['status']); ?></p>
                        <form action="profil.php" method="post">
                            <div class="form-group">
                                <label for="biographie"><strong>Biographie:</strong></label>
                                <textarea class="form-control" id="biographie" name="biographie" rows="4"><?php echo htmlspecialchars($user['biographie'] ?? ''); ?></textarea>
                            </div>
                            <button type="submit" name="update_biographie" class="btn btn-primary">Enregistrer</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
