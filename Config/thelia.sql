
# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- provider_config
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `provider_config`;

CREATE TABLE `provider_config`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `provider` VARCHAR(255) NOT NULL,
    `provider_key` VARCHAR(255),
    `secret` VARCHAR(255),
    `enabled` TINYINT(1) NOT NULL,
    `scope` VARCHAR(255),
    PRIMARY KEY (`id`),
    UNIQUE INDEX `provider_unique` (`provider`)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- hybrid_auth
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `hybrid_auth`;

CREATE TABLE `hybrid_auth`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `provider` VARCHAR(255) NOT NULL,
    `token` VARCHAR(255) NOT NULL,
    `customer_id` INTEGER NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `hybrid_auth_U_1` (`provider`, `customer_id`),
    INDEX `FI_customer_id` (`customer_id`),
    CONSTRAINT `fk_customer_id`
        FOREIGN KEY (`customer_id`)
        REFERENCES `customer` (`id`)
        ON UPDATE RESTRICT
        ON DELETE CASCADE,
    CONSTRAINT `fk_provider`
        FOREIGN KEY (`provider`)
        REFERENCES `provider_config` (`provider`)
        ON UPDATE RESTRICT
        ON DELETE CASCADE
) ENGINE=InnoDB;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
