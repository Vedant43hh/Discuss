-- Database for the Discuss Q&A app (php-project).
-- Import in phpMyAdmin (Import tab) or run:  mysql -u root < database.sql
--
-- WARNING: this DROPS the four tables first, so importing it deletes all existing
-- users, questions and answers. That's needed because the phpdiscuss database may
-- already have a "users" table, and old accounts with plain-text passwords can't
-- log in anymore now that the app uses password_hash() / password_verify().

CREATE DATABASE IF NOT EXISTS phpdiscuss CHARACTER SET utf8mb4;
USE phpdiscuss;

DROP TABLE IF EXISTS answers, questions, category, users;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    -- WHY UNIQUE: the database itself refuses a second account with the same email.
    email VARCHAR(100) NOT NULL UNIQUE,
    -- WHY 255: password_hash() output is 60 chars today but can get longer in future PHP versions.
    password VARCHAR(255) NOT NULL,
    address VARCHAR(255)
);

CREATE TABLE category (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
);

CREATE TABLE questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    category_id INT,
    user_id INT NOT NULL
);

CREATE TABLE answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    answer TEXT NOT NULL,
    question_id INT NOT NULL,
    user_id INT NOT NULL
);

INSERT INTO category (name) VALUES
    ('php'),
    ('javascript'),
    ('mysql'),
    ('html & css');
