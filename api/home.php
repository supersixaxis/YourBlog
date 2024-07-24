<?php
session_start();

// Charger les articles
$articles_file = 'admin/articles.txt';
$articles = [];

if (file_exists($articles_file)) {
    $articles = json_decode(file_get_contents($articles_file), true);
}

// Vérifier si l'utilisateur est administrateur
$is_admin = isset($_SESSION['user']) && $_SESSION['user']['status'] === 'admin';

// Gestion de la suppression et modification des articles
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($is_admin) {
        if (isset($_POST['delete_article'])) {
            $article_index = $_POST['article_index'];
            unset($articles[$article_index]);
            file_put_contents($articles_file, json_encode(array_values($articles)));
        } elseif (isset($_POST['update_article'])) {
            $article_index = $_POST['article_index'];
            $article_title = trim($_POST['article_title']);
            $article_content = trim($_POST['article_content']);
            $category = trim($_POST['category']);
            $article_keywords = explode(',', trim($_POST['keywords']));
            $main_image = $articles[$article_index]['main_image'];
            $gallery_images = $articles[$article_index]['gallery_images'];
            $author = $articles[$article_index]['author'];
            $status = $articles[$article_index]['status']; // conserver le statut actuel

            // Gestion de l'image principale
            if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] == UPLOAD_ERR_OK) {
                $main_image = 'uploads/' . basename($_FILES['main_image']['name']);
                move_uploaded_file($_FILES['main_image']['tmp_name'], $main_image);
            }

            // Gestion de la galerie d'images
            if (isset($_FILES['gallery_images'])) {
                $gallery_images = [];
                foreach ($_FILES['gallery_images']['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES['gallery_images']['error'][$key] == UPLOAD_ERR_OK) {
                        $gallery_image = 'uploads/' . basename($_FILES['gallery_images']['name'][$key]);
                        move_uploaded_file($tmp_name, $gallery_image);
                        $gallery_images[] = $gallery_image;
                    }
                }
            }

            $articles[$article_index] = [
                'title' => $article_title,
                'content' => $article_content,
                'category' => $category,
                'keywords' => $article_keywords,
                'main_image' => $main_image,
                'gallery_images' => $gallery_images,
                'author' => $author,
                'status' => $status // conserver le statut actuel
            ];
            file_put_contents($articles_file, json_encode($articles));
        }
    }
}

// Filtrage des articles
$category_filter = isset($_GET['category']) ? trim($_GET['category']) : '';
$keyword_filter = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';

// Ajouter un filtre pour les articles "valides"
$filtered_articles = array_filter($articles, function ($article) use ($category_filter, $keyword_filter) {
    $category_match = !$category_filter || stripos($article['category'], $category_filter) !== false;
    $keyword_match = !$keyword_filter || in_array($keyword_filter, $article['keywords']);
    $status_match = isset($article['status']) && $article['status'] === 'valide'; // vérifier le statut
    return $category_match && $keyword_match && $status_match;
});

// Trier les articles du plus récent au plus ancien
usort($filtered_articles, function ($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});

// Pagination
$articles_per_page = 10;
$total_articles = count($filtered_articles);
$total_pages = ceil($total_articles / $articles_per_page);
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$current_page = max(1, min($total_pages, $current_page));
$start_index = ($current_page - 1) * $articles_per_page;
$paginated_articles = array_slice($filtered_articles, $start_index, $articles_per_page);

