# Simple Voting System

## Overview
This is a simple voting system application that allows users to register, login, cast votes, and view real-time voting results. The system uses a client-server architecture with direct socket communication, making it reliable and easy to use.

## Features
- User registration and login
- Secure password handling (SHA-256 hashing)
- Poll with multiple choice options
- Prevention of multiple votes from the same user
- Real-time voting results display in a separate window
- Simple and intuitive graphical user interface with optimized button sizes

## Technical Details
- Built with Java 17
- Uses socket-based communication (no RMI dependencies)
- Swing-based graphical user interface
- Concurrent handling of multiple clients
- Basic logging for security and debugging

## System Requirements
- Java 17 or higher
- **Graphical Desktop Environment** (Ubuntu Desktop, Windows, macOS, etc.)
  - The client application requires a graphical display to run
  - Will not work in headless environments (like servers without displays)

## Project Structure
- `src/main/java/com/example/votingsystem/` - Source code directory
  - `Server.java` - Server implementation that handles voting logic
  - `Client.java` - Client GUI application for user interaction
- `bin/` - Compiled class files (created after running compile.sh)
- `compile.sh` - Script to compile the Java source files
- `run_server.sh` - Script to start the server
- `run_client.sh` - Script to start the client GUI
- `architecture.md` - Detailed system architecture documentation
- `INSTRUCTIONS_Ubuntu.md` - Step-by-step instructions for Ubuntu

## User Interface Features
- **Welcome Screen**: Large, easy-to-click login and register buttons
- **Authentication Screens**: Optimized field and button sizes for better usability
- **Voting Screen**: Clean interface focused on the voting process
- **Results Display**: Separate window showing voting statistics and percentages

## Quick Start
1. Ensure Java 17 is installed
2. Run `./compile.sh` to compile the code
3. Run `./run_server.sh` in one terminal to start the server
4. Run `./run_client.sh` in another terminal to start the client GUI
5. Use the client interface to register, login, vote, and view results

See `INSTRUCTIONS_Ubuntu.md` for detailed setup and usage instructions.

## Security Notes
- This is a demonstration application and has basic security features
- Passwords are hashed using SHA-256 before storage
- The system prevents multiple votes from the same user
- All communication is validated on both client and server sides
- For production use, additional security measures would be recommended

## License
This software is provided for educational purposes only.
