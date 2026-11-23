-- =====================================================================
-- Smart Living - database
-- Esprit School of Engineering - PIWEB 2A - 2026/2027
-- Import: phpMyAdmin > smartliving > SQL (paste) or Import (file)
-- WARNING: deletes and recreates all the tables.
-- =====================================================================

CREATE DATABASE IF NOT EXISTS smartliving CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE smartliving;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS donation, campaign, response, reclamation, redemption, reward,
  comment, post, event_participation, green_event, task, incident, incident_category, users, user;

-- ===== Shared: users (workshop "Sessions and PHP Security") =====
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  first_name VARCHAR(50) NOT NULL,
  last_name VARCHAR(50) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
  status ENUM('active', 'blocked') NOT NULL DEFAULT 'active',
  points INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ===== Module 1: Urban Reporting (Incident + Task) =====
CREATE TABLE IF NOT EXISTS incident (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(150) NOT NULL,
  description TEXT,
  photo VARCHAR(255),
  latitude FLOAT,
  longitude FLOAT,
  status VARCHAR(30) NOT NULL DEFAULT 'SUBMITTED',
  priority VARCHAR(20),
  createdAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  userId INT NOT NULL,
  FOREIGN KEY (userId) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS task (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(150) NOT NULL,
  description TEXT,
  assignedTeam VARCHAR(100),
  dueDate DATETIME,
  status VARCHAR(30) NOT NULL DEFAULT 'TODO',
  incidentId INT NOT NULL,
  FOREIGN KEY (incidentId) REFERENCES incident(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ===== Module 2: Events & Participation =====
CREATE TABLE IF NOT EXISTS green_event (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(150) NOT NULL,
  description TEXT,
  location VARCHAR(150),
  eventDate DATETIME NOT NULL,
  capacity INT NOT NULL DEFAULT 0,
  organizerId INT NOT NULL,
  FOREIGN KEY (organizerId) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS event_participation (
  id INT AUTO_INCREMENT PRIMARY KEY,
  registeredAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  numberOfTickets INT NOT NULL DEFAULT 1,
  ticketCode VARCHAR(50) UNIQUE,
  attended BOOLEAN NOT NULL DEFAULT FALSE,
  userId INT NOT NULL,
  eventId INT NOT NULL,
  FOREIGN KEY (userId) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (eventId) REFERENCES green_event(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ===== Module 3: Forum =====
CREATE TABLE IF NOT EXISTS post (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(150) NOT NULL,
  content TEXT NOT NULL,
  image VARCHAR(255),
  createdAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  userId INT NOT NULL,
  FOREIGN KEY (userId) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS comment (
  id INT AUTO_INCREMENT PRIMARY KEY,
  content TEXT NOT NULL,
  createdAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  userId INT NOT NULL,
  postId INT NOT NULL,
  FOREIGN KEY (userId) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (postId) REFERENCES post(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ===== Module 4: Green Rewards =====
CREATE TABLE IF NOT EXISTS reward (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  description TEXT,
  pointsCost INT NOT NULL,
  stock INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS redemption (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pointsSpent INT NOT NULL,
  quantity INT NOT NULL DEFAULT 1,
  voucherCode VARCHAR(50) UNIQUE,
  redeemedAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  expiryDate DATETIME,
  status VARCHAR(30) NOT NULL DEFAULT 'PENDING',
  userId INT NOT NULL,
  rewardId INT NOT NULL,
  FOREIGN KEY (userId) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (rewardId) REFERENCES reward(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ===== Module 5: Complaints =====
CREATE TABLE IF NOT EXISTS reclamation (
  id INT AUTO_INCREMENT PRIMARY KEY,
  subject VARCHAR(150) NOT NULL,
  description TEXT,
  status VARCHAR(30) NOT NULL DEFAULT 'OPEN',
  createdAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  userId INT NOT NULL,
  FOREIGN KEY (userId) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS response (
  id INT AUTO_INCREMENT PRIMARY KEY,
  content TEXT NOT NULL,
  attachment VARCHAR(255),
  isFinal BOOLEAN NOT NULL DEFAULT FALSE,
  satisfactionRating INT,
  createdAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  reclamationId INT NOT NULL,
  FOREIGN KEY (reclamationId) REFERENCES reclamation(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ===== Module 6: Donations =====
CREATE TABLE IF NOT EXISTS campaign (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(150) NOT NULL,
  description TEXT,
  goalAmount FLOAT NOT NULL,
  collectedAmount FLOAT NOT NULL DEFAULT 0,
  endDate DATETIME
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS donation (
  id INT AUTO_INCREMENT PRIMARY KEY,
  amount FLOAT NOT NULL,
  donatedAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  message TEXT,
  userId INT NOT NULL,
  campaignId INT NOT NULL,
  FOREIGN KEY (userId) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (campaignId) REFERENCES campaign(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ===== Demo accounts =====
-- admin@smartliving.tn / Admin@1234   (role admin  -> backoffice)
-- user@smartliving.tn  / User@1234    (role user   -> frontoffice)
INSERT INTO users (username, first_name, last_name, email, password, role, status, points) VALUES
('admin', 'Salma', 'Admin', 'admin@smartliving.tn', '$2y$10$vCqCJ822hwzEfyrPmVvOVOegP6yagSN1ajeieZpWR7gJ4gCHr5ikm', 'admin', 'active', 0),
('citizen', 'Yasmine', 'Ben Ali', 'user@smartliving.tn', '$2y$10$9PQz91lPYGSQYv7muCTVTenTVRM.s.0YA6p.D4l9EAW5JdiNp0TNC', 'user', 'active', 120);

SET FOREIGN_KEY_CHECKS = 1;
