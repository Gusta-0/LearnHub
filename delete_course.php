<?php
session_start();
require_once 'includes/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_role = '';
try {
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = :user_id");
    $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    $user_role = $stmt->fetchColumn();
} catch (\PDOException $e) {
    error_log("Erro ao buscar papel do usuário para restrição de acesso: " . $e->getMessage());
    $user_role = 'user';
}

if ($user_role !== 'admin') {
    header("Location: dashboard.php?access_denied=true");
    exit();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $course_id = $_GET['id'];

    try {
        $stmt_select_image = $pdo->prepare("SELECT image FROM courses WHERE id = :id");
        $stmt_select_image->bindParam(':id', $course_id, PDO::PARAM_INT);
        $stmt_select_image->execute();
        $image_to_delete = $stmt_select_image->fetchColumn();

        $stmt_delete = $pdo->prepare("DELETE FROM courses WHERE id = :id");
        $stmt_delete->bindParam(':id', $course_id, PDO::PARAM_INT);

        if ($stmt_delete->execute()) {
            if ($image_to_delete && file_exists('uploads/' . $image_to_delete)) {
                unlink('uploads/' . $image_to_delete);
            }
            header("Location: dashboard.php?status=deleted");
            exit();
        } else {

            header("Location: dashboard.php?status=error&message=" . urlencode("Erro ao deletar curso: " . $stmt_delete->errorInfo()[2]));
            exit();
        }
    } catch (\PDOException $e) {
        header("Location: dashboard.php?status=error&message=" . urlencode("Erro no banco de dados ao deletar curso: " . $e->getMessage()));
        exit();
    }
} else {

    header("Location: dashboard.php?status=error&message=" . urlencode("ID do curso não fornecido."));
    exit();
}
?>