
# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

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
    INDEX `FI_customer_id` (`customer_id`),
    CONSTRAINT `fk_customer_id`
        FOREIGN KEY (`customer_id`)
        REFERENCES `customer` (`id`)
        ON UPDATE RESTRICT
        ON DELETE CASCADE
) ENGINE=InnoDB;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
