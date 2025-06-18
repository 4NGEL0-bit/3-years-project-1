# Socket-Based Voting System Architecture

## Overview
This document outlines the architecture for a simple socket-based voting system that allows users to register, login, cast votes, and view results in real-time.

## Components

### 1. Server
The server component will:
- Accept and manage socket connections from multiple clients
- Handle user authentication (registration and login)
- Store and validate votes
- Prevent multiple voting by the same user
- Calculate and distribute voting results
- Log all activities for security and debugging

### 2. Client
The client component will:
- Provide a graphical user interface (GUI) using Java Swing
- Connect to the server via sockets
- Allow users to register and login
- Display the poll question and voting options
- Enable authenticated users to cast votes
- Show real-time voting results
- Handle connection errors gracefully

## Communication Protocol
A simple text-based protocol will be used for client-server communication:

1. **Message Format**: `COMMAND|param1|param2|...|paramN`
2. **Commands**:
   - `REGISTER|username|password` - Register a new user
   - `LOGIN|username|password` - Authenticate a user
   - `POLL` - Request poll question and options
   - `VOTE|username|option` - Cast a vote
   - `RESULTS` - Request current voting results
   - `HASVOTED|username` - Check if a user has already voted

3. **Response Format**: `STATUS|message|[additional data]`
   - Status can be `SUCCESS` or `ERROR`
   - Message provides details about the operation
   - Additional data may include poll information or results

## Data Structures

### Server-Side Storage
1. **User Management**:
   - HashMap<String, String> for username -> hashed password
   - HashMap<String, Boolean> for tracking which users have voted

2. **Vote Storage**:
   - HashMap<String, Integer> for option -> vote count
   - String for poll question
   - List<String> for poll options

### Client-Side Storage
1. **Session Information**:
   - Current username (if logged in)
   - Connection status
   - Poll information (question and options)
   - Current results (if available)

## Security Considerations
1. **Password Security**:
   - Passwords will be hashed using SHA-256 before storage or transmission
   - No plaintext passwords will be stored

2. **Input Validation**:
   - All user inputs will be validated on both client and server
   - Special characters will be properly handled to prevent injection attacks

3. **Authentication**:
   - Users must login before casting votes
   - Server will verify user identity for all operations

## Error Handling
1. **Connection Issues**:
   - Client will attempt to reconnect if connection is lost
   - Appropriate error messages will be displayed to users

2. **Invalid Operations**:
   - Server will validate all operations and return appropriate error messages
   - Client will display error messages to users

## Logging
1. **Server Logs**:
   - All authentication attempts (success/failure)
   - All vote operations
   - Connection events
   - Errors and exceptions

2. **Client Logs**:
   - Connection events
   - User actions
   - Errors and exceptions
