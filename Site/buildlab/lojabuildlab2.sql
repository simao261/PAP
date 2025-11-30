-- BASE DE DADOS COMPLETA: BuildLab

CREATE DATABASE IF NOT EXISTS lojabuildlab CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE lojabuildlab;

-- UTILIZADORES
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  email VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('user','admin') NOT NULL DEFAULT 'user',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  last_login DATETIME NULL
) ENGINE=InnoDB;

-- BUILDS (produtos principais da loja) - ATUALIZADA COM TAGS
CREATE TABLE builds (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(150) NOT NULL,
  description TEXT,
  price DECIMAL(10,2) NOT NULL,
  stock INT NOT NULL DEFAULT 0,
  image_path VARCHAR(255),
  usage_tags VARCHAR(255) NULL,
  performance_level ENUM('entry','mid','high','extreme') DEFAULT 'mid',
  created_by INT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- COMPONENTES (detalhes de cada build)
CREATE TABLE components (
  id INT AUTO_INCREMENT PRIMARY KEY,
  build_id INT NOT NULL,
  category VARCHAR(50) NOT NULL,
  model VARCHAR(200) NOT NULL,
  specs TEXT NULL,
  price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  FOREIGN KEY (build_id) REFERENCES builds(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ENCOMENDAS
CREATE TABLE orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  total DECIMAL(10,2) NOT NULL,
  status ENUM('pending','paid','shipped','cancelled') DEFAULT 'pending',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- DETALHES DE ENCOMENDAS
CREATE TABLE order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  build_id INT NOT NULL,
  quantity INT NOT NULL DEFAULT 1,
  price_each DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (build_id) REFERENCES builds(id)
) ENGINE=InnoDB;

-- MENSAGENS DE SUPORTE
CREATE TABLE support_messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  subject VARCHAR(200),
  message TEXT NOT NULL,
  status ENUM('open','answered','closed') DEFAULT 'open',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- PEDIDOS DE ORÇAMENTO PERSONALIZADO
CREATE TABLE build_requests (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  cpu_preference VARCHAR(150) NULL,
  gpu_preference VARCHAR(150) NULL,
  ram_preference VARCHAR(150) NULL,
  storage_preference VARCHAR(150) NULL,
  budget DECIMAL(10,2) NULL,
  notes TEXT NULL,
  status ENUM('pending','viewed','responded','closed') DEFAULT 'pending',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- NOVA TABELA: NOTIFICAÇÕES
CREATE TABLE notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  title VARCHAR(200) NOT NULL,
  message TEXT NOT NULL,
  type ENUM('info','success','warning','error') DEFAULT 'info',
  is_read BOOLEAN DEFAULT FALSE,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- NOVA TABELA: COMPONENTES INDIVIDUAIS (para o simulador)
CREATE TABLE individual_components (
  id INT AUTO_INCREMENT PRIMARY KEY,
  category VARCHAR(50) NOT NULL,
  name VARCHAR(200) NOT NULL,
  brand VARCHAR(100),
  model VARCHAR(200),
  specs TEXT,
  price DECIMAL(10,2) NOT NULL,
  image_path VARCHAR(255),
  compatibility_notes TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- CONTA ADMIN (com password hasheada)
INSERT INTO users (username, email, password_hash, role)
VALUES ('admin', 'admin@buildlab.pt', '$2y$10$CZLBEtbsJdKdyMI7j6A9bOGmrgR.c35xDzfF5scrz8njvZVAIbcKq', 'admin');
-- password: 12345

-- CONTA DE UTILIZADOR DE EXEMPLO
INSERT INTO users (username, email, password_hash, role)
VALUES ('joaopedro', 'joao@buildlab.pt', '$2y$10$dpvVJ.y0ShDF/XN4ZXlXFe5Oucq6MI3yyr0cCjJjzXDcG5/T4GvAa', 'user');
-- password: 12345

-- BUILDS PRÉ-DEFINIDAS COM TAGS DE USO
INSERT INTO builds (title, description, price, stock, image_path, usage_tags, performance_level, created_by)
VALUES
('Build Gamer RTX', 'PC Gaming de alto desempenho com RTX 4070 e Ryzen 7.', 1499.99, 5, 'images/build1.jpg', 'gaming,high-performance', 'high', 1),
('Build Intermédia', 'Ideal para trabalho e jogos médios. Equilibrada e eficiente.', 899.99, 10, 'images/build2.jpg', 'gaming,work,study', 'mid', 1),
('Build Económica', 'Computador de entrada com excelente custo-benefício.', 549.99, 12, 'images/build3.jpg', 'study,work,budget', 'entry', 1),
('Build Profissional', 'Desempenho extremo para criadores de conteúdo e edição de vídeo.', 1999.99, 3, 'images/build4.jpg', 'work,professional,content-creation', 'extreme', 1);

-- COMPONENTES DAS BUILDS
INSERT INTO components (build_id, category, model, specs, price) VALUES
-- Build Gamer RTX
(1, 'Processador', 'AMD Ryzen 7 7800X3D', '8 núcleos, 16 threads, 5.0 GHz', 399.99),
(1, 'Placa Gráfica', 'NVIDIA RTX 4070 12GB', 'DLSS 3, Ray Tracing, 12GB GDDR6X', 699.99),
(1, 'Memória RAM', 'Corsair Vengeance 32GB DDR5 6000MHz', '2x16GB', 189.99),
(1, 'Armazenamento', 'Samsung 980 PRO 1TB NVMe', 'Leitura 7000MB/s', 99.99),

-- Build Intermédia
(2, 'Processador', 'Intel Core i5-13400F', '10 núcleos, 16 threads', 239.99),
(2, 'Placa Gráfica', 'NVIDIA RTX 3060 12GB', 'Ray Tracing, DLSS', 349.99),
(2, 'Memória RAM', 'Kingston Fury 16GB DDR4 3200MHz', '2x8GB', 69.99),
(2, 'Armazenamento', 'Crucial P3 1TB NVMe', 'Leitura 3500MB/s', 59.99),

-- Build Económica
(3, 'Processador', 'AMD Ryzen 5 5600G', '6 núcleos, GPU integrada Vega 7', 159.99),
(3, 'Placa Gráfica', 'Integrada Vega 7', 'Integrada no CPU', 0.00),
(3, 'Memória RAM', 'Crucial 16GB DDR4 3200MHz', '2x8GB', 59.99),
(3, 'Armazenamento', 'Kingston A2000 500GB NVMe', 'Leitura 2200MB/s', 39.99),

-- Build Profissional
(4, 'Processador', 'Intel Core i9-14900K', '24 núcleos, 32 threads', 699.99),
(4, 'Placa Gráfica', 'NVIDIA RTX 4090 24GB', 'GDDR6X, Ray Tracing, DLSS 3.5', 1799.99),
(4, 'Memória RAM', 'G.Skill Trident Z5 RGB 64GB DDR5 6000MHz', '2x32GB', 299.99),
(4, 'Armazenamento', 'Samsung 990 PRO 2TB NVMe', 'Leitura 7450MB/s', 169.99);

-- COMPONENTES INDIVIDUAIS PARA O SIMULADOR
INSERT INTO individual_components (category, name, brand, model, specs, price, image_path, compatibility_notes) VALUES
-- CPUs
('CPU', 'AMD Ryzen 7 7800X3D', 'AMD', '7800X3D', '8 núcleos, 16 threads, 5.0 GHz, Socket AM5', 399.99, 'images/cpu/ryzen7-7800x3d.jpg', 'Socket AM5, DDR5, PCIe 5.0'),
('CPU', 'Intel Core i5-13400F', 'Intel', 'i5-13400F', '10 núcleos, 16 threads, Socket LGA1700', 239.99, 'images/cpu/i5-13400f.jpg', 'Socket LGA1700, DDR4/DDR5, PCIe 5.0'),
('CPU', 'AMD Ryzen 5 5600G', 'AMD', '5600G', '6 núcleos, 12 threads, GPU integrada, Socket AM4', 159.99, 'images/cpu/ryzen5-5600g.jpg', 'Socket AM4, DDR4, PCIe 4.0'),

-- GPUs
('GPU', 'NVIDIA RTX 4070 12GB', 'NVIDIA', 'RTX 4070', '12GB GDDR6X, Ray Tracing, DLSS 3', 699.99, 'images/gpu/rtx4070.jpg', 'PCIe 4.0, 8-pin power'),
('GPU', 'NVIDIA RTX 3060 12GB', 'NVIDIA', 'RTX 3060', '12GB GDDR6, Ray Tracing, DLSS', 349.99, 'images/gpu/rtx3060.jpg', 'PCIe 4.0, 8-pin power'),
('GPU', 'NVIDIA RTX 4090 24GB', 'NVIDIA', 'RTX 4090', '24GB GDDR6X, Ray Tracing, DLSS 3.5', 1799.99, 'images/gpu/rtx4090.jpg', 'PCIe 4.0, 16-pin power'),

-- RAM
('RAM', 'Corsair Vengeance 32GB DDR5', 'Corsair', 'Vengeance DDR5', '32GB (2x16GB) 6000MHz', 189.99, 'images/ram/corsair-vengeance-ddr5.jpg', 'DDR5, 6000MHz, CL36'),
('RAM', 'Kingston Fury 16GB DDR4', 'Kingston', 'Fury Beast DDR4', '16GB (2x8GB) 3200MHz', 69.99, 'images/ram/kingston-fury.jpg', 'DDR4, 3200MHz, CL16'),
('RAM', 'G.Skill Trident Z5 RGB 64GB', 'G.Skill', 'Trident Z5 RGB', '64GB (2x32GB) 6000MHz RGB', 299.99, 'images/ram/gskill-trident.jpg', 'DDR5, 6000MHz, RGB'),

-- Storage
('Storage', 'Samsung 980 PRO 1TB', 'Samsung', '980 PRO', '1TB NVMe PCIe 4.0, 7000MB/s', 99.99, 'images/storage/samsung-980pro.jpg', 'M.2 NVMe, PCIe 4.0'),
('Storage', 'Crucial P3 1TB', 'Crucial', 'P3 NVMe', '1TB NVMe PCIe 3.0, 3500MB/s', 59.99, 'images/storage/crucial-p3.jpg', 'M.2 NVMe, PCIe 3.0'),
('Storage', 'Samsung 990 PRO 2TB', 'Samsung', '990 PRO', '2TB NVMe PCIe 4.0, 7450MB/s', 169.99, 'images/storage/samsung-990pro.jpg', 'M.2 NVMe, PCIe 4.0');

-- NOTIFICAÇÃO DE TESTE
INSERT INTO notifications (user_id, title, message, type)
VALUES (1, 'Bem-vindo ao BuildLab!', 'O sistema de notificações está funcionando corretamente.', 'success');
