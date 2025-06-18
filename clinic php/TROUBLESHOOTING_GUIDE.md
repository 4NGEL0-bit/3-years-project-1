# Troubleshooting Guide for ClinicSys

This document provides solutions for common issues you might encounter when setting up and running the ClinicSys application.

## Database Connection Issues

### Issue: Cannot connect to database
**Symptoms:** White screen, "Database connection failed" error, or PHP errors related to mysqli_connect.

**Solutions:**
1. Verify your database credentials in `includes/db.php`
2. Ensure MySQL service is running
3. Check if the `clinic_db` database exists
4. Verify your MySQL user has proper permissions

### Issue: Table doesn't exist errors
**Symptoms:** Error messages containing "Table 'clinic_db.X' doesn't exist"

**Solutions:**
1. Ensure you've imported the `schema.sql` file correctly
2. Check if all tables were created in phpMyAdmin
3. If specific tables are missing, try importing the schema again
4. Verify there were no errors during import

## Login Issues

### Issue: Cannot log in with provided credentials
**Symptoms:** Login fails, redirects back to login page, or shows error message.

**Solutions:**
1. Ensure you've imported the `seed_users.sql` file
2. Verify the user exists in the `users` table
3. Default credentials are:
   - Admin: `admin@clinicsys.com` / `admin123`
   - Doctor: `doctor.carter@clinicsys.com` / `doctor123`
   - Nurse: `nurse.johnson@clinicsys.com` / `nurse123`
   - Patient: `john.doe@example.com` / `patient123`
4. If passwords don't work, you can update them directly in the database:
   ```sql
   UPDATE users SET mot_de_passe = '$2y$10$6qPSYlQGEMZLBzQYL5C4UeqVRw/dBMJHMEmtS5tG0hmXiguQ1IzAa' WHERE email = 'admin@clinicsys.com';
   ```
   (This sets the password to 'admin123')

## Page Display Issues

### Issue: Pages show PHP errors or warnings
**Symptoms:** Error messages at the top of pages, functionality not working.

**Solutions:**
1. Ensure PHP version is 7.4 or higher
2. Check file permissions (should be readable by web server)
3. Verify all project files were extracted correctly
4. Check for syntax errors in PHP files

### Issue: CSS/JS not loading
**Symptoms:** Unstyled pages, animations not working.

**Solutions:**
1. Check browser console for 404 errors
2. Ensure the `css` and `js` folders are in the correct location
3. Verify file permissions allow web server to read these files

## Docker/Kubernetes Deployment Issues

### Issue: Docker containers won't start
**Symptoms:** Error messages when running `docker-compose up`.

**Solutions:**
1. Ensure Docker and Docker Compose are installed
2. Check if ports 80 and 3306 are available (not used by other services)
3. Verify Docker has sufficient permissions
4. Check logs with `docker-compose logs`

### Issue: Kubernetes pods not running
**Symptoms:** Pods in CrashLoopBackOff or Error state.

**Solutions:**
1. Check pod logs: `kubectl logs <pod-name>`
2. Ensure all Kubernetes manifests are applied
3. Verify cluster has sufficient resources
4. Check if persistent volumes are properly configured

## Getting Additional Help

If you encounter issues not covered in this guide:

1. Check the PHP error logs (typically in your web server's log directory)
2. Review the MySQL error logs
3. Verify all steps in the `SETUP_INSTRUCTIONS.md` and `IMPORT_INSTRUCTIONS.md` files
4. Contact support with the following information:
   - Exact error messages
   - Steps to reproduce the issue
   - Your environment details (OS, PHP version, MySQL version)
   - Screenshots if applicable

## Feedback Loop

We're committed to ensuring ClinicSys works perfectly in your environment. If you encounter any issues:

1. Document the exact steps that led to the issue
2. Capture any error messages
3. Note your environment details
4. Contact our support team with this information

We'll work with you to resolve any issues promptly and provide updated files if necessary.
