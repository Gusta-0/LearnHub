<?php
session_start();
require_once 'includes/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$course_id = $_POST['course_id'] ?? null;

if ($course_id) {
    try {
        // Verifica se o curso já está no carrinho do usuário para evitar duplicatas ou atualizar quantidade
        $stmt = $pdo->prepare("SELECT quantity FROM cart_items WHERE user_id = :user_id AND course_id = :course_id");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':course_id', $course_id, PDO::PARAM_INT);
        $stmt->execute();
        $existing_item = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing_item) {
            // Se já existe, apenas atualiza a quantidade (neste caso, mantemos 1) ou ignoramos
            // Para cursos, geralmente é 1 item por curso no carrinho, a menos que você venda "licenças"
            // Se você quiser permitir múltiplos do mesmo curso, atualize a quantidade:
            // $new_quantity = $existing_item['quantity'] + 1;
            // $stmt_update = $pdo->prepare("UPDATE cart_items SET quantity = :quantity WHERE user_id = :user_id AND course_id = :course_id");
            // $stmt_update->bindParam(':quantity', $new_quantity, PDO::PARAM_INT);
            // $stmt_update->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            // $stmt_update->bindParam(':course_id', $course_id, PDO::PARAM_INT);
            // $stmt_update->execute();
            header("Location: dashboard.php?status=already_in_cart"); // Redireciona de volta
            exit();
        } else {
            // Se não existe, insere um novo item no carrinho
            $stmt_insert = $pdo->prepare("INSERT INTO cart_items (user_id, course_id, quantity, added_at) VALUES (:user_id, :course_id, :quantity, NOW())");
            $quantity = 1; // Para cursos, a quantidade geralmente é 1
            $stmt_insert->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt_insert->bindParam(':course_id', $course_id, PDO::PARAM_INT);
            $stmt_insert->bindParam(':quantity', $quantity, PDO::PARAM_INT);

            if ($stmt_insert->execute()) {
                header("Location: dashboard.php?status=added_to_cart");
                exit();
            } else {
                header("Location: dashboard.php?status=cart_error&message=" . urlencode("Erro ao adicionar curso ao carrinho."));
                exit();
            }
        }
    } catch (\PDOException $e) {
        header("Location: dashboard.php?status=cart_error&message=" . urlencode("Erro de banco de dados: " . $e->getMessage()));
        exit();
    }
} else {
    header("Location: dashboard.php?status=cart_error&message=" . urlencode("ID do curso não fornecido."));
    exit();
}
?>