-- Create Branches table
CREATE TABLE IF NOT EXISTS branches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    branch_name VARCHAR(50) NOT NULL,
    branch_id VARCHAR(4) GENERATED ALWAYS AS (
        CONCAT(
            UPPER(SUBSTRING(branch_name, 1, 2)),
            POSITION(UPPER(SUBSTRING(branch_name, 1, 1)) IN 'ABCDEFGHIJKLMNOPQRSTUVWXYZ') - 1
        )
    ) STORED UNIQUE
);

-- Create Master table
CREATE TABLE IF NOT EXISTS master (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(50) NOT NULL
);

-- Create Subjects table with branch_id as foreign key
CREATE TABLE IF NOT EXISTS subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_name VARCHAR(50) NOT NULL,
    fees DECIMAL(10, 2) NOT NULL,
    tax DECIMAL(5, 2) NOT NULL,
    branch_id VARCHAR(4) NOT NULL,
    FOREIGN KEY (branch_id) REFERENCES branches(branch_id)
);

-- Create Subjects_Branches table
CREATE TABLE IF NOT EXISTS subject_branches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_id INT NOT NULL,
    branch_id VARCHAR(4) NOT NULL,
    FOREIGN KEY (subject_id) REFERENCES subjects(id),
    FOREIGN KEY (branch_id) REFERENCES branches(branch_id)
);

-- Create Branch Admins table
CREATE TABLE IF NOT EXISTS branch_admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    branch_id VARCHAR(4) NOT NULL,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(50) NOT NULL,
    FOREIGN KEY (branch_id) REFERENCES branches(branch_id)
);

-- Create Branch Students table
CREATE TABLE IF NOT EXISTS branch_students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    branch_id VARCHAR(4) NOT NULL,
    student_name VARCHAR(100) NOT NULL,
    contact VARCHAR(20) NOT NULL,
    contact2 VARCHAR(20) NOT NULL,
    address VARCHAR(255) NOT NULL,
    parent_name VARCHAR(100) NOT NULL,
    UNIQUE KEY (branch_id, student_name, parent_name, contact)
);

-- Create Invoices table
CREATE TABLE IF NOT EXISTS invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    branch_id VARCHAR(4) NOT NULL,
    student_id INT NOT NULL,
    invoice_number VARCHAR(20) NOT NULL,
    grand_total DECIMAL(10, 2) NOT NULL,
    invoice_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(branch_id),
    FOREIGN KEY (student_id) REFERENCES branch_students(id)
);

-- Add new columns to the Invoices table
ALTER TABLE invoices
ADD COLUMN subject_name JSON,
ADD COLUMN student_name VARCHAR(100),
ADD COLUMN parent_name VARCHAR(100),
ADD COLUMN contact_number VARCHAR(20),
ADD COLUMN address VARCHAR(255),
ADD COLUMN paid_amount DECIMAL(10, 2) DEFAULT 0,
ADD COLUMN due_amount DECIMAL(10, 2) DEFAULT 0,
ADD COLUMN balance_amount DECIMAL(10, 2) DEFAULT 0,
ADD COLUMN invoice_status ENUM('Paid', 'Due','Advance paid') DEFAULT 'Due',
ADD COLUMN time TIME DEFAULT CURRENT_TIME();

ALTER TABLE invoices
MODIFY COLUMN time TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
-- -- Update the stored procedure to include the correct logic
-- DROP PROCEDURE IF EXISTS generateInvoiceNumber;

-- DELIMITER //
-- CREATE PROCEDURE generateInvoiceNumber(IN invoiceID INT)
-- BEGIN
--     DECLARE invoiceDate DATE;
--     DECLARE branchID VARCHAR(4);
--     DECLARE sequenceNumber INT;

--     SELECT DATE_FORMAT(invoice_date, '%Y%m%d'), branch_id
--     INTO invoiceDate, branchID
--     FROM invoices
--     WHERE id = invoiceID;

--     SELECT COALESCE(MAX(SUBSTRING(invoice_number, -2)), 0) + 1
--     INTO sequenceNumber
--     FROM invoices
--     WHERE DATE(invoice_date) = invoiceDate;

--     UPDATE invoices
--     SET invoice_number = CONCAT(branchID, DATE_FORMAT(invoice_date, '%Y%m%d'), LPAD(sequenceNumber, 2, '0'))
--     WHERE id = invoiceID;
-- END //
-- DELIMITER ;

-- Update existing data in invoice_number
UPDATE invoices i
SET invoice_number = (
    SELECT CONCAT(branch_id, DATE_FORMAT(invoice_date, '%Y%m%d'), LPAD((ROW_NUMBER() OVER (PARTITION BY DATE(invoice_date) ORDER BY id) - 1) % 100, 2, '0'))
    FROM invoices
    WHERE i.id = id
);

-- Update data in the subject_names column
UPDATE invoices i
SET subject_name = (
    SELECT GROUP_CONCAT(s.subject_name)
    FROM subjects s
    WHERE s.id = i.student_id  -- Corrected from i.subject_id to i.student_id
    GROUP BY i.id
);

-- Update data in the student_name, parent_name, contact_number, address columns
UPDATE invoices i
JOIN branch_students s ON s.id = i.student_id
SET i.student_name = s.student_name,
    i.contact_number = s.contact,
    i.address = s.address;

-- -- Update the stored procedure to include the correct logic
-- DROP PROCEDURE IF EXISTS generateInvoiceNumber;

-- DELIMITER //
-- CREATE PROCEDURE generateInvoiceNumber(IN invoiceID INT)
-- BEGIN
--     DECLARE invoiceDate DATE;
--     DECLARE branchID VARCHAR(4);
--     DECLARE sequenceNumber INT;

--     SELECT DATE_FORMAT(invoice_date, '%Y%m%d'), branch_id
--     INTO invoiceDate, branchID
--     FROM invoices
--     WHERE id = invoiceID;

--     SELECT COALESCE(MAX(SUBSTRING(invoice_number, -2)), 0) + 1
--     INTO sequenceNumber
--     FROM invoices
--     WHERE DATE(invoice_date) = invoiceDate;

--     UPDATE invoices
--     SET invoice_number = CONCAT(branchID, DATE_FORMAT(invoice_date, '%Y%m%d'), LPAD(sequenceNumber, 2, '0'))
--     WHERE id = invoiceID;
-- END //
-- DELIMITER ;

-- Continue with the rest of your code

-- Insert data into the master table
INSERT INTO master (username, password) VALUES ('master1', MD5('master123'));



