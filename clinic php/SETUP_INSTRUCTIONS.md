# How to Run the PHP Medical Appointment Project Locally

This guide will walk you through setting up and running the PHP-based medical appointment system on your local machine. You will need a web server environment that supports PHP and MySQL.

## Prerequisites

1.  **Web Server with PHP and MySQL:**
    *   **XAMPP (Recommended for ease of use):** A free and open-source cross-platform web server solution stack package, consisting mainly of the Apache HTTP Server, MariaDB database (a MySQL fork), and interpreters for scripts written in the PHP and Perl programming languages. Download from [Apache Friends](https://www.apachefriends.org).
    *   **WAMP (for Windows):** Similar to XAMPP but Windows-specific. Download from [WampServer](https://www.wampserver.com/en/).
    *   **MAMP (for macOS):** Similar to XAMPP but macOS-specific. Download from [MAMP](https://www.mamp.info/en/mamp/).
    *   **Manual Setup:** You can also install Apache/Nginx, PHP, and MySQL/MariaDB separately if you prefer.

2.  **Web Browser:** Any modern web browser like Chrome, Firefox, Safari, or Edge.

3.  **Project Files:** The `clinic_project_scaffold.zip` file you received.

## Setup Steps

1.  **Install Your Web Server Environment:**
    *   Download and install XAMPP, WAMP, or MAMP according to the instructions on their respective websites.
    *   Ensure that Apache and MySQL (or MariaDB) services are started through the control panel of your chosen software.

2.  **Extract Project Files:**
    *   Unzip the `clinic_project_scaffold.zip` file.
    *   You will get a folder named `clinic_project` (or similar, depending on how it was zipped, it might be `home/ubuntu/clinic_project`). You need the `clinic_project` folder that contains `index.php`, `css/`, `includes/`, etc.
    *   Copy this `clinic_project` folder into the web server's document root directory:
        *   **XAMPP:** `C:\xampp\htdocs\` (Windows) or `/Applications/XAMPP/htdocs/` (macOS).
        *   **WAMP:** `C:\wamp\www\` or `C:\wamp64\www\`.
        *   **MAMP:** `/Applications/MAMP/htdocs/`.
        *   So, after copying, you should have a path like `C:\xampp\htdocs\clinic_project\`.

3.  **Set Up the Database:**
    *   Open your web browser and go to `http://localhost/phpmyadmin` (this is the default URL for phpMyAdmin in XAMPP, WAMP, and MAMP).
    *   **Create a new database:**
        *   Click on "Databases" at the top (or "New" on the left panel).
        *   In the "Create database" field, enter `clinic_db`.
        *   Choose a collation like `utf8mb4_unicode_ci` (often the default is fine) and click "Create".
    *   **Import the schema:**
        *   Select the newly created `clinic_db` database from the left-hand sidebar.
        *   Click on the "Import" tab at the top.
        *   Under "File to import", click "Choose File" and select the `schema.sql` file that was provided to you (it's also inside the `clinic_project` folder).
        *   Leave other options as default and click "Go" at the bottom of the page.
        *   You should see a success message indicating that the tables have been created.

4.  **Configure Database Connection (if necessary):**
    *   The project's database connection file is located at `clinic_project/includes/db.php`.
    *   By default, it's configured for:
        *   Host: `localhost`
        *   User: `root`
        *   Password: `(empty)`
        *   Database Name: `clinic_db`
    *   This configuration usually works out-of-the-box with default XAMPP/WAMP/MAMP installations. If your MySQL setup has a different username or password (especially if you set one for the `root` user), you will need to update these lines in `db.php`:
        ```php
        define("DB_USER", "your_mysql_username");
        define("DB_PASS", "your_mysql_password");
        ```

5.  **Access the Project:**
    *   Open your web browser.
    *   Navigate to `http://localhost/clinic_project/`.
    *   You should see the homepage of the medical appointment system (currently a basic login page).

## Next Steps

*   The project is currently a scaffold. This means the basic structure and files are in place, but the detailed functionality within each PHP file (forms, data processing, dynamic content display) needs to be implemented.
*   You can start by trying to register a new patient and then log in.
*   Explore the different dashboard placeholders for admin, patient, doctor, and nurse roles (you'll need to manually create users with these roles in the `users` table via phpMyAdmin for testing, or implement the user management features in the admin dashboard).

If you encounter any issues, double-check the Apache and MySQL error logs provided by your XAMPP/WAMP/MAMP control panel. These can provide clues about what might be going wrong.

