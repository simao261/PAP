-- Criar base de dados
CREATE DATABASE IF NOT EXISTS buildlabapp;
USE buildlab;

-- Tabela de utilizadores (perfil/login)
CREATE TABLE utilizadores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    telefone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    foto_perfil VARCHAR(255), -- link da imagem ou base64
    data_registo TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de builds
CREATE TABLE builds (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_utilizador INT NOT NULL,
    nome VARCHAR(150) NOT NULL,
    descricao TEXT,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_utilizador) REFERENCES utilizadores(id) ON DELETE CASCADE
);

-- Tabela dos componentes de cada build
CREATE TABLE build_componentes (
    id_build INT NOT NULL,
    componente VARCHAR(150) NOT NULL, -- ex: "RTX 3060", "Ryzen 5 5600"
    tipo ENUM('GPU','CPU','RAM','Motherboard','Fonte','Armazenamento','Caixa') NOT NULL,
    preco DECIMAL(10,2),
    PRIMARY KEY (id_build, componente),
    FOREIGN KEY (id_build) REFERENCES builds(id) ON DELETE CASCADE
);

-- Tabela de suporte
CREATE TABLE suporte (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_utilizador INT,
    email VARCHAR(150) NOT NULL,
    mensagem TEXT NOT NULL,
    data_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_utilizador) REFERENCES utilizadores(id) ON DELETE SET NULL
);
