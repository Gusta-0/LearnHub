<?php
session_start();
// O arquivo de conexão agora define a variável $pdo
require_once 'includes/database.php';


$error_message = '';

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];




    try {
        // Prepara a consulta SQL para buscar o usuário
        // Use :username como placeholder nomeado
        $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE username = :username");

        // Associa o valor ao placeholder
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);

        // Executa a consulta
        $stmt->execute();

        // Obtém o resultado
        $user = $stmt->fetch(PDO::FETCH_ASSOC); // PDO::FETCH_ASSOC retorna um array associativo

        if ($user) {
            // Verifica a senha criptografada
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                header("Location: dashboard.php");
                exit();
            } else {
                $error_message = "Usuário ou senha incorretos.";
            }
        } else {
            $error_message = "Usuário ou senha incorretos.";
        }
    } catch (\PDOException $e) {
        $error_message = "Erro ao tentar fazer login: " . $e->getMessage();
        // Em um ambiente de produção, você logaria o erro detalhado e mostraria uma mensagem genérica.
    }
}
// O restante do HTML e Bootstrap permanece o mesmo
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - LearnHub</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
<div class="container">
    <div class="login-container">
        <h2 class="text-center mb-4">Login no LearnHub</h2>
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        <form action="index.php" method="POST">
            <div class="form-group">
                <label for="username">Usuário:</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Senha:</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Entrar</button>
            <p class="text-center mt-3">Não tem uma conta? <a href="register.php">Cadastre-se aqui</a></p>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>