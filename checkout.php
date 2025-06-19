<?php
session_start();
require_once 'includes/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não logado.']);
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    $stmt_clear_cart = $pdo->prepare("DELETE FROM cart_items WHERE user_id = :user_id");
    $stmt_clear_cart->execute(['user_id' => $user_id]);

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    error_log("Erro no checkout simplificado: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Ocorreu um erro ao processar sua compra.']);
}
exit();
?>