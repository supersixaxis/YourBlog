<?php
session_start();

// Vérifier si l'utilisateur est administrateur
if (!isset($_SESSION['user']) || $_SESSION['user']['status'] !== 'admin') {
    header("Location: /api/connexion.php");
    exit;
}

$articles_file = 'articles.txt';

$articles = [];

if (file_exists($articles_file)) {
    $articles = json_decode(file_get_contents($articles_file), true);
}

// Valider un commentaire
if (isset($_POST['validate_comment'])) {
    $article_index = (int)$_POST['article_index'];
    $comment_index = (int)$_POST['comment_index'];
    if (isset($articles[$article_index]['comments'][$comment_index])) {
        $articles[$article_index]['comments'][$comment_index]['status'] = 'valide';
        file_put_contents($articles_file, json_encode($articles));
        header("Location: gestion_commentaires.php");
        exit;
    }
}

// Supprimer un commentaire
if (isset($_POST['delete_comment'])) {
    $article_index = (int)$_POST['article_index'];
    $comment_index = (int)$_POST['delete_comment'];
    if (isset($articles[$article_index]['comments'][$comment_index])) {
        array_splice($articles[$article_index]['comments'], $comment_index, 1);
        file_put_contents($articles_file, json_encode($articles));
        header("Location: gestion_commentaires.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Commentaires</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <?php include('../header.php'); ?>
    <div class="container">
        <h1 class="mt-5">Commentaires en attente</h1>
        <div class="mt-5">
            <?php foreach ($articles as $article_index => $article): ?>
                <?php if (!empty($article['comments'])): ?>
                    <ul class="list-unstyled">
                        <?php foreach ($article['comments'] as $comment_index => $comment): ?>
                            <?php if ($comment['status'] === 'en attente'): ?>
                                <h3><a href="../view_article.php?id=<?php echo $article_index; ?>"><?php echo htmlspecialchars($article['title']); ?></a></h3>
                                <li class="media mb-3">
                                    <div class="media-body">
                                        <h5 class="mt-0 mb-1">
                                            <a href="../profil_user.php?pseudo=<?php echo htmlspecialchars($comment['pseudo']); ?>">
                                                <?php echo htmlspecialchars($comment['pseudo']); ?>
                                            </a>
                                        </h5>
                                        <?php echo htmlspecialchars($comment['content']); ?>
                                        <p><small class="text-muted">Note: <?php echo $comment['rating']; ?>/5</small></p>
                                        <p><small class="text-muted">Posté le: <?php echo $comment['date']; ?></small></p>
                                        <form action="gestion_commentaires.php" method="post" style="display:inline;">
                                            <input type="hidden" name="article_index" value="<?php echo $article_index; ?>">
                                            <input type="hidden" name="comment_index" value="<?php echo $comment_index; ?>">
                                            <button type="submit" name="validate_comment" class="btn btn-success btn-sm">Valider</button>
                                        </form>
                                        <form action="gestion_commentaires.php" method="post" style="display:inline;">
                                            <input type="hidden" name="article_index" value="<?php echo $article_index; ?>">
                                            <input type="hidden" name="delete_comment" value="<?php echo $comment_index; ?>">
                                            <button type="submit" name="delete_comment" class="btn btn-danger btn-sm">Supprimer</button>
                                        </form>
                                    </div>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
