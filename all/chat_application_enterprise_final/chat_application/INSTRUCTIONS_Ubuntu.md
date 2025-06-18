# Detailed Build and Run Instructions for Ubuntu 22.04.5 - Chat Application

This document provides detailed step-by-step instructions to compile and run the **Enterprise Chat Application** on a fresh Ubuntu 22.04.5 system.

**Assumptions:**

*   You have a working Ubuntu 22.04.5 installation (Desktop or Server).
*   You have terminal access with `sudo` privileges.
*   You have internet connectivity to download packages.
*   You have obtained the chat application code and placed it in your home directory, resulting in a `~/chat_application` folder.

**Important Note on Java Version:** The included scripts (`compile.sh`, `run_server.sh`, `run_client.sh`) have been configured to explicitly use the standard installation path for **OpenJDK 17** (`/usr/lib/jvm/java-17-openjdk-amd64`) to ensure compatibility and avoid issues with different default Java versions on your system.

---

## Step 1: Install Prerequisites

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

## Step 2: Prepare Application Files

**Crucially, all subsequent commands in these instructions MUST be run from *within* the application directory (`~/chat_application`).**

1.  **Navigate to the Application Directory:**
    *   Open a terminal (or use the existing one) and navigate into the `chat_application` directory located in your home folder:
        ```bash
        cd ~/chat_application
        ```
    *   Verify you are in the correct directory:
        ```bash
        pwd
        ```
        (Should output `/home/your_username/chat_application` or similar)
    *   List the files to confirm:
        ```bash
        ls -l
        ```
        You should see `src`, `compile.sh`, `run_server.sh`, `run_client.sh`, `README.md`, etc.

2.  **Make Scripts Executable:**
    *   While inside the `~/chat_application` directory, run:
        ```bash
        chmod +x compile.sh run_server.sh run_client.sh
        ```

---

## Step 3: Compile the Java Code

Compile the Java source files into `.class` files using the specific Java 17 compiler path defined in the script.

1.  **Run the Compile Script (from `~/chat_application`):**
    ```bash
    ./compile.sh
    ```
2.  **Check Output:** You should see messages about removing old files (if any), creating the `bin` directory, and finally "Compilation successful. Class files are in bin". If you see errors, check the Java installation path (Step 1.4) and ensure the source code hasn't been corrupted.
3.  **Verify:** A new directory named `bin` should now exist within `~/chat_application`.

---

## Step 4: Run the Server

The server needs to be running before the client can connect. The `run_server.sh` script handles starting the RMI registry and then the chat server application, using the specific Java 17 runtime path.

1.  **Open a Terminal (Terminal 1):** Ensure you are in the `~/chat_application` directory.
    ```bash
    # If needed, navigate back:
    cd ~/chat_application
    ```
2.  **Run the Server Script (from `~/chat_application`):**
    ```bash
    ./run_server.sh
    ```
3.  **Check Output:** You should see messages like:
    *   "Starting Chat Application Server..."
    *   "Chat service implementation initialized successfully."
    *   "Chat service bound to: rmi://localhost:1099/ChatService"
    *   "Chat Application Server is running..."
    *   "Press Ctrl+C to stop the server."
    *   "Default chat rooms created: general, tech, random"

4.  **Keep Server Running:** Leave this terminal open and the server running. The server will continue to run until you press `Ctrl+C` or close the terminal.

---

## Step 5: Run the Client

With the server running, you can now start the client application to connect and use the chat system.

1.  **Open a New Terminal (Terminal 2):** Press `Ctrl+Alt+T` to open a second terminal window.
2.  **Navigate to Application Directory:**
    ```bash
    cd ~/chat_application
    ```
3.  **Run the Client Script:**
    ```bash
    ./run_client.sh
    ```
4.  **Check Output:** You should see:
    *   "Starting Chat Application Client..."
    *   "Connected to chat server successfully."
    *   A GUI window should appear with the chat application login screen.

---

## Step 6: Using the Chat Application

### First Time Setup (Registration)

1.  **Register a New User:**
    *   In the login window, enter a username (3-20 characters, alphanumeric and underscore only)
    *   Enter a password (minimum 6 characters)
    *   Click the "Register" button
    *   You should see "Registration successful! You can now login."

2.  **Login:**
    *   Enter your username and password
    *   Click the "Login" button
    *   The main chat interface should appear

### Using the Chat Interface

1.  **Join a Chat Room:**
    *   In the "Chat Rooms" panel on the right, you'll see three default rooms: general, tech, random
    *   Select a room from the list
    *   Click the "Join" button
    *   You should see "Joined room: [room_name]" and the current room will be displayed at the top

