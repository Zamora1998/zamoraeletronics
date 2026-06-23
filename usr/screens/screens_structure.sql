-- =========================================================
-- Módulo: tv_screens — RZamora Electronics
-- Gestión de clientes y órdenes de trabajo de televisores
-- =========================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------
-- Tabla: tv_brands (Marcas de televisores)
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `tv_brands`;
CREATE TABLE `tv_brands` (
    `id`     SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nombre` VARCHAR(100) COLLATE utf8mb4_unicode_ci NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

INSERT INTO `tv_brands` (`nombre`) VALUES
    ('Samsung'),
    ('LG'),
    ('Sony'),
    ('Panasonic'),
    ('Hisense'),
    ('TCL'),
    ('Vizio'),
    ('Sharp'),
    ('Philips'),
    ('RCA'),
    ('Insignia'),
    ('Toshiba'),
    ('Daewoo'),
    ('Otra');

-- ---------------------------------------------------------
-- Tabla: tv_models (Modelos con ruta a PDF de tarjeta)
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `tv_models`;
CREATE TABLE `tv_models` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `brand_id`   SMALLINT UNSIGNED NOT NULL,
    `modelo`     VARCHAR(150) COLLATE utf8mb4_unicode_ci NOT NULL,
    `pantalla`   VARCHAR(50)  COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ej: 55 pulgadas, OLED 4K',
    `pdf_ruta`   VARCHAR(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ruta relativa al PDF del diagrama de tarjeta',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `brand_id` (`brand_id`),
    CONSTRAINT `fk_tvmodels_brand` FOREIGN KEY (`brand_id`)
        REFERENCES `tv_brands` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- Tabla: tv_parts (Partes / repuestos de televisores)
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `tv_parts`;
CREATE TABLE `tv_parts` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `brand_id`    SMALLINT UNSIGNED DEFAULT NULL COMMENT 'Marca compatible (puede ser NULL si es genérico)',
    `nombre`      VARCHAR(200) COLLATE utf8mb4_unicode_ci NOT NULL,
    `descripcion` TEXT COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `precio_crc`  DECIMAL(12, 2) DEFAULT NULL COMMENT 'Precio en colones costarricenses',
    `stock`       INT NOT NULL DEFAULT 0,
    `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `brand_id` (`brand_id`),
    CONSTRAINT `fk_tvparts_brand` FOREIGN KEY (`brand_id`)
        REFERENCES `tv_brands` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- Tabla: tv_clients (Clientes)
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `tv_clients`;
CREATE TABLE `tv_clients` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nombre`     VARCHAR(150) COLLATE utf8mb4_unicode_ci NOT NULL,
    `telefono`   VARCHAR(20)  COLLATE utf8mb4_unicode_ci NOT NULL,
    `ubicacion`  VARCHAR(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Dirección legible para mostrar',
    `latitud`    DECIMAL(10, 8) DEFAULT NULL COMMENT 'Para link de Google Maps',
    `longitud`   DECIMAL(11, 8) DEFAULT NULL COMMENT 'Para link de Google Maps',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- Tabla: work_orders (Órdenes de trabajo)
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `work_orders`;
CREATE TABLE `work_orders` (
    `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `client_id`       INT UNSIGNED NOT NULL,
    `brand_id`        SMALLINT UNSIGNED DEFAULT NULL COMMENT 'Marca del TV',
    `model_id`        INT UNSIGNED DEFAULT NULL COMMENT 'Modelo del TV (FK a tv_models)',
    `modelo_libre`    VARCHAR(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Modelo escrito manualmente si no está en catálogo',
    `pantalla_libre`  VARCHAR(80)  COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Tamaño/tipo de pantalla manual',
    `falla_reportada` TEXT COLLATE utf8mb4_unicode_ci NOT NULL,
    `costo_estimado`  DECIMAL(12, 2) NOT NULL DEFAULT 0 COMMENT 'En colones CRC',
    `abono_inicial`   DECIMAL(12, 2) NOT NULL DEFAULT 0 COMMENT 'En colones CRC',
    `estado`          ENUM('pendiente','en_reparacion','listo','entregado') NOT NULL DEFAULT 'pendiente',
    `tipo_pago`       ENUM('sinpe','efectivo','mixto','pendiente') NOT NULL DEFAULT 'pendiente',
    `firma_ruta`      VARCHAR(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ruta al PNG de la firma del cliente al revisar/entregar',
    `notas`           TEXT COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `client_id` (`client_id`),
    KEY `brand_id` (`brand_id`),
    KEY `model_id` (`model_id`),
    KEY `estado` (`estado`),
    CONSTRAINT `fk_wo_client` FOREIGN KEY (`client_id`)
        REFERENCES `tv_clients` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_wo_brand` FOREIGN KEY (`brand_id`)
        REFERENCES `tv_brands` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_wo_model` FOREIGN KEY (`model_id`)
        REFERENCES `tv_models` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- Tabla: work_order_parts (Partes usadas en una orden)
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `work_order_parts`;
CREATE TABLE `work_order_parts` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `order_id`    INT UNSIGNED NOT NULL,
    `part_id`     INT UNSIGNED NOT NULL,
    `cantidad`    INT NOT NULL DEFAULT 1,
    `precio_unit` DECIMAL(12, 2) NOT NULL COMMENT 'Precio en colones al momento de registrar',
    PRIMARY KEY (`id`),
    KEY `order_id` (`order_id`),
    KEY `part_id` (`part_id`),
    CONSTRAINT `fk_wop_order` FOREIGN KEY (`order_id`)
        REFERENCES `work_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_wop_part` FOREIGN KEY (`part_id`)
        REFERENCES `tv_parts` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------
-- Registrar rutas en la tabla routes del sistema
-- (Ajustar access_id según los roles del sistema)
-- ---------------------------------------------------------
INSERT INTO `routes` (`method`, `url`, `file`, `ispublic`, `isalluser`) VALUES
    ('get',  '/screens',          'usr/screens/ctrlScreenView.php',     0, 1),
    ('post', '/screens',          'usr/screens/ctrlScreens.php',        0, 1),
    ('get',  '/screens/edit',     'usr/screens/ctrlScreenEdit.php',     0, 1),
    ('post', '/screens/edit',     'usr/screens/ctrlScreenEdit.php',     0, 1),
    ('get',  '/screens/catalogs', 'usr/screens/ctrlScreenCatalogs.php', 0, 1),
    ('post', '/screens/catalogs', 'usr/screens/ctrlScreenCatalogs.php', 0, 1);

-- =========================================================
-- FIN del script
-- =========================================================
