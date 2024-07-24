<?php
session_start();

// Vérifier si l'utilisateur est administrateur ou créateur
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['status'], ['admin', 'createur'])) {
    header("Location: /connexion");
    exit;
}

$articles_file = 'admin/articles.txt';

$articles = [];

// Charger les articles existants
if (file_exists($articles_file)) {
    $articles = json_decode(file_get_contents($articles_file), true);
}

// Gestion des articles
if (isset($_POST['create_article'])) {
    $article_title = trim($_POST['article_title']);
    $article_content = trim($_POST['article_content']);
    $category = trim($_POST['category']);
    $article_keywords = explode(',', trim($_POST['keywords']));
    $main_image = '';
    $gallery_images = [];
    $author = $_SESSION['user']['pseudo']; // Ajouter le créateur de l'article
    $status = 'en attente'; // Définir le statut comme "en attente"

    // Gestion de l'image principale
    if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] == UPLOAD_ERR_OK) {
        $main_image = 'uploads/' . basename($_FILES['main_image']['name']);
        move_uploaded_file($_FILES['main_image']['tmp_name'], $main_image);
    }

    // Gestion de la galerie d'images
    if (isset($_FILES['gallery_images'])) {
        foreach ($_FILES['gallery_images']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['gallery_images']['error'][$key] == UPLOAD_ERR_OK) {
                $gallery_image = 'uploads/' . basename($_FILES['gallery_images']['name'][$key]);
                move_uploaded_file($tmp_name, $gallery_image);
                $gallery_images[] = $gallery_image;
            }
        }
    }

    $new_article = [
        'title' => $article_title,
        'content' => $article_content,
        'category' => $category,
        'keywords' => $article_keywords,
        'main_image' => $main_image,
        'gallery_images' => $gallery_images,
        'author' => $author, // Enregistrer le créateur de l'article
        'status' => $status // Enregistrer le statut de l'article
    ];
    $articles[] = $new_article;
    file_put_contents($articles_file, json_encode($articles));
} elseif (isset($_POST['delete_article'])) {
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
    $author = $articles[$article_index]['author']; // Conserver le créateur d'origine
    $status = $articles[$article_index]['status']; // Conserver le statut d'origine

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
        'author' => $author, // Conserver le créateur d'origine
        'status' => $status // Conserver le statut d'origine
    ];
    file_put_contents($articles_file, json_encode($articles));
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
    <?php include('header'); ?>
    <div class="container">
        <h1 class="mt-5">Gestion des Articles</h1>
        
        <!-- Gestion des articles -->
        <div class="mt-5">
            <form action="creation_article" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="article_title">Titre de l'article</label>
                    <input type="text" class="form-control" id="article_title" name="article_title" placeholder="Titre de l'article" required>
                </div>
                <div class="form-group">
                    <label for="article_content">Contenu de l'article</label>
                    <textarea class="form-control" id="article_content" name="article_content" placeholder="Contenu de l'article" rows="5" required></textarea>
                </div>
                <div class="form-group">
                    <label for="category">Catégorie</label>
                    <input type="text" class="form-control" id="category" name="category" placeholder="Catégorie" required>
                </div>
                <div class="form-group">
                    <label for="keywords">Mots Clés (séparés par des virgules)</label>
                    <input type="text" class="form-control" id="keywords" name="keywords" placeholder="Mots Clés" required>
                </div>
                <div class="form-group">
                    <label for="main_image">Image Principale</label>
                    <input type="file" class="form-control-file" id="main_image" name="main_image" required>
                </div>
                <div class="form-group">
                    <label for="gallery_images">Galerie d'Images</label>
                    <input type="file" class="form-control-file" id="gallery_images" name="gallery_images[]" multiple>
                </div>
                <button type="submit" name="create_article" class="btn btn-primary">Créer</button>
            </form>
        </div>
    </div>
</body>
</html>
