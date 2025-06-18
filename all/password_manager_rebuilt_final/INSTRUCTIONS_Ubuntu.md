# Detailed Build and Run Instructions for Ubuntu 22.04.5 (Rebuilt Version)

This document provides detailed step-by-step instructions to compile and run the **rebuilt** Simple RMI Password Manager on a fresh Ubuntu 22.04.5 system.

**Assumptions:**

*   You have a working Ubuntu 22.04.5 installation (Desktop or Server).
*   You have terminal access with `sudo` privileges.
*   You have internet connectivity to download packages.
*   You have **deleted any previous versions** of the application (e.g., `~/password_manager_app`).
*   You have obtained the rebuilt application code (e.g., by unzipping the provided `.zip` file) and placed it in your home directory, resulting in a `~/password_manager_rebuild` folder.

**Important Note on Java Version:** The included scripts (`compile.sh`, `run_server.sh`, `run_client.sh`) have been configured to explicitly use the standard installation path for **OpenJDK 17** (`/usr/lib/jvm/java-17-openjdk-amd64`) to ensure compatibility and avoid issues with different default Java versions on your system.

---

**Step 1: Install Prerequisites**

First, update your system's package list and install the necessary software: Java Development Kit (JDK 17) and `net-tools` (which provides the `netstat` command used for checking the RMI registry port in the server script).

1.  **Open a Terminal:** Press `Ctrl+Alt+T` or search for "Terminal".
2.  **Update Package List:**
    ```bash
    sudo apt update
    ```
    Enter your password when prompted.
3.  **Install JDK (OpenJDK 17) and net-tools:**
    ```bash
    sudo apt install openjdk-17-jdk net-tools -y
    ```
    *(The `-y` flag automatically confirms the installation.)*
4.  **Verify Java 17 Installation Path (Crucial Check):**
    ```bash
    ls -l /usr/lib/jvm/java-17-openjdk-amd64/bin/java
    ```
    This command **must** successfully list the Java executable. If it fails or shows a different path, your Java 17 installation is non-standard. You would need to edit the `JAVA17_HOME` variable at the top of `compile.sh`, `run_server.sh`, and `run_client.sh` to match the correct path on your system.

---

**Step 2: Prepare Application Files**

**Crucially, all subsequent commands in these instructions MUST be run from *within* the application directory (`~/password_manager_rebuild`).**

1.  **Navigate to the Application Directory:**
    *   Open a terminal (or use the existing one) and navigate into the `password_manager_rebuild` directory located in your home folder:
        ```bash
        cd ~/password_manager_rebuild
        ```
    *   Verify you are in the correct directory:
        ```bash
        pwd
        ```
        (Should output `/home/your_username/password_manager_rebuild` or similar)
    *   List the files to confirm:
        ```bash
        ls -l
        ```
        You should see `src`, `compile.sh`, `run_server.sh`, `run_client.sh`, `README.md`, etc.

2.  **Make Scripts Executable:**
    *   While inside the `~/password_manager_rebuild` directory, run:
        ```bash
        chmod +x compile.sh run_server.sh run_client.sh
        ```

---

**Step 3: Compile the Java Code**

Compile the Java source files into `.class` files using the specific Java 17 compiler path defined in the script.

1.  **Run the Compile Script (from `~/password_manager_rebuild`):**
    ```bash
    ./compile.sh
    ```
2.  **Check Output:** You should see messages about removing old files (if any), creating the `bin` directory, and finally "Compilation successful. Class files are in bin". If you see errors, check the Java installation path (Step 1.4) and ensure the source code hasn't been corrupted.
3.  **Verify:** A new directory named `bin` should now exist within `~/password_manager_rebuild`.

---

**Step 4: Run the Server**

The server needs to be running before the client can connect. The `run_server.sh` script now handles starting the RMI registry more robustly and then the password manager server application, using the specific Java 17 runtime path.

1.  **Open a Terminal (Terminal 1):** Ensure you are in the `~/password_manager_rebuild` directory.
    ```bash
    # If needed, navigate back:
    cd ~/password_manager_rebuild
    ```
2.  **Run the Server Script (from `~/password_manager_rebuild`):**
    ```bash
    ./run_server.sh
    ```
3.  **Check Output:** You should see messages like:
    *   "Attempting to start RMI registry..."
    *   "RMI Registry started in background..." or "Warning: Port 1099 appears to be already in use..."
    *   "RMI Registry appears to be running successfully..."
    *   "Starting Password Manager Server..."
    *   "Password Manager implementation instance created."
    *   "Located RMI registry on port 1099"
    *   "Password Manager Server ready. Service name: PasswordManagerService"
    *   "Server bound in registry. Waiting for client connections..."
    If you see any errors here (e.g., "Failed to start RMI registry", "Failed to start Password Manager Server"), review the error messages carefully. Common causes include port conflicts (1099) or issues with the compiled code.
