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

$cart_item_count = 0;
try {
    $stmt_cart = $pdo->prepare("SELECT COUNT(*) FROM cart_items WHERE user_id = :user_id");
    $stmt_cart->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt_cart->execute();
    $cart_item_count = $stmt_cart->fetchColumn();
} catch (\PDOException $e) {
    error_log("Erro ao contar itens do carrinho: " . $e->getMessage());
}

$search_term = $_GET['search'] ?? '';
$courses = [];

try {
    $sql = "SELECT id, title, description, category, price, image, video_link FROM courses";

    if (!empty($search_term)) {
        $sql .= " WHERE title LIKE :search OR description LIKE :search";
    }

    $sql .= " ORDER BY id DESC";

    $stmt_courses = $pdo->prepare($sql);

    // Associa o parâmetro de busca se necessário
    if (!empty($search_term)) {
        $like_term = '%' . $search_term . '%';
        $stmt_courses->bindParam(':search', $like_term, PDO::PARAM_STR);
    }

    $stmt_courses->execute();
    $courses = $stmt_courses->fetchAll(PDO::FETCH_ASSOC);

} catch (\PDOException $e) {
    echo "<div class='alert alert-danger'>Erro ao carregar cursos: " . $e->getMessage() . "</div>";
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - LearnHub</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand logo" href="dashboard.php"><i class="fas fa-brain"></i> LearnHub</a>

    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div id="alert-container" style="position: fixed; top: 80px; right: 20px; z-index: 9999; width: 300px;"></div>
    <div class="collapse navbar-collapse" id="navbarNav">
        <form class="form-inline mx-auto search-form" action="dashboard.php" method="GET">
            <input class="form-control mr-sm-2 flex-grow-1" type="search" name="search" placeholder="O que você quer aprender?" aria-label="Search" value="<?php echo htmlspecialchars($search_term); ?>">
            <button class="btn btn-outline-success" type="submit">Buscar</button>
        </form>

        <ul class="navbar-nav ml-auto">
            <?php if ($user_role === 'admin'): ?>
                <li class="nav-item">
                    <a class="nav-link" href="add_course.php">Adicionar Curso</a>
                </li>
            <?php endif; ?>
            <li class="nav-item mr-3">
                <a class="nav-link cart-link" href="cart.php">
                    <i class="fas fa-shopping-cart"></i> Carrinho
                    <?php if ($cart_item_count > 0): ?>
                        <span class="cart-badge" id="cart-badge"><?php echo $cart_item_count; ?></span>
                    <?php else: ?>
                        <span class="cart-badge" id="cart-badge" style="display: none;"></span>
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

<div class="container mt-5">
    <div class="text-center mb-5 page-header">
        <h1>Explore Nossos Cursos</h1>
        <p class="lead">Encontre a próxima habilidade para impulsionar sua carreira.</p>
    </div>

    <div class="row">
        <?php if (count($courses) > 0): ?>
            <?php foreach ($courses as $course): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card course-card h-100">
                        <?php if (!empty($course['image'])): ?>
                            <img src="uploads/<?php echo htmlspecialchars($course['image']); ?>" class="card-img-top" alt="Capa do Curso">
                        <?php else: ?>
                            <img src="https://via.placeholder.com/400x200?text=LearnHub" class="card-img-top" alt="Sem Imagem">
                        <?php endif; ?>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title gradient-text"><?php echo htmlspecialchars($course['title']); ?></h5>
                            <p class="card-text text-muted"><?php echo htmlspecialchars($course['category']); ?></p>
                            <p class="card-text"><?php echo nl2br(htmlspecialchars($course['description'])); ?></p>
                            <p class="card-text course-price gradient-text">R$ <?php echo number_format($course['price'], 2, ',', '.'); ?></p>

                            <div class="mt-auto card-actions">
                                <?php if (!empty($course['video_link'])): ?>
                                    <a href="<?php echo htmlspecialchars($course['video_link']); ?>" target="_blank" class="btn btn-custom btn-intro btn-sm">Ver Introdução</a>
                                <?php endif; ?>

                                <?php if ($user_role === 'admin'): ?>
                                    <a href="edit_course.php?id=<?php echo $course['id']; ?>" class="btn btn-custom btn-edit btn-sm"><i class="fas fa-edit"></i> Editar</a>
                                    <a href="delete_course.php?id=<?php echo $course['id']; ?>" class="btn btn-custom btn-delete btn-sm" onclick="return confirm('Tem certeza que deseja excluir este curso?');"><i class="fas fa-trash-alt"></i> Excluir</a>
                                <?php else: ?>
                                    <form method="POST" class="d-inline add-to-cart-form">
                                        <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                                        <button type="submit" class="btn btn-custom btn-add-cart btn-sm"><i class="fas fa-cart-plus"></i> Adicionar ao Carrinho</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info text-center" role="alert">
                    Nenhum curso encontrado para o termo "<?php echo htmlspecialchars($search_term); ?>".
                    <?php if ($user_role === 'admin'): ?>
                        <a href="add_course.php">Cadastre o primeiro!</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script> <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
    $(document).ready(function() {
        // Escuta o evento de 'submit' nos formulários de adicionar ao carrinho
        $('.add-to-cart-form').on('submit', function(event) {
            event.preventDefault();

            var formData = $(this).serialize();
            var button = $(this).find('button[type="submit"]');
            var originalButtonHtml = button.html();

            button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

            $.ajax({
                type: 'POST',
                url: 'add_to_cart.php',
                data: formData,
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        // Atualiza o contador do carrinho na navbar
                        var badge = $('#cart-badge');
                        badge.text(response.cart_item_count);
                        if(response.cart_item_count > 0) {
                            badge.show();
                        } else {
                            badge.hide();
                        }

                        // Mostra a mensagem de sucesso
                        showAlert(response.message, 'success');
                    } else {
                        // Mostra a mensagem de erro (ex: usuário não logado)
                        showAlert(response.message, 'danger');
                    }
                },
                error: function() {
                    // Roda se houver um erro de conexão
                    showAlert('Não foi possível conectar ao servidor.', 'danger');
                },
                complete: function() {
                    // Reabilita o botão ao final da requisição
                    button.prop('disabled', false).html(originalButtonHtml);
                }
            });
        });

        // Função para mostrar os alertas dinâmicos
        function showAlert(message, type) {
            $('html, body').animate({scrollTop: 0}, 'slow');

            var alertHtml = '<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' +
                message +
                '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                '<span aria-hidden="true">&times;</span>' +
                '</button>' +
                '</div>';

            $('#alert-container').html(alertHtml);

            setTimeout(function() {
                $('#alert-container .alert').fadeOut('slow', function() { $(this).remove(); });
            }, 4000);
        }
    });
</script>
</body>
</html>