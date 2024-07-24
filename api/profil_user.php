<?php
session_start();

$users_file = 'users.txt';
$articles_file = 'admin/articles.txt';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    header("Location: /connexion");
    exit;
}

$user = $_SESSION['user'];
$users = [];
$articles = [];

// Charger les utilisateurs existants
if (file_exists($users_file)) {
    $users = json_decode(file_get_contents($users_file), true);
}

// Vérifier si un utilisateur est spécifié dans l'URL
$user_found = null;
if (isset($_GET['pseudo'])) {
    $pseudo = $_GET['pseudo'];
    foreach ($users as $u) {
        if ($u['pseudo'] === $pseudo) {
            $user_found = $u;
            break;
        }
    }
}

if (!$user_found) {
    echo "Utilisateur non trouvé.";
    exit;
}

// Charger les articles existants
if (file_exists($articles_file)) {
    $articles = json_decode(file_get_contents($articles_file), true);
}

// Initialiser le tableau des commentaires de l'utilisateur
$user_comments = [];

// Collecter les commentaires de l'utilisateur
foreach ($articles as $article_index => $article) {
    if (isset($article['comments'])) {
        foreach ($article['comments'] as $comment) {
            if ($comment['pseudo'] === $pseudo) {
                // Ajouter les détails de l'article au commentaire
                $comment['article_title'] = $article['title'];
                $comment['article_index'] = $article_index;
                $user_comments[] = $comment;
            }
        }
    }
}

// Compter le nombre de commentaires de l'utilisateur
$num_comments = count($user_comments);

// Calculer la moyenne des notes attribuées par l'utilisateur
$average_rating = calculate_average_rating($user_comments);

// Fonction pour calculer la moyenne des notes
function calculate_average_rating($comments) {
    if (empty($comments)) {
        return null;
    }
    $total_rating = 0;
    foreach ($comments as $comment) {
        $total_rating += $comment['rating'];
    }
    return $total_rating / count($comments);
}

// Récupérer les deux derniers commentaires de l'utilisateur
$last_two_comments = array_slice(array_reverse($user_comments), 0, 2);

// Récupérer les deux derniers articles pour les créateurs/administrateurs
$last_two_articles = [];
if ($user_found['status'] === 'createur' || $user_found['status'] === 'admin') {
    $last_two_articles = array_slice(array_reverse($articles), 0, 2);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil de <?php echo htmlspecialchars($user_found['pseudo']); ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

</head>
<body>
    <?php include 'header.php'; ?>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h2 class="mt-5 mb-3">Profil de <?php echo htmlspecialchars($user_found['pseudo']); ?></h2>
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($user_found['pseudo']); ?></h5>
                        <img src="<?php echo htmlspecialchars($user_found['avatar']); ?>" alt="User Avatar" class="img-fluid mb-3">
                        <p class="card-text"><strong>Email:</strong> <?php echo htmlspecialchars($user_found['email']); ?></p>
                        <p class="card-text"><strong>Adresse:</strong> <?php echo htmlspecialchars($user_found['address']); ?></p>
                        <p class="card-text"><strong>Code Postal:</strong> <?php echo htmlspecialchars($user_found['cp']); ?></p>
                        <p class="card-text"><strong>Ville:</strong> <?php echo htmlspecialchars($user_found['city']); ?></p>
                        <p class="card-text"><strong>Status:</strong> <?php echo htmlspecialchars($user_found['status']); ?></p>
                        <div class="card mt-3">
                            <div class="card-body">
                                <form action="profil_user" method="post">
                                    <div class="form-group">
                                        <label for="biographie"><strong>Modifier la biographie:</strong></label>
                                        <textarea class="form-control" id="biographie" name="biographie" rows="4"><?php echo htmlspecialchars($user_found['biographie'] ?? ''); ?></textarea>
                                    </div>
                                    <button type="submit" name="update_biographie" class="btn btn-primary">Enregistrer</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section des statistiques -->
                <div class="card mt-3">
                    <div class="card-body">
                        <h5 class="card-title">Statistiques</h5>
                        <ul class="list-group">
                            <li class="list-group-item">
                                <?php if (!empty($user_comments)): ?>
                                    <p><strong>Nombre de commentaires:</strong> <?php echo $num_comments; ?></p>
                                    <p><strong>Note moyenne attribuée:</strong> <?php echo number_format($average_rating, 1); ?>/5</p>
                                <?php else: ?>
                                    <p>Aucun commentaire publié.</p>
                                <?php endif; ?>
                            </li>
                            <?php if ($user_found['status'] === 'createur' || $user_found['status'] === 'admin'): ?>
                                <li class="list-group-item">
                                    <p><strong>Nombre d'articles créés:</strong> <?php echo count($articles); ?></p>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>

                <!-- Section des deux derniers commentaires -->
                <?php if (!empty($last_two_comments)): ?>
                    <div class="card mt-3">
                        <div class="card-body">
                            <h5 class="card-title">Derniers commentaires</h5>
                            <ul class="list-group">
                                <?php foreach ($last_two_comments as $comment): ?>
                                    <li class="list-group-item">
                                        <p><strong>Commentaire:</strong> <?php echo htmlspecialchars($comment['content']); ?></p>
                                        <p><strong>Note:</strong> <?php echo htmlspecialchars($comment['rating']); ?>/5</p>
                                        <small class="text-muted">Article: <a href="view_article?id=<?php echo $comment['article_index']; ?>"><?php echo htmlspecialchars($comment['article_title']); ?></a></small>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Section des deux derniers articles pour les createurs/admins -->
                <?php if (!empty($last_two_articles)): ?>
                    <div class="card mt-3">
                        <div class="card-body">
                            <h5 class="card-title">Derniers articles publiés</h5>
                            <ul class="list-group">
                                <?php foreach ($last_two_articles as $article): ?>
                                    <li class="list-group-item">
                                        <h6><?php echo htmlspecialchars($article['title']); ?></h6>
                                        <p><?php echo htmlspecialchars($article['content']); ?></p>
                                        <small class="text-muted">Catégorie: <?php echo htmlspecialchars($article['category']); ?></small>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
