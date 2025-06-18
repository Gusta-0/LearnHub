<?php
$host = "localhost"; // Geralmente 'localhost' para XAMPP
$dbname = "cursos_db"; // Nome do banco de dados que criamos
$username = "root";    // Usuário padrão do MySQL no XAMPP
$password = "";        // Senha padrão do MySQL no XAMPP (vazia)
$charset = "utf8mb4";  // Charset para suportar caracteres especiais

// DSN (Data Source Name) - Informações de conexão para o PDO
$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

// Opções do PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lança exceções em caso de erros
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Define o modo de retorno padrão como array associativo
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Desabilita a emulação de prepared statements (mais seguro)
];

try {
    // Cria a instância PDO
    $pdo = new PDO($dsn, $username, $password, $options);
    // A variável de conexão agora se chama $pdo, em vez de $conn
    // echo "Conexão PDO bem-sucedida!"; // Para depuração
} catch (\PDOException $e) {
    // Em caso de erro na conexão, encerra o script e mostra a mensagem de erro
    die("Erro de conexão com o banco de dados: " . $e->getMessage());
}
