<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (isset($_SESSION['user'])) {
    $user = $_SESSION['user'];
    $is_admin = ($user['status'] === 'admin');
    $is_createur = ($user['status'] === 'createur');
} else {
    // Redirection si l'utilisateur n'est pas connecté
    header("Location: /connexion");
    exit;
}

// Déconnexion de l'utilisateur
if (isset($_POST['logout'])) {
    session_unset();    
    session_destroy(); 
    header("Location: /connexion");
    exit;
}
?>

<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="#">Your Blog</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item">
                <a class="nav-link" href="/home">Home</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/profil">Profile</a>
            </li>
            <?php if ($is_admin || $is_createur): ?>
                <li class="nav-item">
                <a class="nav-link" href="/creation_article">Publier un article</a>
            </li>
            <?php endif; ?>
            <?php if ($is_admin): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="gestionDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Gestion
                    </a>
                    <div class="dropdown-menu" aria-labelledby="gestionDropdown">
                        <a class="dropdown-item" href="/admin/gestion_article">Gestion des articles</a>
                        <a class="dropdown-item" href="/admin/gestion_commentaires">Gestion des commentaires</a>
                        <a class="dropdown-item" href="/admin/gestion_utilisateur">Gestion des utilisateurs</a>
                    </div>
                </li>
            <?php endif; ?>
        </ul>
        <!-- Formulaire de déconnexion -->
        <form method="post" class="form-inline my-2 my-lg-0">
            <button type="submit" name="logout" class="btn btn-outline-danger my-2 my-sm-0">Déconnexion</button>
        </form>
    </div>
</nav>

<!-- Inclusion des bibliothèques JavaScript nécessaires -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
