<?php
session_start();

// Charger les articles
$articles_file = 'admin/articles.txt';
$articles = [];

if (file_exists($articles_file)) {
    $articles = json_decode(file_get_contents($articles_file), true);
}

// Vérifier si l'ID de l'article est passé en paramètre
$article_index = isset($_GET['id']) ? (int)$_GET['id'] : null;
$article = $articles[$article_index] ?? null;

if (!$article) {
    echo "Article non trouvé.";
    exit;
}

// Ajouter un commentaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user'])) {
    if (isset($_POST['comment_content']) && isset($_POST['rating'])) {
        $comment_content = trim($_POST['comment_content']);
        $rating = (int)$_POST['rating'];

        if ($comment_content && $rating >= 1 && $rating <= 5) {
            $comment = [
                'pseudo' => $_SESSION['user']['pseudo'],
                'content' => $comment_content,
                'rating' => $rating,
                'date' => date('Y-m-d H:i:s'),
                'status' => 'en attente'
            ];

            if (!isset($article['comments'])) {
                $article['comments'] = [];
            }

            $article['comments'][] = $comment;
            $articles[$article_index] = $article;
            file_put_contents($articles_file, json_encode($articles));
        }
    }

    // Supprimer un commentaire (si admin)
    if (isset($_POST['delete_comment']) && isset($_SESSION['user']['status']) && $_SESSION['user']['status'] === 'admin') {
        $comment_index = (int)$_POST['delete_comment'];
        if (isset($article['comments'][$comment_index])) {
            array_splice($article['comments'], $comment_index, 1);
            $articles[$article_index] = $article;
            file_put_contents($articles_file, json_encode($articles));
        }
    }
}

// Fonction pour calculer la moyenne des notes
function calculate_average_rating($comments) {
    if (empty($comments)) {
        return null;
    }
    $total_rating = 0;
    $rating_count = 0;
    foreach ($comments as $comment) {
        if (isset($comment['rating']) && $comment['status'] === 'valide') {
            $total_rating += $comment['rating'];
            $rating_count++;
        }
    }
    return $rating_count ? $total_rating / $rating_count : null;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($article['title']); ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <?php include('header.php'); ?>
    <div class="container">
        <h1 class="mt-5"><?php echo htmlspecialchars($article['title']); ?></h1>
        <p><?php echo htmlspecialchars($article['content']); ?></p>
        <p><strong>Catégorie:</strong> <?php echo htmlspecialchars($article['category']); ?></p>
        <p><strong>Mots Clés:</strong> <?php echo implode(', ', $article['keywords']); ?></p>
        <p><strong>Auteur:</strong> <a href="profil_user.php?pseudo=<?php echo htmlspecialchars($article['author']); ?>"><?php echo htmlspecialchars($article['author']); ?></a></p>

        <?php
        $average_rating = calculate_average_rating($article['comments'] ?? []);
        if ($average_rating !== null): ?>
            <p><strong>Note Moyenne:</strong> <?php echo number_format($average_rating, 1); ?>/5</p>
        <?php endif; ?>

        <!-- Formulaire pour ajouter un commentaire -->
        <?php if (isset($_SESSION['user'])): ?>
            <h3>Ajouter un commentaire</h3>
            <form action="view_article.php?id=<?php echo $article_index; ?>" method="post">
                <div class="form-group">
                    <label for="comment_content">Commentaire:</label>
                    <textarea class="form-control" id="comment_content" name="comment_content" rows="3" required></textarea>
                </div>
                <div class="form-group">
                    <label for="rating">Note:</label>
                    <select class="form-control" id="rating" name="rating" required>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Envoyer</button>
            </form>
        <?php else: ?>
            <p><a href="connexion.php">Connectez-vous</a> pour ajouter un commentaire.</p>
        <?php endif; ?>

        <!-- Afficher les commentaires approuvés -->
        <?php if (!empty($article['comments'])): ?>
            <h3>Commentaires</h3>
            <ul class="list-unstyled">
                <?php foreach ($article['comments'] as $index => $comment): ?>
                    <?php if ($comment['status'] === 'valide'): ?>
                        <li class="media mb-3">
                            <div class="media-body">
                                <h5 class="mt-0 mb-1">
                                    <a href="profil_user.php?pseudo=<?php echo htmlspecialchars($comment['pseudo']); ?>">
                                        <?php echo htmlspecialchars($comment['pseudo']); ?>
                                    </a>
                                </h5>
                                <?php echo htmlspecialchars($comment['content']); ?>
                                <p><small class="text-muted">Note: <?php echo $comment['rating']; ?>/5</small></p>
                                <p><small class="text-muted">Posté le: <?php echo $comment['date']; ?></small></p>

                                <?php if (isset($_SESSION['user']['status']) && $_SESSION['user']['status'] === 'admin'): ?>
                                    <form action="view_article.php?id=<?php echo $article_index; ?>" method="post" style="display:inline;">
                                        <input type="hidden" name="delete_comment" value="<?php echo $index; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">Supprimer</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</body>
</html>
