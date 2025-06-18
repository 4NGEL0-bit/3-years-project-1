package com.example.chatapp;

import java.rmi.Remote;
import java.rmi.RemoteException;
import java.util.List;

/**
 * RMI interface for the Chat Service
 * Defines all remote methods available to clients
 */
public interface ChatService extends Remote {
    
    // Authentication methods
    /**
     * Authenticate a user with username and password
     * @param username The username
     * @param password The password
     * @return true if authentication successful, false otherwise
     * @throws RemoteException if RMI communication fails
     */
    boolean authenticateUser(String username, String password) throws RemoteException;
    
    /**
     * Register a new user
     * @param username The desired username
     * @param password The password
     * @return true if registration successful, false if username already exists
     * @throws RemoteException if RMI communication fails
     */
    boolean registerUser(String username, String password) throws RemoteException;
    
    /**
     * Logout a user and invalidate their session
     * @param sessionId The session ID to logout
     * @throws RemoteException if RMI communication fails
     */
    void logoutUser(String sessionId) throws RemoteException;
    
    // Chat room management
    /**
     * Get list of available chat rooms
     * @return List of room names
     * @throws RemoteException if RMI communication fails
     */
    List<String> getAvailableRooms() throws RemoteException;
    
    /**
     * Join a chat room
     * @param sessionId The user's session ID
     * @param roomName The room to join
     * @return true if successfully joined, false otherwise
     * @throws RemoteException if RMI communication fails
     */
    boolean joinRoom(String sessionId, String roomName) throws RemoteException;
    
    /**
     * Leave a chat room
     * @param sessionId The user's session ID
     * @param roomName The room to leave
     * @return true if successfully left, false otherwise
     * @throws RemoteException if RMI communication fails
     */
    boolean leaveRoom(String sessionId, String roomName) throws RemoteException;
    
    // Message operations
    /**
     * Send a message to a chat room
     * @param sessionId The user's session ID
     * @param roomName The target room
     * @param message The message content
     * @return true if message sent successfully, false otherwise
     * @throws RemoteException if RMI communication fails
     */
    boolean sendMessage(String sessionId, String roomName, String message) throws RemoteException;
    
    /**
     * Get message history for a room
     * @param sessionId The user's session ID
     * @param roomName The room name
     * @param limit Maximum number of messages to retrieve
     * @return List of messages
     * @throws RemoteException if RMI communication fails
     */
    List<Message> getMessageHistory(String sessionId, String roomName, int limit) throws RemoteException;
    
    /**
     * Get new messages since a specific message ID
     * @param sessionId The user's session ID
     * @param roomName The room name
     * @param lastMessageId The ID of the last message the client has
     * @return List of new messages
     * @throws RemoteException if RMI communication fails
     */
    List<Message> getNewMessages(String sessionId, String roomName, long lastMessageId) throws RemoteException;
    
    // Session management
    /**
     * Create a new session for a user
     * @param username The username
     * @return Session ID string
     * @throws RemoteException if RMI communication fails
     */
    String createSession(String username) throws RemoteException;
    
    /**
     * Check if a session is valid
     * @param sessionId The session ID to check
     * @return true if valid, false otherwise
     * @throws RemoteException if RMI communication fails
     */
    boolean isSessionValid(String sessionId) throws RemoteException;
    
    /**
     * Refresh a session to extend its timeout
     * @param sessionId The session ID to refresh
     * @throws RemoteException if RMI communication fails
     */
    void refreshSession(String sessionId) throws RemoteException;
    
    /**
     * Get list of users in a specific room
     * @param sessionId The user's session ID
     * @param roomName The room name
     * @return List of usernames in the room
     * @throws RemoteException if RMI communication fails
     */
    List<String> getRoomUsers(String sessionId, String roomName) throws RemoteException;
}

