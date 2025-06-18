# Simple RMI Password Manager (Rebuilt Version)

## Overview

This project is a simple password manager application built using Java RMI (Remote Method Invocation) for client-server communication. It allows users to register, login, and manage basic password entries (account name, username, password) for different services.

This version has been rebuilt to address previous stability issues and includes:
*   Explicit use of Java 17 in scripts for compatibility.
*   Robust error checking in startup scripts.
*   A graphical user interface (GUI) built with Java Swing.
*   A welcome screen for initial connection and user action selection (Login/Register).
*   A main application screen displaying stored entries in a structured table (JTable).

## Features

*   User Registration & Login (using SHA-256 password hashing on the server).
*   Add, Update, View, and Delete password entries.
*   Client-Server communication via Java RMI.
*   Basic Swing GUI with welcome screen and table display.

## Important Security Limitations

**This application is intended for educational purposes and demonstrating Java RMI concepts ONLY. It has significant security flaws and MUST NOT be used to store real passwords.**

*   **In-Memory Storage:** All user credentials and password entries are stored only in the server's memory. All data is lost when the server process stops.
*   **Password Visibility:** Password entries (including the password field itself) are transmitted between client and server and potentially displayed in the client GUI table without strong encryption. **This is highly insecure.**
*   **Basic Hashing:** User login passwords use SHA-256 hashing, but without salting, making them more vulnerable to certain attacks compared to modern password storage practices.
*   **Unencrypted RMI Traffic:** Standard Java RMI communication is not encrypted by default. Sensitive data could be intercepted on the network if not running purely on localhost or without additional security layers (like SSL/TLS, which is not implemented here).
*   **No Input Sanitization:** Limited input validation is performed.

## Project Structure

```
password_manager_rebuild/
├── src/                     # Java source code
│   └── main/
│       └── java/
│           └── com/
│               └── example/
│                   └── passwordmanager/
│                       ├── PasswordManager.java     # RMI Interface
│                       ├── PasswordManagerImpl.java # RMI Implementation
│                       ├── Server.java              # Server main class
│                       └── Client.java              # Client GUI main class
├── bin/                     # Compiled .class files (created by compile.sh)
├── compile.sh             # Script to compile Java code
├── run_server.sh          # Script to start RMI registry and server
├── run_client.sh          # Script to start the client GUI
├── README.md              # This file
└── INSTRUCTIONS_Ubuntu.md # Detailed build/run steps for Ubuntu 22.04
```

## Usage

Please refer to `INSTRUCTIONS_Ubuntu.md` for detailed steps on how to compile and run the application on Ubuntu 22.04.5.

