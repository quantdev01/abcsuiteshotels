<?php

/**
 *  Please read the terms of the CLUF license attached to this module(cf "licences" folder)
 *
 * @author    Línea Gráfica E.C.E. S.L.
 * @copyright Lineagrafica.es - Línea Gráfica E.C.E. S.L. all rights reserved.
 * @license   https://www.lineagrafica.es/licenses/license_en.pdf
 *            https://www.lineagrafica.es/licenses/license_es.pdf
 *            https://www.lineagrafica.es/licenses/license_fr.pdf
 */

$sql[] = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."pluglin_plan` (
        `id_pluglin_plan` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    	`max_language` INT NOT NULL,
		`max_words` INT NOT NULL,
		`price` DECIMAL(10,2) NOT NULL
		) ENGINE = "._MYSQL_ENGINE_." CHARACTER SET utf8 COLLATE utf8_general_ci;";


$sql[] = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."pluglin_content` (
        `id_pluglin_content` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `id_pluglin` INT UNSIGNED NOT NULL,
    	`type` VARCHAR(30) NOT NULL,
		`id_content` INT NOT NULL,
		`read` INT NOT NULL,
		`send` INT NOT NULL,
		`package` INT NOT NULL,
		`status` VARCHAR(35),
		`data_json` TEXT,
		`date_add` DATETIME,
		`date_update` DATETIME,
		UNIQUE KEY type_id (`type`,`id_content`) 
		) ENGINE = "._MYSQL_ENGINE_." CHARACTER SET utf8 COLLATE utf8_general_ci;";

$sql[] = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."pluglin_download` (
        `id_pluglin_datapack` INT UNSIGNED NOT NULL PRIMARY KEY,
    	`status` VARCHAR(30) NOT NULL,
		`url` VARCHAR(255) 
		) ENGINE = "._MYSQL_ENGINE_." CHARACTER SET utf8 COLLATE utf8_general_ci;";


$sql[] = "INSERT INTO `"._DB_PREFIX_."pluglin_plan` 
	(`max_language`, `max_words`, `price`) VALUES ('3', '10000', '0');";
$sql[] = "INSERT INTO `"._DB_PREFIX_."pluglin_plan` 
	(`max_language`, `max_words`, `price`) VALUES ('0', '50000', '9.99');";
$sql[] = "INSERT INTO `"._DB_PREFIX_."pluglin_plan` 
	(`max_language`, `max_words`, `price`) VALUES ('0', '100000', '19.99');";
$sql[] = "INSERT INTO `"._DB_PREFIX_."pluglin_plan` 
	(`max_language`, `max_words`, `price`) VALUES ('0', '250000', '49.99');";
$sql[] = "INSERT INTO `"._DB_PREFIX_."pluglin_plan` 
	(`max_language`, `max_words`, `price`) VALUES ('0', '500000', '89.99');";
$sql[] = "INSERT INTO `"._DB_PREFIX_."pluglin_plan` 
	(`max_language`, `max_words`, `price`) VALUES ('0', '1000000', '119.99');";


foreach ($sql as $query) {
    Db::getInstance()->execute($query);
}
