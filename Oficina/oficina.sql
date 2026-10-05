-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -----------------------------------------------------
-- Schema oficina
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema oficina
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `oficina` DEFAULT CHARACTER SET utf8 ;
USE `oficina` ;

-- -----------------------------------------------------
-- Table `oficina`.`users`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `oficina`.`users` (
  `id` BIGINT NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('ADM', 'CLI') NOT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `email_UNIQUE` (`email` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `oficina`.`categorias`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `oficina`.`categorias` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(255) NOT NULL,
  `descricao` TEXT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `oficina`.`produtos`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `oficina`.`produtos` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(255) NOT NULL,
  `descricao` TEXT NULL,
  `estoque` INT NOT NULL,
  `preco` DECIMAL(10,2) NOT NULL,
  `categoria_id` BIGINT NOT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_produtos_categorias1_idx` (`categoria_id` ASC),
  CONSTRAINT `fk_produtos_categorias1`
    FOREIGN KEY (`categoria_id`)
    REFERENCES `oficina`.`categorias` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `oficina`.`veiculos`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `oficina`.`veiculos` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `placa` VARCHAR(10) NOT NULL,
  `marca` VARCHAR(100) NULL,
  `modelo` VARCHAR(100) NULL,
  `ano` INT NULL,
  `user_id` BIGINT NOT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `placa_UNIQUE` (`placa` ASC),
  INDEX `fk_veiculos_users1_idx` (`user_id` ASC),
  CONSTRAINT `fk_veiculos_users1`
    FOREIGN KEY (`user_id`)
    REFERENCES `oficina`.`users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `oficina`.`enderecos`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `oficina`.`enderecos` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `endereco` VARCHAR(255) NOT NULL,
  `cidade` VARCHAR(255) NOT NULL,
  `estado` VARCHAR(255) NOT NULL,
  `cep` VARCHAR(20) NOT NULL,
  `user_id` BIGINT NOT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_enderecos_users_idx` (`user_id` ASC),
  CONSTRAINT `fk_enderecos_users`
    FOREIGN KEY (`user_id`)
    REFERENCES `oficina`.`users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `oficina`.`pedidos`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `oficina`.`pedidos` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `status` ENUM('ABERTO', 'PAGO', 'ENVIADO', 'CONCLUIDO', 'CANCELADO') NOT NULL,
  `total` DECIMAL(10,2) NOT NULL,
  `user_id` BIGINT NOT NULL,
  `veiculo_id` BIGINT NOT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_pedidos_users1_idx` (`user_id` ASC),
  INDEX `fk_pedidos_veiculos1_idx` (`veiculo_id` ASC),
  CONSTRAINT `fk_pedidos_users1`
    FOREIGN KEY (`user_id`)
    REFERENCES `oficina`.`users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_pedidos_veiculos1`
    FOREIGN KEY (`veiculo_id`)
    REFERENCES `oficina`.`veiculos` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `oficina`.`itens_pedido`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `oficina`.`itens_pedido` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `quantidade` INT NOT NULL,
  `preco` DECIMAL(10,2) NOT NULL,
  `pedido_id` BIGINT NOT NULL,
  `produto_id` BIGINT NOT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_itens_pedidos_pedidos1_idx` (`pedido_id` ASC),
  INDEX `fk_itens_pedidos_produtos1_idx` (`produto_id` ASC),
  CONSTRAINT `fk_itens_pedidos_pedidos1`
    FOREIGN KEY (`pedido_id`)
    REFERENCES `oficina`.`pedidos` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_itens_pedidos_produtos1`
    FOREIGN KEY (`produto_id`)
    REFERENCES `oficina`.`produtos` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
