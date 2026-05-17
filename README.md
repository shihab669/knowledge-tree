# Knowledge Tree

A visual knowledge management system for organizing thoughts, ideas, and information in interactive tree structures.

![Version](https://img.shields.io/badge/Version-1.0.0-blueviolet)
![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1)
![License](https://img.shields.io/badge/License-MIT-green)

## Overview

Knowledge Tree is a self-hosted application for building and exploring hierarchical notes. It uses PHP and MySQL for persistence, with a D3.js-powered tree visualization for smooth zooming, panning, and interactive node management.

## Features

- Interactive tree visualization with D3.js
- Dark dashboard with a glassmorphism-inspired UI
- Unlimited nesting depth for notes and branches
- Rich text editing with Markdown support
- Global search with Ctrl+K / Cmd+K
- Context actions for edit, add child, duplicate, and delete
- Responsive layout for desktop and mobile
- Secure authentication and prepared statements

## Requirements

- PHP 8.0 or higher
- MySQL 8.0 or compatible server
- PDO MySQL extension
- Apache with `mod_rewrite` or another compatible web server

## Installation

### Local Development

1. Clone the repository:

```bash
git clone https://github.com/yourusername/knowledge-tree.git
cd knowledge-tree
```

2. Create a MySQL database and import the schema:

```bash
mysql -u your_user -p your_database < database.sql
```

3. Update `config.php` with your MySQL credentials.

4. Start the PHP development server:

```bash
php -S localhost:8000
```

5. Open `http://localhost:8000` in your browser.

6. Follow the setup flow to connect the app to your database and create your account.

### Shared Hosting

1. Upload the project files to your hosting account.
2. Import `database.sql` into your MySQL database.
3. Configure the database credentials in `config.php`.
4. Make sure your web server supports PHP and URL rewriting.
5. Open the site in a browser and complete the setup flow.

## Project Structure

```
knowledge-tree/
├── index.php
├── config.php
├── setup.php
├── database.sql
├── app/
│   ├── Core/
│   │   ├── Database.php
│   │   ├── Router.php
│   │   ├── Auth.php
│   │   └── Response.php
│   ├── Controllers/
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   └── ApiController.php
│   ├── Models/
│   │   ├── User.php
│   │   └── Node.php
│   └── Views/
│       ├── 404.php
│       ├── dashboard.php
│       ├── layout.php
│       ├── auth/
│       │   └── login.php
│       └── partials/
│           ├── navbar.php
│           ├── node-editor.php
│           └── sidebar.php
├── public/
│   ├── css/
│   ├── js/
│   └── assets/
└── data/
```

## Usage

### Navigation

- Click a node to view its details
- Right-click a node for edit, add child, duplicate, or delete
- Use the sidebar to browse branches
- Use the zoom controls to zoom in, zoom out, or reset the view
- Press Ctrl+K / Cmd+K to search across notes

### Keyboard Shortcuts

| Shortcut | Action |
|----------|--------|
| Ctrl+K / Cmd+K | Open search |
| Ctrl+S | Save current node |
| Escape | Close editor or menu |
| Delete | Remove selected node |

### Node Operations

- Create a root node from the toolbar or empty state
- Add a child node from the node details panel or context menu
- Edit an existing node in the modal editor
- Duplicate a node and its children
- Delete a node and its descendants

## Configuration

Edit `config.php` to adjust your database connection and application settings.

Typical values include:

```php
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'knowledge_tree');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');
```

## Security

- Password hashing with bcrypt
- Prepared SQL statements
- Output escaping to prevent XSS
- Session-based authentication

## Browser Support

- Chrome 90 and later
- Firefox 88 and later
- Safari 14 and later
- Edge 90 and later

## License

MIT License. See the LICENSE file for details.

## Credits

- [D3.js](https://d3js.org/) - Tree visualization
- [Font Awesome](https://fontawesome.com/) - Icons
- [Inter](https://rsms.me/inter/) - Typeface