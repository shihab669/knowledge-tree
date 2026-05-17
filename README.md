# Knowledge Tree

A visual knowledge management system for organizing thoughts, ideas, and information in interactive tree structures.

![Version](https://img.shields.io/badge/Version-1.0.0-blueviolet)
![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1)
![License](https://img.shields.io/badge/License-MIT-green)

## Overview

Knowledge Tree is a self-hosted application that helps you organize information hierarchically. Built with PHP and SQLite, it requires no external services or databases. The interactive tree visualization is powered by D3.js, providing smooth zoom, pan, and animations.

## Features

- Interactive tree visualization with D3.js
- Dark theme with glassmorphism design
- Unlimited nesting depth
- Rich text editing with Markdown support
- Global search with Ctrl+K
- Responsive across all devices
- Secure authentication with bcrypt
- No external dependencies required

## Requirements

- PHP 8.0 or higher
- PDO SQLite extension
- Apache with mod_rewrite (or compatible web server)

## Installation

### Local Development

1. Clone this repository to your web server directory:

```bash
git clone https://github.com/yourusername/knowledge-tree.git
cd knowledge-tree
```

2. Ensure the `data` directory is writable:

```bash
chmod 755 data/
```

3. Start the PHP development server:

```bash
php -S localhost:8000
```

4. Open `http://localhost:8000` in your browser

5. Follow the installation wizard to create your admin account

### Shared Hosting

Knowledge Tree is a self-hosted application that helps you organize information hierarchically. Built with PHP and MySQL, it uses a relational database for persistent storage. The interactive tree visualization is powered by D3.js, providing smooth zoom, pan, and animations.
1. Upload all files to your hosting directory via FTP or file manager

2. Set permissions on the `data/` directory to 755
knowledge-tree/
├── index.php              # Application entry point
├── config.php             # Configuration settings
│   ├── Core/              # Framework core classes
│   │   ├── Database.php   # SQLite connection handler
│   │   ├── Router.php     # URL routing engine
│   │   ├── Auth.php       # Authentication manager
│   │   └── Response.php   # HTTP response helpers
│   ├── Controllers/       # Request handlers
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   └── ApiController.php
│   ├── Models/            # Data models
│   │   ├── User.php
│   │   └── Node.php
2. Import `database.sql` into your MySQL database:
├── public/
│   ├── css/               # Stylesheets
mysql -u your_user -p your_database < database.sql
│   └── assets/            # Static assets
└── data/                  # Database storage
```

## Usage

### Navigation

4. Update `config.php` with your MySQL credentials.

5. Open `http://localhost:8000` in your browser
- **Zoom**: Mouse wheel or trackpad gesture
- **Select**: Click on any node
6. Follow the installation wizard to create your admin account
- **Context Menu**: Right-click on a node
- **Search**: Press Ctrl+K (Cmd+K on macOS)

### Keyboard Shortcuts

2. Import `database.sql` into your MySQL database
|----------|--------|
3. Configure the MySQL connection in `config.php`

4. Navigate to your domain in a web browser
| Ctrl+S | Save current node |
5. Complete the installation wizard
| Delete | Remove selected node |

### Node Operations

- Click the plus icon to add a root node
- Right-click a node for edit, add child, duplicate, or delete
- Double-click a node to open the editor
- Click expand/collapse indicators on nodes with children

## Security

- Bcrypt password hashing
│   │   ├── Database.php   # MySQL connection handler
- Prepared SQL statements throughout
- Output escaping to prevent XSS
- Secure session configuration
- Rate limiting on authentication

## Configuration

Edit `config.php` to adjust:

```php
// Session timeout (seconds)
define('SESSION_LIFETIME', 3600 * 8);

// Password hashing cost
define('BCRYPT_COST', 12);
└── database.sql           # MySQL schema and seed data
// Allow new user registration
define('ALLOW_REGISTRATION', true);
```

## Browser Support

- Chrome 90 and later
- Firefox 88 and later
- Safari 14 and later
- Edge 90 and later

## License

MIT License. See LICENSE file for details.

## Credits

- [D3.js](https://d3js.org/) - Data visualization library
- [Font Awesome](https://fontawesome.com/) - Icon toolkit
- [Inter](https://rsms.me/inter/) - Typeface
