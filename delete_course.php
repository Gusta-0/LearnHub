<?php
session_start();
require_once 'includes/database.php'; // Certifique-se que esta linha está correta e funcionando!

// Redireciona se o usuário não estiver logado
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Obter o papel (role) do usuário logado
$user_role = '';
try {
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = :user_id");
    $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    $user_role = $stmt->fetchColumn();
} catch (\PDOException $e) {
    error_log("Erro ao buscar papel do usuário para restrição de acesso: " . $e->getMessage());
    $user_role = 'user'; // Fallback seguro
}

// Redireciona se o usuário não for admin
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
        // Primeiro, obtenha o nome da imagem para poder deletá-la do servidor
        $stmt_select_image = $pdo->prepare("SELECT image FROM courses WHERE id = :id");
        $stmt_select_image->bindParam(':id', $course_id, PDO::PARAM_INT);
        $stmt_select_image->execute();
        $image_to_delete = $stmt_select_image->fetchColumn(); // fetchColumn() para obter um único valor

        // Deleta o curso do banco de dados
        $stmt_delete = $pdo->prepare("DELETE FROM courses WHERE id = :id");
        $stmt_delete->bindParam(':id', $course_id, PDO::PARAM_INT);

        if ($stmt_delete->execute()) {
            // Se a exclusão do banco de dados foi bem-sucedida, tenta deletar a imagem
            if ($image_to_delete && file_exists('uploads/' . $image_to_delete)) {
                unlink('uploads/' . $image_to_delete); // Deleta o arquivo da imagem
            }
            header("Location: dashboard.php?status=deleted");
            exit();
        } else {
            // Erro ao deletar no banco de dados
            header("Location: dashboard.php?status=error&message=" . urlencode("Erro ao deletar curso: " . $stmt_delete->errorInfo()[2]));
            exit();
        }
    } catch (\PDOException $e) {
        header("Location: dashboard.php?status=error&message=" . urlencode("Erro no banco de dados ao deletar curso: " . $e->getMessage()));
        exit();
    }
} else {
    // ID do curso não fornecido
    header("Location: dashboard.php?status=error&message=" . urlencode("ID do curso não fornecido."));
    exit();
}
?>