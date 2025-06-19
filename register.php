<?php
session_start();
require_once 'includes/database.php';

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error_message = "Todos os campos são obrigatórios.";
    } elseif ($password !== $confirm_password) {
        $error_message = "As senhas não coincidem.";
    } elseif (strlen($password) < 6) {
        $error_message = "A senha deve ter pelo menos 6 caracteres.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username OR email = :email");
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $error_message = "Usuário ou email já cadastrado.";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                $stmt_insert = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
                $stmt_insert->bindParam(':username', $username, PDO::PARAM_STR);
                $stmt_insert->bindParam(':email', $email, PDO::PARAM_STR);
                $stmt_insert->bindParam(':password', $hashed_password, PDO::PARAM_STR);

                if ($stmt_insert->execute()) {
                    $success_message = "Cadastro realizado com sucesso! Você já pode fazer login.";
                } else {
                    $error_message = "Erro ao cadastrar: " . $stmt_insert->errorInfo()[2];
                }
            }
        } catch (\PDOException $e) {
            $error_message = "Erro no banco de dados durante o cadastro: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - LearnHub</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/register.css">
</head>
<body>
<div class="container">
    <div class="register-container">
        <h2 class="text-center mb-4">Crie sua Conta no LearnHub</h2>
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-light" role="alert"
                 style="background-color: rgba(255, 255, 255, 0.85); color: #155724; font-weight: bold;">
                <?php echo $success_message; ?> <a href="index.php">Fazer Login</a>
            </div>
        <?php endif; ?>
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger" role="alert"
                 style="background-color: rgba(255, 255, 255, 0.85); color: #721c24; font-weight: bold;">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        <form action="register.php" method="POST">
            <div class="form-group">
                <label for="username">Usuário:</label>
                <input type="text" class="form-control" id="username" name="username" required
                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
            </div>
            <div class="form-group">
                <label for="email">E-mail:</label>
                <input type="email" class="form-control" id="email" name="email" required
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
            <div class="form-group">
                <label for="password">Senha (mínimo 6 caracteres):</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirmar Senha:</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit" class="btn btn-success btn-block">Finalizar Cadastro</button>
            <p class="text-center mt-3">Já tem uma conta? <a href="index.php">Faça Login</a></p>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>