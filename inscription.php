<?php
session_start();

$error_message = '';
$success_message = '';

if (isset($_POST['pseudo'], $_POST['email'], $_POST['password'], $_POST['confirm_password'], $_POST['address'], $_POST['cp'], $_POST['city'])) {
    $pseudo = trim($_POST["pseudo"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $confirm_password = trim($_POST["confirm_password"]);
    $address = trim($_POST["address"]);
    $cp = trim($_POST["cp"]);
    $city = trim($_POST["city"]);
    $status = 'Utilisateur';
    $biographie = 'Veuillez remplir votre biographie';
    $avatar = 'images/default.webp'; // Avatar par défaut
    $date = date('Y-m-d H:i:s');

    // Validation des mots de passe
    if ($password !== $confirm_password) {
        $error_message = "Les mots de passe ne correspondent pas.";
    }

    // Gestion de l'avatar si présent
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $fileType = $_FILES['avatar']['type'];
        if (in_array($fileType, $allowedTypes)) {
            $avatar_name = basename($_FILES['avatar']['name']);
            $upload_dir = 'images/';
            $upload_file = $upload_dir . $avatar_name;
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $upload_file)) {
                $avatar = $upload_file;
            } else {
                $error_message = "Erreur lors du téléchargement de l'avatar.";
            }
        } else {
            $error_message = "Format d'avatar invalide. Seuls les formats jpg, png, gif et webp sont autorisés.";
        }
    }

    // Charger les utilisateurs existants
    $users = [];
    $file = 'users.txt';
    if (file_exists($file)) {
        $users = json_decode(file_get_contents($file), true);
    }

    // Vérifier l'unicité du pseudo et de l'email
    foreach ($users as $user) {
        if ($user['pseudo'] === $pseudo) {
            $error_message = "Ce pseudo est déjà pris.";
        }
        if ($user['email'] === $email) {
            $error_message = "Cet email est déjà enregistré.";
        }
    }

    // Si aucune erreur n'est survenue
    if (empty($error_message)) {
        // Hacher le mot de passe
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Ajouter le nouvel utilisateur au tableau des utilisateurs
        $new_user = [
            'pseudo' => $pseudo,
            'email' => $email,
            'password' => $hashed_password,
            'address' => $address,
            'cp' => $cp,
            'city' => $city,
            'avatar' => $avatar,
            'status' => $status,
            'biographie' => $biographie,
            'inscription' => $date
        ];
        $users[] = $new_user;

        // Enregistrer les utilisateurs dans le fichier
        file_put_contents($file, json_encode($users));

        $success_message = "Inscription réussie !";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <h2 class="mt-5">Inscription</h2>
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>
                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>
                <form action="inscription.php" method="post" enctype="multipart/form-data" class="mt-3">
                    <div class="form-group">
                        <label for="pseudo">Pseudo :</label>
                        <input type="text" class="form-control" id="pseudo" name="pseudo" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email :</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Mot de passe :</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirmer le mot de passe :</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    <div class="form-group">
                        <label for="address">Adresse :</label>
                        <input type="text" class="form-control" id="address" name="address" required>
                    </div>
                    <div class="form-group">
                        <label for="cp">Code postal :</label>
                        <input type="text" class="form-control" id="cp" name="cp" required>
                    </div>
                    <div class="form-group">
                        <label for="city">Ville :</label>
                        <input type="text" class="form-control" id="city" name="city" required>
                    </div>
                    <div class="form-group">
                        <label for="avatar">Avatar (optionnel) :</label>
                        <input type="file" class="form-control" id="avatar" name="avatar">
                    </div>
                    <button type="submit" class="btn btn-primary">S'inscrire</button>
                </form>
                <a href="http://localhost/YourBlog/connexion.php" class="btn btn-link mt-3">Vous avez déjà un compte ? Connectez-vous ici.</a>
            </div>
        </div>
    </div>
</body>
</html>
