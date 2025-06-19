<?php
session_start();
require_once 'includes/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
    exit();
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Você precisa estar logado para adicionar itens.']);
    exit();
}

if (empty($_POST['course_id']) || !is_numeric($_POST['course_id'])) {
    echo json_encode(['success' => false, 'message' => 'ID do curso inválido.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$course_id = $_POST['course_id'];

try {
    $stmt_check = $pdo->prepare("SELECT id FROM cart_items WHERE user_id = :user_id AND course_id = :course_id");
    $stmt_check->execute(['user_id' => $user_id, 'course_id' => $course_id]);

    if ($stmt_check->fetch()) {
        $message = 'Este curso já está no seu carrinho!';
    } else {
        $stmt_insert = $pdo->prepare("INSERT INTO cart_items (user_id, course_id, added_at) VALUES (:user_id, :course_id, NOW())");
        $stmt_insert->execute(['user_id' => $user_id, 'course_id' => $course_id]);
        $message = 'Curso adicionado ao carrinho com sucesso!';
    }

    $stmt_count = $pdo->prepare("SELECT COUNT(*) FROM cart_items WHERE user_id = :user_id");
    $stmt_count->execute(['user_id' => $user_id]);
    $new_cart_count = $stmt_count->fetchColumn();

    echo json_encode([
        'success' => true,
        'message' => $message,
        'cart_item_count' => $new_cart_count
    ]);

} catch (\PDOException $e) {
    error_log("Erro no add_to_cart: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Ocorreu um erro de servidor. Tente novamente.']);
}
exit();
?>