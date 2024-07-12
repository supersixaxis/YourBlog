<?php
session_start();

// Vérifier si l'utilisateur est administrateur
if (!isset($_SESSION['user']) || $_SESSION['user']['status'] !== 'admin') {
    header("Location: http://localhost/YourBlog/connexion.php");
    exit;
}

$users_file = '../users.txt'; 

$users = [];

// Charger les utilisateurs existants
if (file_exists($users_file)) {
    $users = json_decode(file_get_contents($users_file), true);
}

// Mettre à jour le statut de l'utilisateur
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_index'], $_POST['new_status'])) {
    $user_index = intval($_POST['user_index']);
    $new_status = $_POST['new_status'];

    if (isset($users[$user_index])) {
        $users[$user_index]['status'] = $new_status;
        file_put_contents($users_file, json_encode($users));
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des utilisateurs</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <?php include('../header.php'); ?>
    <div class="container">
        <h1 class="mt-5">Gestion des utilisateurs</h1>
        
        <!-- Gestion des utilisateurs -->
        <div class="mt-5">
            <?php if (empty($users)): ?>
                <p>Aucun utilisateur trouvé.</p>
            <?php else: ?>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Statut</th>
                            <th>Date d'inscription</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $index => $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($index); ?></td>
                                <td><?php echo htmlspecialchars($user['pseudo']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <form action="gestion_utilisateur.php" method="post">
                                        <input type="hidden" name="user_index" value="<?php echo htmlspecialchars($index); ?>">
                                        <select name="new_status" onchange="this.form.submit()" class="form-control">
                                            <option value="user" <?php echo ($user['status'] === 'user') ? 'selected' : ''; ?>>Utilisateur</option>
                                            <option value="createur" <?php echo ($user['status'] === 'createur') ? 'selected' : ''; ?>>Créateur</option>
                                            <option value="admin" <?php echo ($user['status'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                                        </select>
                                    </form>
                                </td>
                                <td><?php echo htmlspecialchars($user['inscription']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>
