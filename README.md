# Todo App

A straightforward todo app built with PHP and MySQL. Add tasks, mark them done, edit or delete them. Shows your progress at a glance.

## What It Does

- **Add tasks** – Title and description, keep it simple
- **Mark things done** – Check them off as you finish
- **Edit on the fly** – Change a task's title or description in a modal
- **Delete stuff** – Trash individual tasks or wipe all the completed ones at once
- **Filter what you see** – View everything or just what's still pending
- **Quick stats** – Total tasks, what's left to do, what's finished

## File Layout

```
todo-app/
├── src/
│   ├── Task.php         # The Task class
│   ├── TaskManager.php  # All the CRUD stuff and database queries
│   └── Database.php     # Handles database connection
├── index.php            # The page you actually see + form handling
└── schema.sql           # Database setup
```

## How to Set It Up

### What You Need
- PHP 8.0+
- MySQL 5.7+ or MariaDB
- A local dev server (XAMPP, Laragon, PHP's built-in server, whatever)

### Get It Running

**1. Create the database**
```bash
mysql -u root -p < schema.sql
```

**2. Set your database info**

Open `src/Database.php` and update these:
```
DB_HOST=localhost
DB_NAME=todo_app
DB_USER=root
DB_PASS=your_password
```

Or just set environment variables if you prefer.

**3. Start the server**
```bash
php -S localhost:8000
```

**4. Open it up**

Go to `http://localhost:8000` in your browser and start adding tasks.

## Built With

- PHP 8 – OOP classes, PDO, prepared statements
- MySQL – where your tasks live
- HTML/CSS – responsive, no bloat
- JavaScript – modal popup and auto-save on checkbox
