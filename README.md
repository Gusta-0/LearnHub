# LearnHub - Plataforma de Cursos Online

![Dashboard da Plataforma LearnHub](./img-readme/img-logo.png)

## ✒️ Sobre o Projeto

LearnHub é uma aplicação web desenvolvida em PHP puro, que simula uma plataforma de e-commerce para a venda de cursos online. O projeto foi construído com foco em funcionalidades essenciais de um sistema web moderno, incluindo autenticação de usuários, controle de acesso baseado em papéis (Admin e Usuário), gerenciamento completo de cursos (CRUD) e um sistema de carrinho de compras dinâmico com interações via AJAX.

O design foi cuidadosamente elaborado para ser moderno e responsivo, utilizando fundos com gradientes, cards de alto contraste e uma interface de usuário intuitiva para proporcionar uma ótima experiência ao usuário.

---

## ✨ Funcionalidades Principais

### Funcionalidades Gerais
* **Design Responsivo:** Interface adaptável para desktops, tablets e smartphones.
* **Sistema de Autenticação:** Páginas de Login e Cadastro com validação de dados e senhas seguras (hash).
* **Página de Cursos Dinâmica:** Visualização de todos os cursos disponíveis com um layout em cards.
* **Busca de Cursos:** Campo de pesquisa funcional para filtrar cursos por título ou descrição em tempo real.

### Funcionalidades do Administrador (`admin`)
* Acesso a um painel de controle com funcionalidades exclusivas.
* **Gerenciamento de Cursos (CRUD):**
    * **Criar:** Adicionar novos cursos à plataforma através de um formulário dedicado.
    * **Ler:** Visualizar todos os cursos cadastrados.
    * **Atualizar:** Editar informações de cursos existentes.
    * **Deletar:** Remover cursos da plataforma com confirmação.
* **Upload de Imagens:** Sistema de upload para as capas dos cursos, com sanitização de nome de arquivo para maior segurança.

### Funcionalidades do Usuário Comum (`user`)
* Visualizar todos os cursos disponíveis e pesquisar por eles.
* **Carrinho de Compras com AJAX:**
    * Adicionar cursos ao carrinho sem recarregar a página.
    * Receber feedback visual instantâneo (notificações) ao adicionar itens.
    * Visualizar os itens no carrinho e o valor total.
    * Remover itens individuais do carrinho.
    * Contador na barra de navegação que é atualizado dinamicamente.
    * **Finalização de Compra Simulada:** Limpa o carrinho e exibe uma mensagem de sucesso, proporcionando uma experiência de usuário completa.

---

## 🛠️ Tecnologias Utilizadas

* **Back-End:** PHP 8+ (com PDO para interações seguras com o banco de dados)
* **Front-End:** HTML5, CSS3, JavaScript, jQuery (para chamadas AJAX)
* **Framework CSS:** Bootstrap 4
* **Banco de Dados:** MySQL
* **Servidor Local:** XAMPP (Apache, MySQL)
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

5.  **Importe o Banco de Dados**
    * Selecione o banco `cursos_db` que você acabou de criar.
    * Vá até a aba "SQL" e cole o código abaixo para criar e popular as tabelas necessárias. Clique em "Executar".

    ```sql
    -- Estrutura da tabela `users`
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

    -- Inserindo o usuário administrador
    -- Senha: admin123
    INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`) VALUES
    (1, 'admin', 'admin@learnhub.com', '$2y$10$w0Jc6B.4J52/AkpB/.2jA.1P6Q2U.35yTIlx9R5gklzXh2mDeFwLq', 'admin');

    -- Estrutura da tabela `courses`
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

    -- Estrutura da tabela `cart_items`
    CREATE TABLE `cart_items` (
      `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `user_id` int(11) UNSIGNED NOT NULL,
      `course_id` int(11) UNSIGNED NOT NULL,
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

* **Usuário Administrador:** Já vem criado com o script SQL acima.
    * **Usuário:** `admin`
    * **Senha:** `admin123`

* **Usuário Comum:** Você pode criar um novo usuário através da página de cadastro da aplicação. Clique em "Cadastre-se aqui" na tela de login.

---

## 📈 Melhorias Futuras (To-Do)

Este projeto serve como uma excelente base, mas pode ser expandido com novas funcionalidades:

* [ ] **Integração com Gateway de Pagamento:** Implementar um sistema de pagamento real (Stripe, Mercado Pago, etc.).
* [ ] **Painel "Meus Cursos":** Criar uma área onde o usuário possa ver todos os cursos que já comprou.
* [ ] **Página de Detalhes do Curso:** Uma página dedicada para cada curso com mais informações, vídeos e avaliações.
* [ ] **Sistema de Avaliações:** Permitir que usuários avaliem e comentem nos cursos que adquiriram.
* [ ] **Recuperação de Senha:** Implementar a funcionalidade "Esqueci minha senha".
* [ ] **Paginação:** Adicionar paginação na lista de cursos para melhor performance com um grande volume de dados.

---

## 📝 Licença

Este projeto está sob a licença MIT. Você pode ver mais detalhes no arquivo LICENSE do repositório.

---

## 👨‍💻 Autor

Feito com ❤️ por **Gustavo Alves**

* **[LinkedIn](https://www.linkedin.com/in/gustavo-alves-8300b2302/)**
* **[GitHub](https://github.com/Gusta-0)**
* **[Email](gabs.principal.2005@gmail.com)**
  