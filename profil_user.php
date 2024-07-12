<?php
session_start();

// Charger les utilisateurs existants
$users_file = 'users.txt';
$users = [];

if (file_exists($users_file)) {
    $users = json_decode(file_get_contents($users_file), true);
}

// Vérifier si un utilisateur est passé en paramètre
$user_found = null;
if (isset($_GET['pseudo'])) {
    $pseudo = $_GET['pseudo'];
    foreach ($users as $user) {
        if ($user['pseudo'] === $pseudo) {
            $user_found = $user;
            break;
        }
    }
}

if (!$user_found) {
    echo "Utilisateur non trouvé.";
    exit;
}

// Charger les articles et les commentaires
$articles_file = 'admin/articles.txt';
$articles = [];

if (file_exists($articles_file)) {
    $articles = json_decode(file_get_contents($articles_file), true);
}

// Collecter les commentaires de l'utilisateur
$user_comments = [];
foreach ($articles as $article_index => $article) {
    if (isset($article['comments'])) {
        foreach ($article['comments'] as $comment) {
            if ($comment['pseudo'] === $pseudo) {
                $comment['article_title'] = $article['title'];
                $comment['article_index'] = $article_index;
                $user_comments[] = $comment;
            }
        }
    }
}

$comment_count = count($user_comments);
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
    <?php include('header.php'); ?>
    <div class="container">
        <h1 class="mt-5">Profil de <?php echo htmlspecialchars($user_found['pseudo']); ?></h1>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Pseudo: <?php echo htmlspecialchars($user_found['pseudo']); ?></h5>
                <p class="card-text">Email: <?php echo htmlspecialchars($user_found['email']); ?></p>
                <p class="card-text">Adresse: <?php echo htmlspecialchars($user_found['address']); ?></p>
                <p class="card-text">Code Postal: <?php echo htmlspecialchars($user_found['cp']); ?></p>
                <p class="card-text">Ville: <?php echo htmlspecialchars($user_found['city']); ?></p>
                <p class="card-text">Statut: <?php echo htmlspecialchars($user_found['status']); ?></p>
                <p class="card-text">Biographie: <?php echo htmlspecialchars($user_found['biographie']); ?></p>
                <?php if (!empty($user_found['avatar'])): ?>
                    <img src="<?php echo htmlspecialchars($user_found['avatar']); ?>" class="img-thumbnail" alt="Avatar">
                <?php endif; ?>
                <p class="card-text">Nombre de commentaires laissés: <?php echo $comment_count; ?></p>
            </div>
        </div>

        <?php if ($comment_count > 0): ?>
            <h3 class="mt-5">Commentaires postés de l'utilisateur</h3>
            <ul class="list-unstyled">
                <?php foreach ($user_comments as $comment): ?>
                    <li class="media mb-3">
                        <div class="media-body">
                            <h5 class="mt-0 mb-1">
                                <a href="view_article.php?id=<?php echo $comment['article_index']; ?>">
                                    <?php echo htmlspecialchars($comment['article_title']); ?>
                                </a>
                            </h5>
                            <?php echo htmlspecialchars($comment['content']); ?>
                            <p><small class="text-muted">Note: <?php echo $comment['rating']; ?>/5</small></p>
                            <p><small class="text-muted">Posté le: <?php echo $comment['date']; ?></small></p>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</body>
</html>
