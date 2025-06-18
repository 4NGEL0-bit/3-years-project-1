# Voting System Setup and Usage Instructions for Ubuntu 22.04

This document provides step-by-step instructions for setting up and running the Simple Voting System on Ubuntu 22.04.

## Prerequisites

1. **Java 17 Installation**
   ```bash
   sudo apt update
   sudo apt install openjdk-17-jdk -y
   ```

2. **Verify Java Installation**
   ```bash
   java -version
   ```
   You should see output indicating Java 17 is installed.

## Setup Instructions

1. **Extract the Application**
   ```bash
   unzip voting_system.zip
   cd voting_system_rmi
   ```

2. **Make Scripts Executable**
   ```bash
   chmod +x compile.sh run_server.sh run_client.sh
   ```

3. **Compile the Application**
   ```bash
   ./compile.sh
   ```
   You should see "Compilation successful. Class files are in bin"

## Running the Application

### Important Note
The client application requires a graphical desktop environment to run. It will not work in headless environments (like servers without displays or SSH sessions without X11 forwarding).

### Starting the Server

1. **Open a Terminal Window**
2. **Navigate to the Application Directory**
   ```bash
   cd ~/voting_system_rmi
   ```
3. **Start the Server**
   ```bash
   ./run_server.sh
   ```
4. **Verify Server Started**
   You should see output similar to:
   ```
   Starting Voting System Server...
   Server logging initialized
   Vote data initialized
   Voting System Server started on port 9876
   Server started on port 9876
   ```
5. **Keep this Terminal Open**
   The server will continue running in this terminal window.

### Starting the Client

1. **Open a New Terminal Window**
2. **Navigate to the Application Directory**
   ```bash
   cd ~/voting_system_rmi
   ```
3. **Start the Client**
   ```bash
   ./run_client.sh
   ```
4. **Client GUI**
   A graphical window titled "Voting System Client" should appear.

## Using the Client Application

1. **Welcome Screen**
   - Choose "Register" to create a new account, or
   - Choose "Login" if you already have an account

2. **Registration**
   - Enter a username and password
   - Click "Register"
   - After successful registration, you'll be prompted to login

3. **Login**
   - Enter your username and password
   - Click "Login"

4. **Voting Screen**
   - After login, you'll see the poll question and options
   - Select your preferred option from the dropdown
   - Click "Cast Vote" to submit your vote
   - You can only vote once per account

5. **View Results**
   - Click "View Results" to see current voting statistics
   - Results show vote counts and percentages for each option

6. **Logout**
   - Click "Logout" to end your session
   - You can log back in or register a new account

## Troubleshooting

1. **Server Connection Issues**
   - Ensure the server is running before starting the client
   - Check that port 9876 is not blocked by a firewall

2. **HeadlessException**
   - This error occurs when running the client in a non-graphical environment
   - Ensure you're running on a desktop environment with display capabilities

3. **Java Version Issues**
   - If you see class version errors, verify you're using Java 17
   - Run `java -version` to check

4. **Permission Denied**
   - If you can't execute the scripts, run `chmod +x *.sh` again

## Stopping the Application

1. **To Stop the Client**
   - Close the client window

2. **To Stop the Server**
   - Press Ctrl+C in the server terminal window
