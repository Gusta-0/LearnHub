<?php
session_start();
require_once 'includes/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$user_role = '';

try {
    $stmt_role = $pdo->prepare("SELECT role FROM users WHERE id = :user_id");
    $stmt_role->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt_role->execute();
    $user_role = $stmt_role->fetchColumn();
} catch (\PDOException $e) {
    error_log("Erro ao buscar papel do usuário: " . $e->getMessage());
    $user_role = 'user';
}

if ($user_role !== 'admin') {
    header("Location: dashboard.php?access_denied=true");
    exit();
}

$cart_item_count = 0;
try {
    $stmt_cart = $pdo->prepare("SELECT COUNT(*) FROM cart_items WHERE user_id = :user_id");
    $stmt_cart->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt_cart->execute();
    $cart_item_count = $stmt_cart->fetchColumn();
} catch (\PDOException $e) {
    error_log("Erro ao contar itens do carrinho: " . $e->getMessage());
}


$course_id = $_GET['id'] ?? 0;
$course = null;
$error_message = '';
$success_message = '';

if ($course_id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT id, title, description, category, price, image, video_link FROM courses WHERE id = :id");
        $stmt->bindParam(':id', $course_id, PDO::PARAM_INT);
        $stmt->execute();
        $course = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$course) {
            $error_message = "Curso não encontrado.";
        }
    } catch (\PDOException $e) {
        $error_message = "Erro no banco de dados ao buscar curso: " . $e->getMessage();
    }
} else {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $course) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $video_link = $_POST['video_link'];
    $current_image = $course['image'];

    if (empty($title) || empty($description) || empty($category) || empty($price)) {
        $error_message = "Por favor, preencha todos os campos obrigatórios.";
    } elseif (!is_numeric($price) || $price < 0) {
        $error_message = "O preço deve ser um valor numérico positivo.";
    } else {
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $target_dir = "uploads/";
            $original_filename = basename($_FILES["image"]["name"]);
            $safe_filename = preg_replace('/[^A-Za-z0-9.\-_]/', '_', $original_filename);
            $new_image_name = time() . '_' . $safe_filename;
            $target_file = $target_dir . $new_image_name;
            $uploadOk = 1;
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            $check = getimagesize($_FILES["image"]["tmp_name"]);
            if ($check === false) {
                $error_message .= "O arquivo não é uma imagem válida. ";
                $uploadOk = 0;
            }

            if ($_FILES["image"]["size"] > 5000000) {
                $error_message .= "O arquivo é muito grande (máx 5MB). ";
                $uploadOk = 0;
            }

            if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
                $error_message .= "Apenas arquivos JPG, JPEG, PNG e GIF são permitidos. ";
                $uploadOk = 0;
            }

            if ($uploadOk == 1) {
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    if (!empty($current_image) && file_exists($target_dir . $current_image)) {
                        unlink($target_dir . $current_image);
                    }
                    $current_image = $new_image_name;
                } else {
                    $error_message .= "Houve um erro ao fazer upload da nova imagem. ";
                }
            }
        }

        if (empty($error_message)) {
            try {
                $stmt_update = $pdo->prepare("UPDATE courses SET title = :title, description = :description, category = :category, price = :price, image = :image, video_link = :video_link WHERE id = :id");
                $stmt_update->bindParam(':title', $title, PDO::PARAM_STR);
                $stmt_update->bindParam(':description', $description, PDO::PARAM_STR);
                $stmt_update->bindParam(':category', $category, PDO::PARAM_STR);
                $stmt_update->bindParam(':price', $price, PDO::PARAM_STR);
                $stmt_update->bindParam(':image', $current_image, PDO::PARAM_STR);
                $stmt_update->bindParam(':video_link', $video_link, PDO::PARAM_STR);
                $stmt_update->bindParam(':id', $course_id, PDO::PARAM_INT);

                if ($stmt_update->execute()) {
                    $success_message = "Curso atualizado com sucesso!";
                    $course['title'] = $title;
                    $course['description'] = $description;
                    $course['category'] = $category;
                    $course['price'] = $price;
                    $course['image'] = $current_image;
                    $course['video_link'] = $video_link;
                } else {
                    $error_message = "Erro ao atualizar curso: " . $stmt_update->errorInfo()[2];
                }
            } catch (\PDOException $e) {
                $error_message = "Erro no banco de dados ao atualizar curso: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Curso - LearnHub</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/edit-course.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand logo" href="dashboard.php"><i class="fas fa-brain"></i> LearnHub</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <form class="form-inline mx-auto search-form" action="dashboard.php" method="GET">
            <input class="form-control mr-sm-2 flex-grow-1" type="search" name="search"
                   placeholder="O que você quer aprender?" aria-label="Search">
            <button class="btn btn-outline-success" type="submit">Buscar</button>
        </form>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link" href="add_course.php">Adicionar Curso</a>
            </li>
            <li class="nav-item">
                <a class="nav-link cart-link" href="cart.php">
                    <i class="fas fa-shopping-cart"></i> Carrinho
                    <?php if ($cart_item_count > 0): ?>
                        <span class="cart-badge"><?php echo $cart_item_count; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="nav-item">
                <span class="navbar-text mr-3">Bem-vindo, <?php echo htmlspecialchars($username); ?>!</span>
            </li>
            <li class="nav-item">
                <a class="btn btn-outline-light" href="logout.php">Sair</a>
            </li>
        </ul>
    </div>
</nav>

<div class="container">
    <div class="form-container">
        <h1 class="mb-4 text-center">Editar Curso</h1>

        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?> <a href="dashboard.php" class="alert-link">Voltar
                    para Cursos</a></div>
        <?php endif; ?>
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <?php if ($course): ?>
            <form action="edit_course.php?id=<?php echo htmlspecialchars($course['id']); ?>" method="POST"
                  enctype="multipart/form-data">
                <div class="form-group">
                    <label for="title">Título do Curso:</label>
                    <input type="text" class="form-control" id="title" name="title" required
                           value="<?php echo htmlspecialchars($course['title']); ?>">
                </div>
                <div class="form-group">
                    <label for="description">Descrição:</label>
                    <textarea class="form-control" id="description" name="description" rows="5"
                              required><?php echo htmlspecialchars($course['description']); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="category">Categoria:</label>
                    <input type="text" class="form-control" id="category" name="category" required
                           value="<?php echo htmlspecialchars($course['category']); ?>">
                </div>
                <div class="form-group">
                    <label for="price">Preço (R$):</label>
                    <input type="number" step="0.01" class="form-control" id="price" name="price" required
                           value="<?php echo htmlspecialchars($course['price']); ?>">
                </div>
                <div class="form-group">
                    <label for="image">Imagem de Capa Atual:</label>
                    <?php if (!empty($course['image'])): ?>
                        <img src="uploads/<?php echo htmlspecialchars($course['image']); ?>"
                             class="current-image-preview" alt="Imagem atual do curso">
                    <?php else: ?>
                        <p class="text-light">Nenhuma imagem atual.</p>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="new_image">Nova Imagem de Capa (Opcional):</label>
                    <input type="file" class="form-control" id="image" name="image" accept="image/*">
                    <small class="form-text text-muted">Apenas JPG, JPEG, PNG e GIF são permitidos. Tamanho máximo:
                        5MB.</small>
                </div>
                <div class="form-group">
                    <label for="video_link">Link do Vídeo de Introdução (Opcional):</label>
                    <input type="url" class="form-control" id="video_link" name="video_link"
                           value="<?php echo htmlspecialchars($course['video_link']); ?>">
                </div>
                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-custom btn-submit">Atualizar Curso</button>
                    <a href="dashboard.php" class="btn btn-custom btn-cancel ml-2">Cancelar</a>
                </div>
            </form>
        <?php else: ?>
            <div class="alert alert-warning">Curso não encontrado ou ID inválido.</div>
        <?php endif; ?>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>