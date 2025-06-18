# Deployment Guide: Clinic Appointment System

This guide provides step-by-step instructions for running the Clinic Appointment System using Docker and Kubernetes (with Minikube for local testing).

## Prerequisites

Before you begin, ensure you have the following installed on your Windows 11 machine:

1.  **Docker Desktop:** Download and install from [Docker Hub](https://www.docker.com/products/docker-desktop/). Ensure it's configured to use the WSL 2 backend for better performance.
2.  **kubectl:** This is the Kubernetes command-line tool. Docker Desktop for Windows includes an option to install kubectl and connect to a local Kubernetes cluster. You can enable this in Docker Desktop settings under the "Kubernetes" tab.
3.  **Minikube (for local Kubernetes):** For a local Kubernetes environment, Minikube is a good option. Download and install it from the [Kubernetes Minikube documentation](https://minikube.sigs.k8s.io/docs/start/). Alternatively, you can use other local Kubernetes solutions like kind, or the Kubernetes cluster provided by Docker Desktop.

## Directory Structure

Make sure you have the `clinic_project` folder, which should contain:

*   All PHP application files (`index.php`, `register.php`, `includes/`, `css/`, `js/`, etc.)
*   `schema.sql` (Database schema)
*   `seed_users.sql` (Sample user data)
*   `Dockerfile`
*   `docker-compose.yml`
*   `kubernetes/` (directory with Kubernetes YAML manifest files)

## Part 1: Running the Application with Docker Compose

Docker Compose allows you to run the multi-container application (PHP app, MySQL database, phpMyAdmin) easily.

**Step 1: Navigate to the Project Directory**

Open your command-line interface (Command Prompt, PowerShell, or Windows Terminal) and navigate to the `clinic_project` directory:

```bash
cd path\to\your\clinic_project
```

**Step 2: Build and Run the Containers**

Execute the following command:

```bash
docker-compose up -d --build
```

*   `up`: Creates and starts the containers.
*   `-d`: Runs the containers in detached mode (in the background).
*   `--build`: Builds the images before starting the containers (useful if you made changes to `Dockerfile`).

This command will:
1.  Build the Docker image for your PHP application based on the `Dockerfile`.
2.  Pull the official MySQL and phpMyAdmin images from Docker Hub.
3.  Create and start three services: `app` (your PHP application), `db` (MySQL), and `phpmyadmin`.
4.  Create a persistent volume for MySQL data (`mysql_data`) so your database information isn't lost when containers are stopped/removed.

**Step 3: Access the Application**

*   **Clinic Application:** Open your web browser and go to `http://localhost:8080`
*   **phpMyAdmin:** Open your web browser and go to `http://localhost:8081`

**Step 4: Set Up the Database (First Time Only)**

1.  **Access phpMyAdmin:** Go to `http://localhost:8081`.
2.  **Login to phpMyAdmin:**
    *   Server: `db` (this is the service name defined in `docker-compose.yml`)
    *   Username: `root`
    *   Password: `rootpassword` (as defined in `docker-compose.yml`)
3.  **Create the Database:**
    *   Click on the "Databases" tab.
    *   Under "Create database", enter `clinic_db` as the database name.
    *   Choose `utf8mb4_unicode_ci` as the collation.
    *   Click "Create".
4.  **Import the Schema:**
    *   Select the `clinic_db` database from the left-hand sidebar.
    *   Click on the "Import" tab.
    *   Under "File to import", click "Browse..." and select the `schema.sql` file from your `clinic_project` directory.
    *   Ensure the character set is `utf-8`.
    *   Click "Go" at the bottom of the page.
5.  **Import Seed Data (Optional but Recommended for Testing):**
    *   With `clinic_db` still selected, click on the "Import" tab again.
    *   Click "Browse..." and select the `seed_users.sql` file from your `clinic_project` directory.
    *   Click "Go".

You should now be able to use the application at `http://localhost:8080` and log in with the seeded user credentials (e.g., `admin@clinicsys.com` / `admin123`).

**Step 5: Stopping the Application**

To stop the containers, navigate to your `clinic_project` directory in the terminal and run:

```bash
docker-compose down
```

To remove the containers and the network (but not the `mysql_data` volume):

```bash
docker-compose down -v # Use with caution if you want to keep the volume
```

## Part 2: Running the Application with Kubernetes (using Minikube)

This section guides you through deploying the application to a local Kubernetes cluster using Minikube.

**Step 1: Start Minikube**

Open your terminal and start Minikube:

```bash
minikube start --driver=docker
```

Wait for Minikube to start. If you have Docker Desktop installed, Minikube can use its Docker daemon.

**Step 2: Build the Docker Image and Load it into Minikube**

Your Kubernetes cluster needs access to your application's Docker image.

*   **Option A (Recommended for local Minikube): Build directly into Minikube's Docker daemon.**
    First, point your local Docker CLI to Minikube's Docker daemon:
    ```bash
    # For PowerShell
    minikube docker-env | Invoke-Expression
    # For Command Prompt (cmd.exe)
    # minikube docker-env
    # Then copy and paste the output, which looks like:
    # SET DOCKER_TLS_VERIFY=...
    # SET DOCKER_HOST=...
    # SET DOCKER_CERT_PATH=...
    # SET MINIKUBE_ACTIVE_DOCKERD=...
    # For Git Bash or WSL
    # eval $(minikube docker-env)
    ```
    Then, navigate to your `clinic_project` directory and build the image:
    ```bash
    cd path\to\your\clinic_project
    docker build -t clinic-app:latest .
    ```
    This image `clinic-app:latest` will now be available within Minikube.

*   **Option B: Push to a Docker Registry (like Docker Hub).**
    If you were deploying to a remote Kubernetes cluster, you would build your image, tag it with your Docker Hub username, and push it:
    ```bash
    docker build -t yourusername/clinic-app:latest .
    docker push yourusername/clinic-app:latest
    ```
    Then, you would need to update the `image:` field in `/kubernetes/clinic-app-deployment.yaml` from `clinic-app:latest` to `yourusername/clinic-app:latest` and set `imagePullPolicy: Always`.

**Step 3: Apply Kubernetes Manifests**

Navigate to the `clinic_project/kubernetes` directory in your terminal:

```bash
cd path\to\your\clinic_project\kubernetes
```

Apply the manifest files in the following order:

1.  **MySQL Deployment and Service:**
    ```bash
    kubectl apply -f mysql-deployment.yaml
    kubectl apply -f mysql-service.yaml
    ```
2.  **Clinic App Deployment and Service:**
    ```bash
    kubectl apply -f clinic-app-deployment.yaml
    kubectl apply -f clinic-app-service.yaml
    ```
3.  **phpMyAdmin Deployment and Service:**
    ```bash
    kubectl apply -f phpmyadmin-deployment.yaml
    kubectl apply -f phpmyadmin-service.yaml
    ```

**Step 4: Check the Status**

Wait for the Pods to be created and running. You can check their status:

```bash
kubectl get pods
kubectl get services
```

All pods should eventually show `Running` status. Services will show their type and assigned ports/IPs.

**Step 5: Access the Application and phpMyAdmin**

Minikube provides a way to access services of type `LoadBalancer` or `NodePort`.

*   **Clinic Application:**
    ```bash
    minikube service clinic-app-service
    ```
    This command should open the application in your default web browser. If not, it will print the URL (e.g., `http://<minikube-ip>:<nodeport>`).

*   **phpMyAdmin:**
    ```bash
    minikube service phpmyadmin-service
    ```
    This will open phpMyAdmin in your browser or print its URL.

**Step 6: Set Up the Database in Kubernetes (First Time Only)**

1.  **Access phpMyAdmin** using the URL obtained from `minikube service phpmyadmin-service`.
2.  **Login to phpMyAdmin:**
    *   Server: `mysql-service` (this is the Kubernetes service name for MySQL)
    *   Username: `root`
    *   Password: `rootpassword` (as defined in `mysql-deployment.yaml` environment variables)
3.  **Create the Database:** Follow the same steps as in the Docker Compose section (Part 1, Step 4) to create the `clinic_db` database.
4.  **Import Schema and Seed Data:** Follow the same steps as in the Docker Compose section (Part 1, Step 4) to import `schema.sql` and `seed_users.sql` into the `clinic_db` database.

**Step 7: Cleaning Up Kubernetes Resources**

To delete all the resources created in your Minikube cluster:

```bash
cd path\to\your\clinic_project\kubernetes
kubectl delete -f phpmyadmin-service.yaml
kubectl delete -f phpmyadmin-deployment.yaml
kubectl delete -f clinic-app-service.yaml
kubectl delete -f clinic-app-deployment.yaml
kubectl delete -f mysql-service.yaml
kubectl delete -f mysql-deployment.yaml
```

To stop Minikube:

```bash
minikube stop
```

To revert your Docker CLI from Minikube's daemon (if you used Option A in Step 2):
```bash
# For PowerShell
# & minikube docker-env -u | Invoke-Expression
# For Git Bash or WSL
# eval $(minikube docker-env -u)
# For CMD, you may need to manually unset the environment variables or close and reopen the terminal.
```

## Important Notes

*   **Password Hashing:** The current PHP code uses plain text passwords for simplicity in this development stage. **This is highly insecure and MUST be changed to use `password_hash()` for storing passwords and `password_verify()` for checking them before any production use.** The `seed_users.sql` also uses plain text passwords for now.
*   **Error Handling:** The PHP code has basic error messages. Robust error logging and user-friendly error pages should be implemented.
*   **Security:** Ensure all inputs are sanitized, and be mindful of SQL injection (though prepared statements are used, which is good practice).
*   **Kubernetes Persistent Volumes:** The `mysql-deployment.yaml` uses an `emptyDir` volume for MySQL data, which means data will be lost if the MySQL pod is deleted/recreated. For production, you would configure a proper PersistentVolumeClaim (PVC) with a suitable storage class.

This guide should help you get the application running in both Docker and a local Kubernetes environment. Let me know if you have any further questions!

