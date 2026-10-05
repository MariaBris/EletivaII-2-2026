-- Criacao inicial do banco da oficina.
-- Execute o arquivo inteiro no MySQL Workbench.
-- Pode ser repetido: nao apaga tabelas nem registros.
-- IF NOT EXISTS nao atualiza a estrutura de tabelas existentes.

CREATE DATABASE IF NOT EXISTS `oficina`
  DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `oficina`;

CREATE TABLE IF NOT EXISTS `users` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `email_verified_at` TIMESTAMP NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('ADM', 'CLI') NOT NULL DEFAULT 'CLI',
  `remember_token` VARCHAR(100) NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `categorias` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(255) NOT NULL,
  `descricao` TEXT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `produtos` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(255) NOT NULL,
  `descricao` TEXT NULL,
  `estoque` INT NOT NULL,
  `preco` DECIMAL(10,2) NOT NULL,
  `imagem` TEXT NULL,
  `categoria_id` BIGINT NOT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  KEY `fk_produtos_categorias1_idx` (`categoria_id`),
  CONSTRAINT `fk_produtos_categorias1` FOREIGN KEY (`categoria_id`)
    REFERENCES `categorias` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `veiculos` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `placa` VARCHAR(10) NOT NULL,
  `marca` VARCHAR(100) NULL,
  `modelo` VARCHAR(100) NULL,
  `ano` INT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `placa_UNIQUE` (`placa`),
  KEY `fk_veiculos_users1_idx` (`user_id`),
  CONSTRAINT `fk_veiculos_users1` FOREIGN KEY (`user_id`)
    REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `enderecos` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `endereco` VARCHAR(255) NOT NULL,
  `cidade` VARCHAR(255) NOT NULL,
  `estado` VARCHAR(255) NOT NULL,
  `cep` VARCHAR(20) NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  KEY `fk_enderecos_users_idx` (`user_id`),
  CONSTRAINT `fk_enderecos_users` FOREIGN KEY (`user_id`)
    REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `pedidos` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `status` ENUM('ABERTO', 'PAGO', 'ENVIADO', 'CONCLUIDO', 'CANCELADO') NOT NULL,
  `total` DECIMAL(10,2) NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `veiculo_id` BIGINT NOT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  KEY `fk_pedidos_users1_idx` (`user_id`),
  KEY `fk_pedidos_veiculos1_idx` (`veiculo_id`),
  CONSTRAINT `fk_pedidos_users1` FOREIGN KEY (`user_id`)
    REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_pedidos_veiculos1` FOREIGN KEY (`veiculo_id`)
    REFERENCES `veiculos` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `itens_pedido` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `quantidade` INT NOT NULL,
  `preco` DECIMAL(10,2) NOT NULL,
  `pedido_id` BIGINT NOT NULL,
  `produto_id` BIGINT NOT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  KEY `fk_itens_pedidos_pedidos1_idx` (`pedido_id`),
  KEY `fk_itens_pedidos_produtos1_idx` (`produto_id`),
  CONSTRAINT `fk_itens_pedidos_pedidos1` FOREIGN KEY (`pedido_id`)
    REFERENCES `pedidos` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_itens_pedidos_produtos1` FOREIGN KEY (`produto_id`)
    REFERENCES `produtos` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB;

-- Campos utilizados pelo projeto, inclusive em bancos ja existentes.
SET @ajuste = IF(
  EXISTS(SELECT 1 FROM information_schema.columns
    WHERE table_schema = 'oficina' AND table_name = 'users' AND column_name = 'role'),
  'SELECT 1',
  'ALTER TABLE `users` ADD COLUMN `role` ENUM(''ADM'', ''CLI'') NOT NULL DEFAULT ''CLI'''
);
PREPARE ajuste_stmt FROM @ajuste;
EXECUTE ajuste_stmt;
DEALLOCATE PREPARE ajuste_stmt;
SET @ajuste = IF(
  EXISTS(SELECT 1 FROM information_schema.columns
    WHERE table_schema = 'oficina' AND table_name = 'produtos' AND column_name = 'imagem'),
  'SELECT 1',
  'ALTER TABLE `produtos` ADD COLUMN `imagem` TEXT NULL'
);
PREPARE ajuste_stmt FROM @ajuste;
EXECUTE ajuste_stmt;
DEALLOCATE PREPARE ajuste_stmt;

