# LearnHub - Plataforma de Cursos Online

![Dashboard da Plataforma LearnHub](https://i.imgur.com/your-dashboard-image.png) ## ✒️ Sobre o Projeto

LearnHub é uma aplicação web desenvolvida em PHP puro, que simula uma plataforma de e-commerce para a venda de cursos online. O projeto foi construído com foco em funcionalidades essenciais de um sistema web moderno, incluindo autenticação de usuários, controle de acesso baseado em papéis (Admin e Usuário), gerenciamento completo de cursos (CRUD) e um sistema de carrinho de compras.

O design foi cuidadosamente elaborado para ser moderno e responsivo, utilizando fundos com gradientes animados, cards de alto contraste e uma interface de usuário intuitiva.

---

## ✨ Funcionalidades Principais

### Funcionalidades Gerais
* **Design Responsivo:** Interface adaptável para diferentes tamanhos de tela.
* **Sistema de Autenticação:** Páginas de Login e Cadastro com validação e segurança.
* **Página de Cursos:** Visualização de todos os cursos disponíveis com um layout moderno.
* **Busca de Cursos:** Campo de pesquisa funcional para encontrar cursos por título ou descrição.

### Funcionalidades do Administrador (`admin`)
* Acesso a um painel de controle completo.
* **Gerenciamento de Cursos (CRUD):**
    * **Criar:** Adicionar novos cursos à plataforma através de um formulário dedicado.
    * **Ler:** Visualizar todos os cursos cadastrados.
    * **Atualizar:** Editar informações de cursos existentes (título, descrição, preço, imagem, etc).
    * **Deletar:** Remover cursos da plataforma.
* Upload de imagens de capa para os cursos, com sanitização de nome de arquivo para maior segurança.

### Funcionalidades do Usuário Comum (`user`)
* Visualizar todos os cursos disponíveis.
* **Carrinho de Compras:**
    * Adicionar cursos ao carrinho.
    * Visualizar os itens no carrinho e o valor total.
    * Remover itens do carrinho.
    * Contador visual na barra de navegação que exibe a quantidade de itens no carrinho.

---

## 🛠️ Tecnologias Utilizadas

* **Back-End:** PHP 8+
* **Front-End:** HTML5, CSS3, Bootstrap 4
* **Banco de Dados:** MySQL
* **Servidor Local:** XAMPP (Apache, MySQL)
* **Extensão PHP:** PDO (PHP Data Objects) para conexão segura com o banco de dados.
* **Ícones:** Font Awesome

---

## 🚀 Como Executar o Projeto

Siga os passos abaixo para configurar e executar a aplicação em seu ambiente local.

### Pré-requisitos
* Ter o **[XAMPP](https://www.apachefriends.org/pt_br/index.html)** instalado (ou outro ambiente local que suporte Apache, MySQL e PHP).

### Passo a Passo

1.  **Clone o Repositório**
    ```bash
    git clone [https://github.com/seu-usuario/seu-repositorio.git](https://github.com/seu-usuario/seu-repositorio.git)
    ```

2.  **Mova para a Pasta `htdocs`**
    * Mova a pasta do projeto clonado para dentro do diretório `htdocs` da sua instalação do XAMPP.
    * Exemplo no Windows: `C:\xampp\htdocs\learnhub`

3.  **Inicie o Apache e o MySQL**
    * Abra o Painel de Controle do XAMPP e inicie os módulos "Apache" e "MySQL".

4.  **Crie o Banco de Dados**
    * Acesse o phpMyAdmin em seu navegador: `http://localhost/phpmyadmin/`
    * Clique em "Novo" para criar um novo banco de dados.
    * Dê o nome ao banco de dados de `cursos_db` e clique em "Criar".

5.  **Crie as Tabelas**
    * Selecione o banco `cursos_db` que você acabou de criar.
    * Vá até a aba "SQL" e cole o código abaixo para criar todas as tabelas necessárias. Clique em "Executar".

    ```sql
    -- Tabela de Usuários
    CREATE TABLE `users` (
      `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `username` varchar(50) NOT NULL,
      `email` varchar(100) NOT NULL,
      `password` varchar(255) NOT NULL,
      `role` varchar(20) NOT NULL DEFAULT 'user',
      PRIMARY KEY (`id`),
      UNIQUE KEY `username` (`username`),
      UNIQUE KEY `email` (`email`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

    -- Tabela de Cursos
    CREATE TABLE `courses` (
      `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `title` varchar(255) NOT NULL,
      `description` text NOT NULL,
      `category` varchar(100) NOT NULL,
      `price` decimal(10,2) NOT NULL,
      `image` varchar(255) DEFAULT NULL,
      `video_link` varchar(255) DEFAULT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

    -- Tabela do Carrinho de Compras
    CREATE TABLE `cart_items` (
      `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `user_id` int(11) UNSIGNED NOT NULL,
      `course_id` int(11) UNSIGNED NOT NULL,
      `quantity` int(11) UNSIGNED NOT NULL DEFAULT 1,
      `added_at` datetime NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`id`),
      KEY `user_id` (`user_id`),
      KEY `course_id` (`course_id`),
      CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
      CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ```

6.  **Crie a Pasta `uploads`**
    * Dentro da pasta principal do seu projeto (ex: `learnhub`), crie uma pasta chamada `uploads`. É aqui que as imagens de capa dos cursos serão salvas.

7.  **Acesse a Aplicação**
    * Abra seu navegador e acesse: `http://localhost/learnhub/` (substitua `learnhub` pelo nome da pasta do seu projeto).

---

## 👤 Acesso ao Sistema

### Usuário Comum
* Você pode criar um usuário comum através da página de cadastro da aplicação. Clique em "Cadastre-se aqui" na tela de login.

### Usuário Administrador
* O usuário `admin` precisa ser criado manualmente para garantir o controle de acesso.

1.  No phpMyAdmin, selecione a tabela `users`.
2.  Vá para a aba "SQL" e execute o seguinte comando para criar um usuário administrador.

    ```sql
    -- A senha é 'admin123'
    INSERT INTO `users` (`username`, `email`, `password`, `role`) VALUES
    ('admin', 'admin@learnhub.com', '$2y$10$w0Jc6B.4J52/AkpB/.2jA.1P6Q2U.35yTIlx9R5gklzXh2mDeFwLq', 'admin');
    ```
3.  Agora você pode fazer login com o usuário `admin` e a senha `admin123` para acessar as funcionalidades de administrador.
