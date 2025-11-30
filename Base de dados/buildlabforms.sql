
DROP DATABASE IF EXISTS buildlab_forms;


CREATE DATABASE buildlab_forms
	CHARACTER SET utf8mb4
	COLLATE utf8mb4_general_ci;

USE buildlab_forms;

CREATE TABLE Users (
	ID				INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    Username		VARCHAR(50) UNIQUE NOT NULL,
    Password		VARCHAR(100) NOT NULL,
    Email			VARCHAR(100),
    Phone			VARCHAR(20),
    Role			ENUM('user','admin') DEFAULT 'user',
    IsActive		BOOLEAN DEFAULT TRUE
);

CREATE TABLE Builds (
	ID				INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    UserID			INT NOT NULL,
    BuildName		VARCHAR(100) NOT NULL,
    Components		TEXT,
    TotalPrice		DECIMAL(10,2),
    CreatedAt		DATE,
    FOREIGN KEY (UserID) REFERENCES Users(ID)
);


CREATE TABLE Support (
	ID				INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    UserID			INT NOT NULL,
    Subject			VARCHAR(100) NOT NULL,
    Message			TEXT NOT NULL,
    CreatedAt		DATE,
    Status          ENUM('pendente','em progresso','resolvido') DEFAULT 'pendente',
    FOREIGN KEY (UserID) REFERENCES Users(ID)
);


INSERT INTO Users (Username, Password, Email, Phone, Role, IsActive) VALUES
('admin',  'admin123', 'admin@buildlab.com', '910000000', 'admin', TRUE),
('simao', '12345', 'simao@email.com', '911111111', 'user', false),
('joana', 'abc123', 'joana@email.com', '922222222', 'user', TRUE),
('ricardo', 'qwerty', 'ricardo@email.com', '933333333', 'user', TRUE),
('miguel', 'miguel123', 'miguel@buildlab.com', '910006000', 'user', TRUE),
('simaoadmin', 'simao123', 'simaoadmin@email.com', '910338211', 'admin', TRUE),
('rodrigo', 'rodrigo27', 'rodrigo@email.com', '911333677', 'user', TRUE),
('ronaldo', 'ronaldo', 'ronaldo@email.com', '918753213', 'user', TRUE);


INSERT INTO Builds (UserID, BuildName, Components, TotalPrice, CreatedAt) VALUES
(2, 'Setup Gaming', 'RTX 3060, Ryzen 5 5600X, 16GB RAM, 1TB SSD', 1200.50, '2025-09-01'),
(3, 'Setup Trabalho', 'Intel i5, 8GB RAM, 512GB SSD, Gráfica integrada', 750.00, '2025-09-10'),
(4, 'Setup Estudante', 'Ryzen 3, 8GB RAM, 256GB SSD', 500.00, '2025-09-20');


INSERT INTO Support (UserID, Subject, Message, CreatedAt, Status) VALUES
(2, 'Problema no login', 'Não consigo aceder com a minha password.', '2025-09-21', 'pendente'),
(3, 'Erro na build', 'O sistema não validou compatibilidade entre CPU e motherboard.', '2025-09-22', 'pendente'),
(4, 'Sugestão', 'Podiam adicionar mais opções de placas gráficas.', '2025-09-23', 'pendente');


