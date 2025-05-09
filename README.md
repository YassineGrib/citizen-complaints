# Citizen Complaints System

![PHP](https://img.shields.io/badge/PHP-%3E%3D7.4-blue)
![License](https://img.shields.io/badge/license-MIT-green)
![PHPMailer](https://img.shields.io/badge/PHPMailer-v6.8%2B-orange)

A comprehensive web application for managing and processing citizen complaints. Built using PHP and PHPMailer for email notifications.

## Overview

![image](https://github.com/user-attachments/assets/aaac66e4-1c57-460e-ada6-a3b9f9eefb98)

The Citizen Complaints System is an electronic platform that allows citizens to submit and track complaints and suggestions in an easy and effective manner. The system provides a user-friendly interface for citizens to submit their complaints, and a control panel for administrators to manage and process these complaints.

## Key Features

- **Complaint Submission**: User-friendly interface for submitting complaints with file attachment capability
- **Complaint Classification**: Support for different types of complaints (roads, public lighting, public parks, sports facilities, etc.)
- **Admin Dashboard**: Administrative interface for officials to review and process complaints
- **Email Notifications**: Automatic notifications sent to citizens when their complaint status is updated
- **Status Tracking**: Updating complaint status (new, in progress, resolved, rejected)
- **Search and Filtering**: Ability to search and filter complaints according to various criteria

## Technical Requirements

- PHP 7.4 or newer
- Web server (Apache/Nginx)
- MySQL 5.7 or newer
- Enable `fileinfo` extension in PHP
- Enable `mysqli` extension in PHP
- PHPMailer 6.8 or newer

## Project Structure

```
/
├── admin/                  # Admin dashboard files
│   ├── dashboard.php       # Main dashboard page
│   ├── login.php           # Admin login page
│   ├── logout.php          # Logout
│   ├── nav.php             # Dashboard navigation bar
│   └── view_complaint.php  # View complaint details
├── config/                 # Configuration files
│   ├── database.php        # Database connection settings
│   └── mail.php            # Email settings
├── includes/               # Shared files
│   └── nav.php             # General navigation bar
├── complaint.php           # Complaint submission page
├── index.php               # Homepage
├── composer.json           # Project dependencies
└── README.md               # Project documentation
```

## Database Setup

The system uses a MySQL database with the following tables:

### Admin Table (admin)

```sql
CREATE TABLE admin (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) NOT NULL UNIQUE,
  mot_de_passe_hache VARCHAR(255) NOT NULL
);
```

### Complaints Table (reclamations)

```sql
CREATE TABLE reclamations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(255) NOT NULL,
  cni VARCHAR(50) NOT NULL,
  telephone VARCHAR(20) NOT NULL,
  email VARCHAR(255) NOT NULL,
  sujet VARCHAR(255) NOT NULL,
  type VARCHAR(100) NOT NULL,
  contenu TEXT NOT NULL,
  fichier VARCHAR(255),
  statut ENUM('new', 'in progress', 'resolved', 'rejected') DEFAULT 'new',
  date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Responses Table (reponses)

```sql
CREATE TABLE reponses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  reclamation_id INT NOT NULL,
  message TEXT NOT NULL,
  date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (reclamation_id) REFERENCES reclamations(id) ON DELETE CASCADE
);
```

## Email Setup

The system uses the PHPMailer library to send email notifications. Create a `config/mail.php` file with the following content (modify the settings according to your mail server):

```php
<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Email settings
define('MAIL_HOST', 'smtp.example.com');
define('MAIL_PORT', 465);
define('MAIL_USERNAME', 'your-email@example.com');
define('MAIL_PASSWORD', 'your-password');
define('MAIL_FROM', 'your-email@example.com');
define('MAIL_FROM_NAME', 'Complaints System');
define('MAIL_SECURE', PHPMailer::ENCRYPTION_SMTPS);
define('MAIL_CHARSET', 'UTF-8');
define('MAIL_DEBUG', 0); // 0 = no debugging, 2 = full debugging
```

## Installation

1. Copy the project files to your web server directory
2. Create a new MySQL database
3. Execute the SQL commands to create the required tables
4. Create a `config/database.php` file and add the database connection information:

```php
<?php
$host = 'localhost';
$dbname = 'complaints_db';
$username = 'root';
$password = '';

$conn = mysqli_connect($host, $username, $password, $dbname);

if (!$conn) {
    die("Failed to connect to database: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");
```

5. Create a `config/mail.php` file and add the email settings as shown above
6. Install dependencies using Composer:

```
composer install
```

7. Create an admin account in the database:

```sql
INSERT INTO admin (email, mot_de_passe_hache) VALUES ('admin@example.com', '$2y$10$...');
```

## Usage

### For Citizens

1. Visit the system's homepage
2. Click on "Submit a new complaint"
3. Fill out the form with the required information
4. Click "Send" to submit the complaint

### For Administrators

1. Visit the admin login page
2. Enter your email and password
3. Use the dashboard to manage submitted complaints
4. You can change complaint status and respond to them

## Contributing

Contributions are welcome! Please open an issue or submit a pull request for improvements.

## License

This project is intended for educational purposes. See the LICENSE file for details.
