# Enterprise Chat Application

A simple enterprise chat application built with Java RMI technology, featuring real-time messaging, user authentication, and security features.

## Features

### Core Functionality
- **Real-time chat interface**: Send and receive messages instantly
- **Simple interface**: Clean, intuitive Swing-based GUI
- **Public chat rooms**: Multiple users can join shared discussion spaces (general, tech, random)
- **Message history**: View previous conversations when joining a room
- **New message notifications**: Real-time message updates every 2 seconds

### Security Features
- **User authentication**: Secure login system with username/password validation
- **Message filtering**: Automatic filtering of prohibited characters and words
- **Message size limitation**: Maximum message length of 500 characters
- **Automatic disconnection**: Session timeout after 30 minutes of inactivity
- **Password security**: SHA-256 hashing with salt for secure password storage

## Technology Stack

- **Programming Language**: Java 17+
- **Communication Protocol**: Java RMI (Remote Method Invocation)
- **User Interface**: Java Swing
- **Data Storage**: Local JSON files
- **Security**: SHA-256 password hashing, session management
- **Build System**: Shell scripts (compile.sh, run_server.sh, run_client.sh)
- **Target Platform**: Ubuntu 22.04.5 LTS

## Project Structure

```
chat_application/
├── src/
│   └── main/
│       └── java/
│           └── com/
│               └── example/
│                   └── chatapp/
│                       ├── ChatService.java (RMI interface)
│                       ├── ChatServiceImpl.java (Server implementation)
│                       ├── Server.java (RMI server launcher)
│                       ├── Client.java (Swing GUI client)
│                       ├── User.java (User data model)
│                       ├── Message.java (Message data model)
│                       └── ChatRoom.java (Chat room data model)
├── bin/ (compiled classes - created after compilation)
├── data/ (JSON storage files - created automatically)
├── compile.sh (Compilation script)
├── run_server.sh (Server startup script)
├── run_client.sh (Client startup script)
├── README.md (This file)
└── INSTRUCTIONS_Ubuntu.md (Detailed setup instructions)
```

## Quick Start

1. **Compile the application**:
   ```bash
   ./compile.sh
   ```

2. **Start the server** (in one terminal):
   ```bash
   ./run_server.sh
   ```

3. **Start the client** (in another terminal):
   ```bash
   ./run_client.sh
   ```

4. **Register a new user** or **login** with existing credentials

5. **Join a chat room** (general, tech, or random) and start chatting!

## Default Chat Rooms

The application comes with three pre-configured public chat rooms:

- **general**: General discussion room
- **tech**: Technology discussion room  
- **random**: Random chat room

## Security Features Details

### User Authentication
- Username must be 3-20 characters (alphanumeric and underscore only)
- Password must be at least 6 characters
- Passwords are hashed using SHA-256 with random salt

### Message Security
- Messages are filtered for prohibited words: "spam", "hack", "virus", "malware", "phishing"
- Prohibited characters `<>"'&` are replaced with asterisks
- Maximum message length: 500 characters
- Filtered messages are marked as "[filtered]" in the chat

### Session Management
- Automatic logout after 30 minutes of inactivity
- Session tokens are UUID-based for security
- Background cleanup of expired sessions

## System Requirements

- Ubuntu 22.04.5 LTS
- OpenJDK 17 installed at `/usr/lib/jvm/java-17-openjdk-amd64`
- Terminal access with appropriate permissions

## Troubleshooting

If you encounter issues:

1. **Compilation fails**: Ensure Java 17 is installed correctly
2. **Server won't start**: Check if port 1099 is available
3. **Client can't connect**: Ensure the server is running first
4. **GUI doesn't appear**: Check if X11 forwarding is enabled (for SSH connections)

For detailed setup instructions, see `INSTRUCTIONS_Ubuntu.md`.

## Architecture

The application follows a client-server architecture using Java RMI:

- **Server**: Manages user authentication, chat rooms, message storage, and security
- **Client**: Provides Swing-based GUI for user interaction
- **RMI**: Handles remote communication between client and server
- **Data Storage**: JSON files for persistent storage of users, rooms, and messages

## Development

The codebase is designed for maintainability and extensibility:

- Clean separation of concerns with dedicated classes for each component
- Comprehensive error handling and logging
- Thread-safe operations for concurrent user access
- Modular design allowing easy addition of new features

