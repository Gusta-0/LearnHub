<?php
session_start();
require_once 'includes/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$cart_item_id = $_POST['cart_item_id'] ?? null;

if ($cart_item_id) {
    try {
        // Deleta o item do carrinho, garantindo que pertence ao usuário logado
        $stmt_delete = $pdo->prepare("DELETE FROM cart_items WHERE id = :cart_item_id AND user_id = :user_id");
        $stmt_delete->bindParam(':cart_item_id', $cart_item_id, PDO::PARAM_INT);
        $stmt_delete->bindParam(':user_id', $user_id, PDO::PARAM_INT);

        if ($stmt_delete->execute()) {
            header("Location: cart.php?status=removed");
            exit();
        } else {
            header("Location: cart.php?status=remove_error&message=" . urlencode("Erro ao remover item do carrinho."));
            exit();
        }
    } catch (\PDOException $e) {
        header("Location: cart.php?status=remove_error&message=" . urlencode("Erro de banco de dados: " . $e->getMessage()));
        exit();
    }
} else {
    header("Location: cart.php?status=remove_error&message=" . urlencode("ID do item do carrinho não fornecido."));
    exit();
}
?>