4.  **Keep this terminal (Terminal 1) open!** The server runs in the foreground. Closing this terminal will stop the server and erase all in-memory data.

---

**Step 5: Run the Client**

With the server running successfully in Terminal 1, start the client application in a separate terminal.

1.  **Open a NEW Terminal (Terminal 2):** Do not close Terminal 1!
2.  **Navigate to the Application Directory (in Terminal 2):**
    ```bash
    cd ~/password_manager_rebuild
    ```
3.  **Run the Client Script (from `~/password_manager_rebuild`):**
    ```bash
    ./run_client.sh
    ```
4.  **Client GUI:** The "Password Manager - Welcome" window should appear.

---

**Step 6: Using the Client Application**

1.  **Welcome Screen:**
    *   Verify the "Server Address" field shows `localhost` (or the correct server IP if running on a different machine).
    *   Click **"Login / Connect"** to proceed to the main application screen for logging in.
    *   Click **"Register New User"** to proceed to the main application screen for registration.
    *   If the client cannot connect to the server at this stage, a "Connection Error" popup will appear. Ensure the server is running correctly (check Terminal 1 output) and the address is correct.
2.  **Main Application Screen (After Connecting):**
    *   The **Status Area** at the top will show connection status and subsequent action results.
    *   **If Registering:** Enter a desired username and password in the "Authentication" section and click the **"Register"** button. Check the Status Area for confirmation ("User registered successfully..." or an error).
    *   **If Logging In:** Enter your registered username and password and click the **"Login"** button. Check the Status Area.
3.  **After Successful Login:**
    *   The Status Area will show "Login successful...".
    *   The "Authentication" section fields become non-editable, and the "Logout" button becomes active.
    *   The "Manage Entries" section and the "Stored Entries" table become active.
    *   Your existing entries (if any) will be loaded into the table.
    *   **Add/Update Entry:** Fill in the Account Name, Account Username, and Account Password fields, then click "Add/Update Entry". The table will refresh.
    *   **Refresh Entries:** Click "Refresh Entries" to reload the data from the server into the table.
    *   **Select Entry:** Click on a row in the table to populate the Account Name/Username/Password fields in the "Manage Entries" section.
    *   **Delete Entry:** Select an entry in the table (or type the Account Name) and click "Delete Entry". Confirm the deletion in the popup dialog.
    *   **Clear Fields:** Click "Clear Fields" to empty the Account Name/User/Password text boxes and deselect any table row.
4.  **Logout:** Click "Logout" to end your session. The "Manage Entries" section and table will be disabled/cleared, and the Authentication fields will become active again.
5.  **Close:** Close the client window when finished. You can then stop the server by going to Terminal 1 and pressing `Ctrl+C`.

---

**Troubleshooting Tips:**

*   **`./compile.sh: Permission denied`:** You forgot `chmod +x ...` (Step 2.2) or are not in the `~/password_manager_rebuild` directory.
*   **`Command not found`:** You are likely not in the `~/password_manager_rebuild` directory when trying to run `./compile.sh`, `./run_server.sh`, or `./run_client.sh`.
*   **`Error: Java 17 ... not found at /usr/lib/jvm/java-17-openjdk-amd64/...`:** Your Java 17 installation is in a non-standard path. Edit the `JAVA17_HOME` variable in all `.sh` scripts.
*   **`Error: Class files directory 'bin' not found...`:** Run `./compile.sh` successfully first (Step 3).
*   **`Error: Failed to start RMI registry...`:** Ensure port 1099 is free. Use `sudo netstat -tulnp | grep 1099` to check. Kill any conflicting process if necessary.
*   **Client: "Connection Error" Popup on Welcome Screen:**
    *   Double-check the server is running successfully in Terminal 1 (look for "Waiting for client connections...").
    *   Verify the server address on the Welcome Screen is correct (`localhost`).
    *   Check firewall: `sudo ufw status`. If active, try `sudo ufw allow 1099/tcp`.
*   **Client: `ERROR during ... : java.rmi.UnmarshalException... ClassNotFoundException...` (in Status Area):** Usually means client/server class mismatch or classpath issue. Ensure both server and client were compiled with the same code (`./compile.sh`) and are run from `~/password_manager_rebuild`.

**Important Reminders:**

*   **In-Memory Storage:** All data is lost when the server stops.
*   **Security:** This is a demonstration application with **minimal security**. Do not use for real passwords.


