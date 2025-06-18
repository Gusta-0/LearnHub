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

// NOVO: Contar itens no carrinho para a navbar
$cart_item_count = 0;
try {
    $stmt_cart_count = $pdo->prepare("SELECT COUNT(*) FROM cart_items WHERE user_id = :user_id");
    $stmt_cart_count->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt_cart_count->execute();
    $cart_item_count = $stmt_cart_count->fetchColumn();
} catch (\PDOException $e) {
    error_log("Erro ao contar itens do carrinho: " . $e->getMessage());
}

try {
    // Busca os itens do carrinho do usuário, juntando com as informações do curso
    $stmt = $pdo->prepare("
        SELECT ci.id as cart_item_id, c.id as course_id, c.title, c.price, c.image, ci.quantity
        FROM cart_items ci
        JOIN courses c ON ci.course_id = c.id
        WHERE ci.user_id = :user_id
        ORDER BY ci.added_at DESC
    ");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($cart_items as $item) {
        $total_price += ($item['price'] * $item['quantity']);
    }

} catch (\PDOException $e) {
    echo "<div class='alert alert-danger'>Erro ao carregar carrinho: " . $e->getMessage() . "</div>";
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
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <form class="form-inline mx-auto search-form" action="dashboard.php" method="GET">
            <input class="form-control mr-sm-2 flex-grow-1" type="search" name="search" placeholder="O que você quer aprender?" aria-label="Search">
            <button class="btn btn-outline-success" type="submit">Buscar</button>
        </form>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link" href="dashboard.php">Cursos</a>
            </li>
            <li class="nav-item active">
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

<div class="container mt-5">
    <h1 class="mb-4 text-center page-title">Meu Carrinho</h1>

    <?php if (count($cart_items) > 0): ?>
        <?php foreach ($cart_items as $item): ?>
            <div class="cart-item">
                <?php if (!empty($item['image'])): ?>
                    <img src="uploads/<?php echo htmlspecialchars($item['image']); ?>" alt="Capa do Curso">
                <?php else: ?>
                    <img src="https://via.placeholder.com/100x100?text=LearnHub" alt="Sem Imagem">
                <?php endif; ?>
                <div class="cart-item-details">
                    <h5><?php echo htmlspecialchars($item['title']); ?></h5>
                    <p class="text-muted mb-1">Preço: R$ <?php echo number_format($item['price'], 2, ',', '.'); ?></p>
                    <p>Quantidade: <?php echo $item['quantity']; ?></p>
                </div>
                <div>
                    <form action="remove_from_cart.php" method="POST" class="d-inline">
                        <input type="hidden" name="cart_item_id" value="<?php echo $item['cart_item_id']; ?>">
                        <button type="submit" class="btn btn-custom btn-delete btn-sm"><i class="fas fa-trash-alt"></i> Remover</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="cart-total text-right">
            <h3>Total: R$ <?php echo number_format($total_price, 2, ',', '.'); ?></h3>
            <a href="#" class="btn btn-custom btn-checkout btn-lg mt-3">Finalizar Compra</a>
        </div>

    <?php else: ?>
        <div class="alert empty-cart-message text-center" role="alert">
            <i class="fas fa-shopping-cart fa-3x mb-3"></i><br>
            Seu carrinho está vazio. <a href="dashboard.php">Comece a adicionar cursos!</a>
        </div>
    <?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>