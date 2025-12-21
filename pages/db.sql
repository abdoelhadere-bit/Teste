CREATE DATABASE smart_wallet;
USE smart_wallet;

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

CREATE TABLE recurring_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('income', 'expense') NOT NULL,
    montant DECIMAL(10, 2) NOT NULL,
    category VARCHAR(100) NOT NULL,
    description TEXT,
    card_id INT NOT NULL,
    day_of_month INT NOT NULL CHECK (day_of_month >= 1 AND day_of_month <= 31),
    is_active TINYINT(1) DEFAULT 1,
    last_generated DATE DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES register(id) ON DELETE CASCADE,
    FOREIGN KEY (card_id) REFERENCES cards(id) ON DELETE CASCADE
)

CREATE TABLE transfers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    card_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);


select * from recurring_transactions  

select * from transfers

DESCRIBE cards

select * from register

ALTER TABLE cards ADD is_primary TINYINT(1) DEFAULT 0;


alter table budget_limits
add column Recurring TINYINT(1) not null default 0


ALTER TABLE budget_limits DROP COLUMN Recurring;

use smart_wallet

UPDATE recurring_transactions SET last_generated = null;



