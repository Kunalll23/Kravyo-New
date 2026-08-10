# Kravyo — Cloud Kitchen Platform for Homemakers and Small Food Businesses

**Kravyo** is an online cloud kitchen platform designed to empower homemakers, home chefs, and micro food entrepreneurs to sell authentic, hygienic home-cooked meals online without physical restaurant overheads.

---

## 🛠️ Technology Stack & Environment
- **Backend Language:** PHP 8+ (Custom Lightweight MVC Framework)
- **Database:** MySQL 8+ / MariaDB (XAMPP Compatible)
- **Frontend Framework:** HTML5, CSS3, Bootstrap 5.3, JavaScript (ES6+)
- **Server:** Apache (XAMPP Server environment)
- **IDE:** Visual Studio Code

---

## 📁 Project Architecture & Folder Structure

```
Kravyo/
├── .htaccess                    # Apache URL rewriting rule (routes root to public/)
├── README.md                    # Project documentation & local setup instructions
├── ROADMAP.md                   # Multi-phase development roadmap
│
├── config/                      # System Configurations
│   ├── app.php                  # Application URLs, paths & environment mode
│   ├── database.php             # MySQL database connection settings
│   ├── constants.php            # Global role constants, order statuses & badges
│   └── routes.php               # Front-controller URI to Controller mapping
│
├── core/                        # Core Lightweight MVC Framework Engines
│   ├── Database.php             # PDO Singleton connection wrapper
│   ├── Router.php               # Front-controller request router
│   ├── Controller.php           # Base controller class
│   ├── Model.php                # Base model class (CRUD helpers)
│   ├── View.php                 # View renderer with layout support
│   ├── Session.php              # Session & CSRF security manager
│   ├── Helpers.php              # Helper utility functions
│   └── Middleware.php           # Role & auth access control guards
│
├── app/                         # Application Layer
│   ├── controllers/             # Controller classes
│   │   └── HomeController.php   # Landing page controller
│   ├── models/                  # Database models
│   └── middleware/              # Custom middleware filters
│
├── public/                      # Web Root (Publicly accessible)
│   ├── index.php                # Front controller entry point
│   ├── .htaccess                # Apache mod_rewrite configuration
│   ├── assets/
│   │   ├── css/
│   │   │   └── style.css        # Kravyo custom design system styles
│   │   └── js/
│   │       └── app.js           # Client-side JavaScript logic
│   └── uploads/                 # Storage for kitchen/dish uploaded images
│
├── views/                       # View Templates
│   ├── layouts/
│   │   └── main.php             # Master layout template (Bootstrap 5)
│   ├── partials/
│   │   ├── header.php           # Navigation header bar
│   │   ├── footer.php           # Site footer
│   │   └── alerts.php           # Flash notification messages
│   ├── home/
│   │   └── index.php            # Platform landing page view
│   └── errors/
│       ├── 404.php              # 404 Page Not Found view
│       └── 500.php              # 500 Internal Server Error view
│
├── database/                    # Database Scripts
│   ├── schema.sql               # Full database creation script (10 tables)
│   └── seed.sql                 # Development seed data
│
└── storage/                     # Application Storage (Logs & Caches)
    ├── logs/
    └── cache/
```

---

## ⚡ How to Setup & Run on Local XAMPP

1. **Clone / Copy Project Folder**
   Copy the `Kravyo` folder into your XAMPP `htdocs` directory:
   ```
   C:\xampp\htdocs\Kravyo
   ```

2. **Start Apache & MySQL Services**
   Open **XAMPP Control Panel** and click **Start** for both **Apache** and **MySQL**.

3. **Import Database Schema**
   - Open phpMyAdmin in your browser: `http://localhost/phpmyadmin`
   - Create a new database named: `kravyo_db`
   - Click **Import** tab and select `database/schema.sql`
   - (Optional) Import `database/seed.sql` to populate sample data

4. **Launch Application**
   Open your browser and navigate to:
   ```
   http://localhost/Kravyo
   ```
   *The system automatically routes all traffic via `public/index.php` through Apache `.htaccess` rewriting.*
