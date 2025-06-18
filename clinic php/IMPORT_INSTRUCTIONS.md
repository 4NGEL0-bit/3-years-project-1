# Database Import Instructions for ClinicSys

This document provides detailed instructions for importing the database schema and seed data for the ClinicSys application. Follow these steps carefully to ensure a successful setup.

## Prerequisites

- XAMPP, WAMP, MAMP, or any MySQL server installed on your system
- Access to phpMyAdmin or MySQL command line

## Step 1: Create the Database

### Using phpMyAdmin:
1. Open phpMyAdmin (typically at http://localhost/phpmyadmin/)
2. Click on "Databases" in the top menu
3. Under "Create database", enter `clinic_db` as the database name
4. Select "utf8mb4_unicode_ci" as the collation
5. Click "Create"

### Using MySQL Command Line:
```sql
CREATE DATABASE clinic_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

## Step 2: Import the Schema

### Using phpMyAdmin:
1. Select the `clinic_db` database from the left sidebar
2. Click on the "Import" tab in the top menu
3. Click "Choose File" and select the `schema.sql` file from the project folder
4. Ensure "Character set of the file" is set to "utf8mb4"
5. Click "Go" to start the import

### Using MySQL Command Line:
```bash
mysql -u root -p clinic_db < /path/to/schema.sql
```

## Step 3: Import the Seed Data

### Using phpMyAdmin:
1. With the `clinic_db` database still selected
2. Click on the "Import" tab again
3. Click "Choose File" and select the `seed_users.sql` file
4. Ensure "Character set of the file" is set to "utf8mb4"
5. Click "Go" to start the import

### Using MySQL Command Line:
```bash
mysql -u root -p clinic_db < /path/to/seed_users.sql
```

## Step 4: Verify the Import

### Using phpMyAdmin:
1. Click on the `clinic_db` database in the left sidebar
2. You should see the following tables:
   - `users`
   - `appointments`
   - `medical_notes`
   - `payments`
   - `referrals`
   - `clinic_settings`
3. Click on the `users` table and verify that it contains the seed data (admin, doctors, nurses, and patients)

### Using MySQL Command Line:
```sql
USE clinic_db;
SHOW TABLES;
SELECT * FROM users;
```

## Troubleshooting

### Error: "Table 'clinic_db.utilisateurs' doesn't exist"
This error has been fixed in the latest version. If you still encounter it, ensure you're using the most recent `schema.sql` file where all table references have been updated from `utilisateurs` to `users`.

### Error: Foreign Key Constraint Failure
The foreign key constraints have been corrected in the latest version. If you encounter any foreign key errors:
1. Ensure you're using the most recent `schema.sql` file
2. Import the schema first, then the seed data
3. Check that the referenced tables exist before the tables that reference them

### Error: "Access denied for user..."
Ensure you're using the correct username and password for your MySQL server. The default configuration in `includes/db.php` uses:
- Username: `root`
- Password: `""`
- Host: `localhost`
- Database: `clinic_db`

If your MySQL configuration is different, update these values in the `includes/db.php` file.

## Next Steps

After successfully importing the database, you can proceed to set up the web server and access the application. Refer to the `SETUP_INSTRUCTIONS.md` file for detailed steps.

For Docker and Kubernetes deployment options, see the `DEPLOYMENT_GUIDE.md` file.
