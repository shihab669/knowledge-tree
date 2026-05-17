-- Knowledge Tree - MySQL Database Schema
-- Run this file to create the database and tables

CREATE DATABASE IF NOT EXISTS knowledge_tree CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE knowledge_tree;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login DATETIME DEFAULT NULL,
    INDEX idx_username (username),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Nodes table
CREATE TABLE IF NOT EXISTS nodes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    parent_id INT DEFAULT NULL,
    title VARCHAR(255) NOT NULL DEFAULT 'New Node',
    content TEXT DEFAULT '',
    tags VARCHAR(500) DEFAULT '',
    color VARCHAR(20) DEFAULT '#6366f1',
    icon VARCHAR(50) DEFAULT 'fa-circle',
    position INT DEFAULT 0,
    is_collapsed TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES nodes(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_parent_id (parent_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