2.  **Send Messages:**
    *   Type your message in the text field at the bottom
    *   Press Enter or click "Send"
    *   Your message will appear in the chat area

3.  **View Other Users:**
    *   The "Users in Room" panel shows who else is currently in your room
    *   This list updates automatically

4.  **Switch Rooms:**
    *   Click "Leave" to leave your current room
    *   Select a different room and click "Join"

5.  **Logout:**
    *   Click the "Logout" button to return to the login screen

### Multiple Users

To test with multiple users:

1.  **Start Additional Clients:**
    *   Open more terminals and run `./run_client.sh` in each
    *   Register different usernames for each client
    *   Join the same room to chat with each other

---

## Step 7: Security Features in Action

### Message Filtering
Try sending messages with prohibited content to see the filtering in action:
*   Messages containing words like "spam", "hack", "virus" will be filtered
*   Characters like `<`, `>`, `"`, `'`, `&` will be replaced with asterisks
*   Filtered messages will show "[filtered]" in the chat

### Session Timeout
*   If you remain inactive for 30 minutes, you'll be automatically logged out
*   You'll need to login again to continue chatting

### Message Size Limit
*   Messages longer than 500 characters will be rejected
*   You'll see an error message if you try to send a message that's too long

---

## Troubleshooting

### Common Issues and Solutions

1.  **"Java 17 compiler not found" Error:**
    *   Ensure OpenJDK 17 is installed: `sudo apt install openjdk-17-jdk`
    *   Check the installation path: `ls -l /usr/lib/jvm/java-17-openjdk-amd64/bin/java`
    *   If the path is different, edit the `JAVA17_HOME` variable in all three scripts

2.  **"Compilation failed" Error:**
    *   Make sure you're in the correct directory (`~/chat_application`)
    *   Check that all source files are present in `src/main/java/com/example/chatapp/`
    *   Verify Java 17 is properly installed

3.  **"Failed to connect to server" Error:**
    *   Ensure the server is running first (`./run_server.sh`)
    *   Check that port 1099 is not blocked by a firewall
    *   Verify the server started without errors

4.  **GUI Window Doesn't Appear:**
    *   If using SSH, ensure X11 forwarding is enabled: `ssh -X username@hostname`
    *   For headless systems, you may need to install a desktop environment
    *   Check that the DISPLAY environment variable is set correctly

5.  **"Class files directory 'bin' not found" Error:**
    *   Run the compilation step first: `./compile.sh`
    *   Ensure compilation completed successfully

6.  **Server Port Already in Use:**
    *   Check if another RMI registry is running: `netstat -ln | grep 1099`
    *   Kill any existing processes using port 1099
    *   Or restart your system to clear any stuck processes

### Stopping the Application

1.  **Stop the Client:**
    *   Close the GUI window, or press `Ctrl+C` in the client terminal

2.  **Stop the Server:**
    *   Press `Ctrl+C` in the server terminal
    *   The server will perform cleanup and shut down gracefully

---

## Advanced Usage

### Running Multiple Instances

You can run multiple client instances to simulate multiple users:

1.  Keep the server running in one terminal
2.  Open multiple new terminals
3.  Run `./run_client.sh` in each terminal
4.  Register different usernames for each client
5.  Join the same rooms to chat between different users

### Data Storage

The application stores data in JSON files in the `data/` directory:
*   `users.json`: User accounts and authentication data
*   `rooms.json`: Chat room information
*   `messages.json`: Message history

These files are created automatically when the server starts.

### Customization

You can modify the application behavior by editing the source files:
*   Change session timeout in `ChatServiceImpl.java` (SESSION_TIMEOUT constant)
*   Modify message refresh interval in `Client.java` (MESSAGE_REFRESH_INTERVAL constant)
*   Add new prohibited words in `ChatServiceImpl.java` (PROHIBITED_WORDS array)
*   Change maximum message length in `ChatServiceImpl.java` (MAX_MESSAGE_LENGTH constant)

After making changes, recompile with `./compile.sh` and restart the server.

---

## System Requirements Summary

*   **Operating System:** Ubuntu 22.04.5 LTS
*   **Java:** OpenJDK 17 (installed at `/usr/lib/jvm/java-17-openjdk-amd64`)
*   **Memory:** Minimum 512MB RAM (1GB recommended)
*   **Disk Space:** Approximately 50MB for application and data
*   **Network:** Port 1099 must be available for RMI registry
*   **Display:** X11 support for GUI (if using SSH, enable X11 forwarding)

This completes the setup and usage instructions for the Enterprise Chat Application. The application is now ready for use in your Ubuntu 22.04.5 environment.

