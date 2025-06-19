<?php
session_start();
require_once 'includes/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$cart_items = [];
$total_price = 0;

try {

    $stmt = $pdo->prepare("
        SELECT 
            ci.id AS cart_item_id, 
            c.title, 
            c.price, 
            c.image
        FROM 
            cart_items ci
        JOIN 
            courses c ON ci.course_id = c.id
        WHERE 
            ci.user_id = :user_id
        ORDER BY 
            ci.added_at DESC
    ");
    $stmt->execute(['user_id' => $user_id]);
    $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $cart_item_count = count($cart_items);
    foreach ($cart_items as $item) {
        $total_price += $item['price'];
    }

} catch (\PDOException $e) {

    error_log("Erro ao carregar carrinho: " . $e->getMessage());
    $errorMessage = "<div class='alert alert-danger'>Não foi possível carregar os itens do carrinho. Tente novamente mais tarde.</div>";
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Carrinho - LearnHub</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/cart-css.css">
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
                <a class="nav-link" href="dashboard.php">Cursos</a>
            </li>
            <li class="nav-item active mr-3"><a class="nav-link cart-link" href="cart.php">
                    <i class="fas fa-shopping-cart"></i> Carrinho
                    <span class="cart-badge"
                          id="cart-badge" <?php if ($cart_item_count == 0) echo 'style="display: none;"'; ?>>
                        <?php echo $cart_item_count; ?>
                    </span>
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
    <h1 class="mb-4 text-center page-title">Meu Carrinho</h1>

    <div id="checkout-success-message" class="alert alert-success text-center" style="display: none;"></div>

    <?php if (isset($errorMessage)) {
        echo $errorMessage;
    } ?>

    <?php if (!isset($errorMessage) && count($cart_items) > 0): ?>
        <div id="cart-content">
            <?php foreach ($cart_items as $item): ?>
                <div class="cart-item">
                    <?php if (!empty($item['image'])): ?>
                        <img src="uploads/<?php echo htmlspecialchars($item['image']); ?>" alt="Capa do Curso">
                    <?php else: ?>
                        <img src="https://via.placeholder.com/100x100?text=LearnHub" alt="Sem Imagem">
                    <?php endif; ?>
                    <div class="cart-item-details">
                        <h5><?php echo htmlspecialchars($item['title']); ?></h5>
                        <p class="text-muted mb-1">Preço:
                            R$ <?php echo number_format($item['price'], 2, ',', '.'); ?></p>
                    </div>
                    <div>
                        <form action="remove_from_cart.php" method="POST" class="d-inline">
                            <input type="hidden" name="cart_item_id" value="<?php echo $item['cart_item_id']; ?>">
                            <button type="submit" class="btn btn-custom btn-delete btn-sm"><i
                                        class="fas fa-trash-alt"></i> Remover
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="cart-total text-right">
                <h3>Total: R$ <?php echo number_format($total_price, 2, ',', '.'); ?></h3>
                <a href="#" id="btn-checkout" class="btn btn-custom btn-checkout btn-lg mt-3">Finalizar Compra</a>
            </div>
        </div>
    <?php elseif (!isset($errorMessage)): ?>
        <div class="alert empty-cart-message text-center" role="alert">
            <i class="fas fa-shopping-cart fa-3x mb-3"></i><br>
            Seu carrinho está vazio. <a href="dashboard.php">Comece a adicionar cursos!</a>
        </div>
    <?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
    $(document).ready(function () {
        $('#btn-checkout').on('click', function (event) {
            event.preventDefault();

            var button = $(this);
            button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processando...');


            $.ajax({
                type: 'POST',
                url: 'checkout.php',
                dataType: 'json',
                success: function (response) {
                    if (response.success) {

                        $('#cart-content').fadeOut('slow', function () {
                            $(this).remove();
                        });


                        $('#cart-badge').text('0').hide();


                        var successMessage = '<h3><i class="fas fa-check-circle"></i> Compra bem-sucedida!</h3>' +
                            '<p>Obrigado por comprar na LearnHub. Tenha um ótimo aprendizado!</p>' +
                            '<a href="dashboard.php" class="btn btn-primary mt-2">Voltar aos Cursos</a>';
                        $('#checkout-success-message').html(successMessage).fadeIn('slow');

                    } else {
                        // Se houver um erro, mostra uma mensagem e reabilita o botão
                        alert('Erro: ' + (response.message || 'Ocorreu um problema.'));
                        button.prop('disabled', false).text('Finalizar Compra');
                    }
                },
                error: function () {
                    alert('Ocorreu um erro de comunicação. Tente novamente.');
                    button.prop('disabled', false).text('Finalizar Compra');
                }
            });
        });
    });
</script>

</body>
</html>