-- Dados ficticios. Senha dos usuarios de exemplo: Oficina123!
START TRANSACTION;
INSERT INTO `users` (name, email, password, role, created_at, updated_at)
SELECT 'Administrador Exemplo', 'admin@example.com', '$2b$12$IU9TzZVdT8pLiIQNOXpgzOq3fScxgjx8IITWV3qx3msjb5dapf3Jy', 'ADM', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `users` WHERE email = 'admin@example.com');
INSERT INTO `users` (name, email, password, role, created_at, updated_at)
SELECT 'Ana Exemplo', 'ana@example.com', '$2b$12$IU9TzZVdT8pLiIQNOXpgzOq3fScxgjx8IITWV3qx3msjb5dapf3Jy', 'CLI', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `users` WHERE email = 'ana@example.com');
INSERT INTO `users` (name, email, password, role, created_at, updated_at)
SELECT 'Carlos Exemplo', 'carlos@example.com', '$2b$12$IU9TzZVdT8pLiIQNOXpgzOq3fScxgjx8IITWV3qx3msjb5dapf3Jy', 'CLI', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `users` WHERE email = 'carlos@example.com');
SET @ana = (SELECT id FROM users WHERE email = 'ana@example.com');
SET @carlos = (SELECT id FROM users WHERE email = 'carlos@example.com');
INSERT INTO `categorias` (nome, descricao, created_at, updated_at)
SELECT 'Lubrificantes', 'Oleos e fluidos automotivos', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `categorias` WHERE nome = 'Lubrificantes');
SET @lub = (SELECT MIN(id) FROM categorias WHERE nome = 'Lubrificantes');
INSERT INTO `categorias` (nome, descricao, created_at, updated_at)
SELECT 'Filtros', 'Filtros para manutencao', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `categorias` WHERE nome = 'Filtros');
SET @fil = (SELECT MIN(id) FROM categorias WHERE nome = 'Filtros');
INSERT INTO `categorias` (nome, descricao, created_at, updated_at)
SELECT 'Freios', 'Pecas do sistema de freios', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `categorias` WHERE nome = 'Freios');
SET @fre = (SELECT MIN(id) FROM categorias WHERE nome = 'Freios');
INSERT INTO `produtos` (nome, descricao, estoque, preco, categoria_id, created_at, updated_at)
SELECT 'Oleo de motor 5W30', 'Frasco de 1 litro', 30, 45.00, @lub, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `produtos` WHERE nome = 'Oleo de motor 5W30' AND categoria_id = @lub);
INSERT INTO `produtos` (nome, descricao, estoque, preco, categoria_id, created_at, updated_at)
SELECT 'Filtro de oleo', 'Filtro de oleo de exemplo', 20, 25.00, @fil, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `produtos` WHERE nome = 'Filtro de oleo' AND categoria_id = @fil);
INSERT INTO `produtos` (nome, descricao, estoque, preco, categoria_id, created_at, updated_at)
SELECT 'Filtro de ar', 'Filtro de ar de exemplo', 15, 40.00, @fil, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `produtos` WHERE nome = 'Filtro de ar' AND categoria_id = @fil);
INSERT INTO `produtos` (nome, descricao, estoque, preco, categoria_id, created_at, updated_at)
SELECT 'Pastilha de freio', 'Jogo de pastilhas de exemplo', 10, 120.00, @fre, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `produtos` WHERE nome = 'Pastilha de freio' AND categoria_id = @fre);
INSERT INTO `veiculos` (placa, marca, modelo, ano, user_id, created_at, updated_at)
SELECT 'ABC1D23', 'Fiat', 'Uno', 2014, @ana, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `veiculos` WHERE placa = 'ABC1D23');
INSERT INTO `veiculos` (placa, marca, modelo, ano, user_id, created_at, updated_at)
SELECT 'XYZ4E56', 'Volkswagen', 'Gol', 2018, @carlos, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `veiculos` WHERE placa = 'XYZ4E56');
INSERT INTO `enderecos` (endereco, cidade, estado, cep, user_id, created_at, updated_at)
SELECT 'Rua Exemplo, 100', 'Cidade Exemplo', 'SP', '00000-000', @ana, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `enderecos` WHERE user_id = @ana AND endereco = 'Rua Exemplo, 100');
INSERT INTO `enderecos` (endereco, cidade, estado, cep, user_id, created_at, updated_at)
SELECT 'Rua Exemplo, 200', 'Cidade Exemplo', 'SP', '00000-000', @carlos, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `enderecos` WHERE user_id = @carlos AND endereco = 'Rua Exemplo, 200');
SET @veiculo = (SELECT id FROM veiculos WHERE placa = 'ABC1D23' AND user_id = @ana);
INSERT INTO `pedidos` (status, total, user_id, veiculo_id, created_at, updated_at)
SELECT 'ABERTO', 205.00, @ana, @veiculo, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `pedidos` WHERE user_id = @ana AND veiculo_id = @veiculo AND status = 'ABERTO' AND total = 205.00);
SET @pedido = (SELECT MIN(id) FROM pedidos WHERE user_id = @ana AND veiculo_id = @veiculo AND status = 'ABERTO' AND total = 205.00);
SET @produto = (SELECT MIN(id) FROM produtos WHERE nome = 'Oleo de motor 5W30' AND categoria_id = @lub);
INSERT INTO `itens_pedido` (quantidade, preco, pedido_id, produto_id, created_at, updated_at)
SELECT 4, 45.00, @pedido, @produto, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `itens_pedido` WHERE pedido_id = @pedido AND produto_id = @produto);
SET @produto = (SELECT MIN(id) FROM produtos WHERE nome = 'Filtro de oleo' AND categoria_id = @fil);
INSERT INTO `itens_pedido` (quantidade, preco, pedido_id, produto_id, created_at, updated_at)
SELECT 1, 25.00, @pedido, @produto, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `itens_pedido` WHERE pedido_id = @pedido AND produto_id = @produto);
COMMIT;

SELECT 'Dados de exemplo inseridos.' AS resultado;
