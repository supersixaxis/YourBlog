<?php
session_start();

// Vérifier si l'utilisateur est administrateur
if (!isset($_SESSION['user']) || $_SESSION['user']['status'] !== 'admin') {
    header("Location: http://localhost/phpcloudcampus/projet/connexion.php");
    exit;
}

$articles_file = 'articles.txt';

$articles = [];

// Charger les articles existants
if (file_exists($articles_file)) {
    $articles = json_decode(file_get_contents($articles_file), true);
}

// Valider un article
if (isset($_POST['validate_article'])) {
    $article_index = $_POST['article_index'];
    if (isset($articles[$article_index])) {
        $articles[$article_index]['status'] = 'valide';
        file_put_contents($articles_file, json_encode($articles));
        header("Location: gestion_article.php");
        exit;
    }
}
if (isset($_POST['cancel_article'])) {
    $article_index = $_POST['article_index'];
    unset($articles[$article_index]);
    file_put_contents($articles_file, json_encode(array_values($articles)));
    }

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Articles</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <?php include('../header.php'); ?>
    <div class="container">
        <h1 class="mt-5">Articles en attente</h1>
        <div class="mt-5">
            <?php if (empty($articles)): ?>
                <p>Aucun article trouvé.</p>
            <?php else: ?>
                <ul class="list-group">
                    <?php foreach ($articles as $index => $article): ?>
                        <?php if ($article['status'] === 'en attente'): ?>
                            <li class="list-group-item">
                                <h5><?php echo htmlspecialchars($article['title']); ?></h5>
                                <p>Catégorie: <?php echo htmlspecialchars($article['category']); ?></p>
                                <p>Auteur: <?php echo htmlspecialchars($article['author']); ?></p>
                                <p>Statut: <?php echo htmlspecialchars($article['status']); ?></p>
                                <form method="post" action="gestion_article.php" style="display: inline-block;">
                                    <input type="hidden" name="article_index" value="<?php echo $index; ?>">
                                    <button type="submit" name="validate_article" class="btn btn-success">Valider</button>
                                    <button type="submit" name="cancel_article" class="btn btn-danger">Refuser</button>
                                </form>
                                <button type="button" class="btn btn-info" data-toggle="modal" data-target="#articleModal<?php echo $index; ?>">Consulter</button>
                            </li>

                            <!-- Modal -->
                            <div class="modal fade" id="articleModal<?php echo $index; ?>" tabindex="-1" role="dialog" aria-labelledby="articleModalLabel<?php echo $index; ?>" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="articleModalLabel<?php echo $index; ?>"><?php echo htmlspecialchars($article['title']); ?></h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <p><?php echo nl2br(htmlspecialchars($article['content'])); ?></p>
                                            <p><strong>Catégorie:</strong> <?php echo htmlspecialchars($article['category']); ?></p>
                                            <p><strong>Mots clés:</strong> <?php echo implode(', ', array_map('htmlspecialchars', $article['keywords'])); ?></p>
                                            <p><strong>Auteur:</strong> <?php echo htmlspecialchars($article['author']); ?></p>
                                            <p><strong>Statut:</strong> <?php echo htmlspecialchars($article['status']); ?></p>
                                            <?php if (!empty($article['main_image'])): ?>
                                                <p><img src="<?php echo htmlspecialchars($article['main_image']); ?>" alt="Image principale" class="img-fluid"></p>
                                            <?php endif; ?>
                                            <?php if (!empty($article['gallery_images'])): ?>
                                                <div class="gallery">
                                                    <?php foreach ($article['gallery_images'] as $gallery_image): ?>
                                                        <img src="<?php echo htmlspecialchars($gallery_image); ?>" alt="Image de galerie" class="img-fluid">
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
