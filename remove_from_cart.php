<?php
session_start();
require_once 'includes/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if (!isset($_POST['cart_item_id']) || !is_numeric($_POST['cart_item_id'])) ;

try {
    $cart_item_id = $_POST['cart_item_id'];
    $user_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("DELETE FROM cart_items WHERE id = :cart_item_id AND user_id = :user_id");
    $stmt->bindParam(':cart_item_id', $cart_item_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();

    header("Location: cart.php?status=removed_successfully");
    exit();

} catch (\PDOException $e) {
    error_log("Erro ao remover item do carrinho: " . $e->getMessage());

    header("Location: cart.php?status=remove_error&message=" . urlencode("Ocorreu um erro ao tentar remover o item."));
    exit();
}
?>