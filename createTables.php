<?php
include "bootstrap.php";

$dbh->query("
    ALTER TABLE ". $_ENV['TABLE_PREFIX'] ."sportids
    ADD COLUMN name_fr VARCHAR(255) AFTER name_hr,
    ADD COLUMN name_it VARCHAR(255) AFTER name_hr,
    ADD COLUMN name_ru VARCHAR(255) AFTER name_hr,
    ADD INDEX (name_de),
    ADD INDEX (name_en),
    ADD INDEX (name_tr),
    ADD INDEX (name_hr),
    ADD INDEX (name_ru),
    ADD INDEX (name_it),
    ADD INDEX (name_fr)
");


$dbh->query("
    CREATE TABLE IF NOT EXISTS ". $_ENV['TABLE_PREFIX'] ."se_categories(
    id INT AUTO_INCREMENT,
    sport_id INT,
    FOREIGN KEY (sport_id) REFERENCES ". $_ENV['TABLE_PREFIX'] ."sportids (id),
    name_de VARCHAR(255),
    name_en VARCHAR(255),
    name_tr VARCHAR(255),
    name_ru VARCHAR(255),
    name_it VARCHAR(255),
    name_fr VARCHAR(255),
    rank INT,
    active INT,
    PRIMARY KEY (id),
    INDEX (name_de),
    INDEX (name_en),
    INDEX (name_tr),
    INDEX (name_ru),
    INDEX (name_it),
    INDEX (name_fr)
)");

$dbh->query("
    CREATE TABLE IF NOT EXISTS ". $_ENV['TABLE_PREFIX'] ."se_tournaments(
    id INT AUTO_INCREMENT,
    unique_id INT,
    category_id INT NOT NULL,
    FOREIGN KEY (category_id) REFERENCES ". $_ENV['TABLE_PREFIX'] ."se_categories (id),
    name_de VARCHAR(255),
    name_en VARCHAR(255),
    name_tr VARCHAR(255),
    name_ru VARCHAR(255),
    name_it VARCHAR(255),
    name_fr VARCHAR(255),
    rank INT,
    active INT,
    PRIMARY KEY (id),
    INDEX (name_de),
    INDEX (name_en),
    INDEX (name_tr),
    INDEX (name_ru),
    INDEX (name_it),
    INDEX (name_fr)
)");

$dbh->query("
    CREATE TABLE IF NOT EXISTS ". $_ENV['TABLE_PREFIX'] ."se_teams(
    id INT AUTO_INCREMENT,
    name_de VARCHAR(255),
    name_en VARCHAR(255),
    name_tr VARCHAR(255),
    name_ru VARCHAR(255),
    name_it VARCHAR(255),
    name_fr VARCHAR(255),
    rank INT,
    active INT,
    PRIMARY KEY (id),
    INDEX (name_de),
    INDEX (name_en),
    INDEX (name_tr),
    INDEX (name_ru),
    INDEX (name_it),
    INDEX (name_fr)
)");

$dbh->query("
    CREATE TABLE IF NOT EXISTS ". $_ENV['TABLE_PREFIX'] ."se_tournaments_teams(
    tournament_id INT(11),
    team_id INT(11),
    PRIMARY KEY (tournament_id, team_id)
)");