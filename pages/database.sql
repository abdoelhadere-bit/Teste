CREATE DATABASE smart_wallet;
USE tracker;

CREATE TABLE expenses(
	id INT PRIMARY KEY AUTO_INCREMENT,
    montants FLOAT NOT NULL,
    dates DATE NOT NULL,
    description TEXT NOT NULL
);

CREATE TABLE incomes(
	id INT PRIMARY KEY AUTO_INCREMENT,
    montants FLOAT NOT NULL,
    dates DATE NOT NULL,
    description TEXT NOT NULL
);