// Fonction pour calculer la moyenne des notes
function calculate_average_rating($comments) {
    if (empty($comments)) {
        return null;
    }
    $total_rating = 0;
    $rating_count = 0;
    foreach ($comments as $comment) {
        if (isset($comment['rating'])) {
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
    <title>Home</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <?php include('header.php'); ?>
    <div class="container">
        <h1 class="mt-5">Accueil</h1>

        <!-- Filtres -->
        <form method="get" class="mb-4">
            <div class="form-row">
                <div class="col">
                    <input type="text" name="category" class="form-control" placeholder="Catégorie" value="<?php echo htmlspecialchars($category_filter); ?>">
                </div>
                <div class="col">
                    <input type="text" name="keyword" class="form-control" placeholder="Mot Clé" value="<?php echo htmlspecialchars($keyword_filter); ?>">
                </div>
                <div class="col">
                    <button type="submit" class="btn btn-primary">Filtrer</button>
                </div>
            </div>
        </form>

        <?php if (empty($paginated_articles)): ?>
            <p>Aucun article trouvé.</p>
        <?php else: ?>
            <div class="row">
                <?php foreach ($paginated_articles as $index => $article): ?>
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <?php if ($article['main_image']): ?>
                                <img src="<?php echo htmlspecialchars($article['main_image']); ?>" class="card-img-top" alt="Image Principale">
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($article['title']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars($article['content']); ?></p>
                                <p class="card-text"><small class="text-muted">Catégorie: <?php echo htmlspecialchars($article['category']); ?></small></p>
                                <p class="card-text"><small class="text-muted">Mots Clés: <?php echo implode(', ', $article['keywords']); ?></small></p>
                                <p class="card-text"><small class="text-muted">Auteur: <?php echo htmlspecialchars($article['author']); ?></small></p>

                                <?php
                                $average_rating = calculate_average_rating($article['comments'] ?? []);
                                if ($average_rating !== null): ?>
                                    <p class="card-text"><small class="text-muted">Note Moyenne: <?php echo number_format($average_rating, 1); ?>/5</small></p>
                                <?php endif; ?>

                                <!-- Lien pour consulter l'article -->
                                <a href="view_article.php?id=<?php echo $index; ?>" class="btn btn-primary">Consulter</a>
                                
                                <?php if ($is_admin): ?>
                                    <!-- Formulaire de suppression -->
                                    <form action="home.php" method="post" style="display: inline;">
                                        <input type="hidden" name="article_index" value="<?php echo $index; ?>">
                                        <button type="submit" name="delete_article" class="btn btn-danger btn-sm">Supprimer</button>
                                    </form>
                                    
                                    <!-- Formulaire de modification -->
                                    <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editModal<?php echo $index; ?>">Modifier</button>
                                    
                                    <!-- Modal pour modifier l'article -->
                                    <div class="modal fade" id="editModal<?php echo $index; ?>" tabindex="-1" role="dialog" aria-labelledby="editModalLabel<?php echo $index; ?>" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="editModalLabel<?php echo $index; ?>">Modifier l'article</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <form action="home.php" method="post" enctype="multipart/form-data">
                                                        <input type="hidden" name="article_index" value="<?php echo $index; ?>">
                                                        <div class="form-group">
                                                            <label for="article_title<?php echo $index; ?>">Titre de l'article</label>
                                                            <input type="text" class="form-control" id="article_title<?php echo $index; ?>" name="article_title" value="<?php echo htmlspecialchars($article['title']); ?>" required>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="article_content<?php echo $index; ?>">Contenu de l'article</label>
                                                            <textarea class="form-control" id="article_content<?php echo $index; ?>" name="article_content" rows="5" required><?php echo htmlspecialchars($article['content']); ?></textarea>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="category<?php echo $index; ?>">Catégorie</label>
                                                            <input type="text" class="form-control" id="category<?php echo $index; ?>" name="category" value="<?php echo htmlspecialchars($article['category']); ?>" required>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="keywords<?php echo $index; ?>">Mots Clés (séparés par des virgules)</label>
                                                            <input type="text" class="form-control" id="keywords<?php echo $index; ?>" name="keywords" value="<?php echo implode(', ', $article['keywords']); ?>" required>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="main_image<?php echo $index; ?>">Image Principale</label>
                                                            <input type="file" class="form-control-file" id="main_image<?php echo $index; ?>" name="main_image">
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="gallery_images<?php echo $index; ?>">Galerie d'Images</label>
                                                            <input type="file" class="form-control-file" id="gallery_images<?php echo $index; ?>" name="gallery_images[]" multiple>
                                                        </div>
                                                        <button type="submit" name="update_article" class="btn btn-primary">Enregistrer</button>
                                                    </form>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php if ($i == $current_page) echo 'active'; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&category=<?php echo urlencode($category_filter); ?>&keyword=<?php echo urlencode($keyword_filter); ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>

</body>
</html>
