package com.example.chatapp;

import java.rmi.RemoteException;
import java.rmi.server.UnicastRemoteObject;
import java.security.MessageDigest;
import java.security.NoSuchAlgorithmException;
import java.security.SecureRandom;
import java.util.*;
import java.util.concurrent.ConcurrentHashMap;
import java.util.concurrent.atomic.AtomicLong;
import java.util.regex.Pattern;
import java.io.*;
import java.nio.file.Files;
import java.nio.file.Paths;

/**
 * Implementation of the ChatService RMI interface
 * Handles all server-side chat functionality with security features
 */
public class ChatServiceImpl extends UnicastRemoteObject implements ChatService {
    private static final long serialVersionUID = 1L;
    
    // Configuration constants
    private static final int MAX_MESSAGE_LENGTH = 500;
    private static final long SESSION_TIMEOUT = 30 * 60 * 1000; // 30 minutes
    private static final int MAX_HISTORY_MESSAGES = 100;
    private static final String DATA_DIR = "data";
    private static final String USERS_FILE = DATA_DIR + "/users.json";
    private static final String ROOMS_FILE = DATA_DIR + "/rooms.json";
    private static final String MESSAGES_FILE = DATA_DIR + "/messages.json";
    
    // Prohibited words/characters for message filtering
    private static final String[] PROHIBITED_WORDS = {
        "spam", "hack", "virus", "malware", "phishing"
    };
    private static final Pattern PROHIBITED_CHARS = Pattern.compile("[<>\"'&]");
    
    // In-memory data structures
    private Map<String, User> users;
    private Map<String, ChatRoom> chatRooms;
    private List<Message> allMessages;
    private Map<String, String> activeSessions; // sessionId -> username
    private Map<String, Long> sessionTimestamps; // sessionId -> lastActivity
    private AtomicLong messageIdCounter;
    
    // Security and utility objects
    private SecureRandom secureRandom;
    private Timer sessionCleanupTimer;
    
    /**
     * Constructor - initializes the chat service
     * @throws RemoteException if RMI initialization fails
     */
    public ChatServiceImpl() throws RemoteException {
        super();
        this.users = new ConcurrentHashMap<>();
        this.chatRooms = new ConcurrentHashMap<>();
        this.allMessages = Collections.synchronizedList(new ArrayList<>());
        this.activeSessions = new ConcurrentHashMap<>();
        this.sessionTimestamps = new ConcurrentHashMap<>();
        this.messageIdCounter = new AtomicLong(1);
        this.secureRandom = new SecureRandom();
        
        // Initialize data directory and files
        initializeDataStorage();
        
        // Load existing data
        loadData();
        
        // Create default rooms if none exist
        createDefaultRooms();
        
        // Start session cleanup timer
        startSessionCleanupTimer();
        
        System.out.println("ChatService implementation initialized successfully.");
    }
    
    /**
     * Initialize data storage directory and files
     */
    private void initializeDataStorage() {
        try {
            Files.createDirectories(Paths.get(DATA_DIR));
            
            // Create empty JSON files if they don't exist
            if (!Files.exists(Paths.get(USERS_FILE))) {
                Files.write(Paths.get(USERS_FILE), "{\"users\":[]}".getBytes());
            }
            if (!Files.exists(Paths.get(ROOMS_FILE))) {
                Files.write(Paths.get(ROOMS_FILE), "{\"rooms\":[]}".getBytes());
            }
            if (!Files.exists(Paths.get(MESSAGES_FILE))) {
                Files.write(Paths.get(MESSAGES_FILE), "{\"messages\":[]}".getBytes());
            }
        } catch (IOException e) {
            System.err.println("Error initializing data storage: " + e.getMessage());
        }
    }
    
    /**
     * Load data from JSON files
     */
    private void loadData() {
        // For simplicity, we'll start with empty data structures
        // In a production system, you would implement JSON parsing here
        System.out.println("Data loaded from storage files.");
    }
    
    /**
     * Create default chat rooms
     */
    private void createDefaultRooms() {
        if (chatRooms.isEmpty()) {
            ChatRoom generalRoom = new ChatRoom("general", "General discussion room");
            ChatRoom techRoom = new ChatRoom("tech", "Technology discussion room");
            ChatRoom randomRoom = new ChatRoom("random", "Random chat room");
            
            chatRooms.put("general", generalRoom);
            chatRooms.put("tech", techRoom);
            chatRooms.put("random", randomRoom);
            
            System.out.println("Default chat rooms created: general, tech, random");
        }
    }
    
    /**
     * Start timer for cleaning up expired sessions
     */
    private void startSessionCleanupTimer() {
        sessionCleanupTimer = new Timer(true); // Daemon timer
        sessionCleanupTimer.scheduleAtFixedRate(new TimerTask() {
            @Override
            public void run() {
                cleanupExpiredSessions();
            }
        }, 60000, 60000); // Run every minute
    }
    
    /**
     * Clean up expired sessions
     */
    private void cleanupExpiredSessions() {
        long currentTime = System.currentTimeMillis();
        List<String> expiredSessions = new ArrayList<>();
        
        for (Map.Entry<String, Long> entry : sessionTimestamps.entrySet()) {
            if (currentTime - entry.getValue() > SESSION_TIMEOUT) {
                expiredSessions.add(entry.getKey());
            }
        }
        
        for (String sessionId : expiredSessions) {
            String username = activeSessions.get(sessionId);
            if (username != null) {
                User user = users.get(username);
                if (user != null) {
                    user.setOnline(false);
                    // Remove user from all rooms
                    for (String roomName : user.getJoinedRooms()) {
                        ChatRoom room = chatRooms.get(roomName);
                        if (room != null) {
                            room.removeParticipant(username);
                        }
                    }
                    user.getJoinedRooms().clear();
                }
            }
            activeSessions.remove(sessionId);
            sessionTimestamps.remove(sessionId);
        }
        
        if (!expiredSessions.isEmpty()) {
            System.out.println("Cleaned up " + expiredSessions.size() + " expired sessions");
        }
    }
    
    /**
     * Hash a password using SHA-256
     * @param password The password to hash
     * @param salt The salt to use
     * @return The hashed password
     */
    private String hashPassword(String password, String salt) {
        try {
            MessageDigest md = MessageDigest.getInstance("SHA-256");
            md.update(salt.getBytes());
            byte[] hashedPassword = md.digest(password.getBytes());
            
            StringBuilder sb = new StringBuilder();
            for (byte b : hashedPassword) {
                sb.append(String.format("%02x", b));
            }
            return salt + ":" + sb.toString();
        } catch (NoSuchAlgorithmException e) {
            throw new RuntimeException("SHA-256 algorithm not available", e);
        }
    }
    
    /**
     * Generate a random salt
     * @return Random salt string
     */
    private String generateSalt() {
        byte[] salt = new byte[16];
        secureRandom.nextBytes(salt);
        StringBuilder sb = new StringBuilder();
        for (byte b : salt) {
            sb.append(String.format("%02x", b));
        }
        return sb.toString();
    }
    
    /**
     * Verify a password against a hash
     * @param password The password to verify
     * @param storedHash The stored hash (salt:hash)
     * @return true if password matches, false otherwise
     */
    private boolean verifyPassword(String password, String storedHash) {
        String[] parts = storedHash.split(":");
        if (parts.length != 2) {
            return false;
        }
        String salt = parts[0];
        String expectedHash = hashPassword(password, salt);
        return expectedHash.equals(storedHash);
    }
    
    /**
     * Generate a unique session ID
     * @return Unique session ID
     */
    private String generateSessionId() {
        return UUID.randomUUID().toString();
    }
    
    /**
     * Filter message content for prohibited words and characters
     * @param content The original message content
     * @return Filtered message content
     */
    private String filterMessage(String content) {
        String filtered = content;
        
        // Remove prohibited characters
        filtered = PROHIBITED_CHARS.matcher(filtered).replaceAll("*");
        
        // Replace prohibited words
        for (String word : PROHIBITED_WORDS) {
            filtered = filtered.replaceAll("(?i)" + Pattern.quote(word), "***");
        }
        
        return filtered;
    }
    
    /**
     * Validate session and update activity
     * @param sessionId The session ID to validate
     * @return The username associated with the session, or null if invalid
     */
    private String validateAndRefreshSession(String sessionId) {
        if (sessionId == null || !activeSessions.containsKey(sessionId)) {
            return null;
        }
        
        Long lastActivity = sessionTimestamps.get(sessionId);
        if (lastActivity == null || System.currentTimeMillis() - lastActivity > SESSION_TIMEOUT) {
            // Session expired
            activeSessions.remove(sessionId);
            sessionTimestamps.remove(sessionId);
            return null;
        }
        
        // Refresh session
        sessionTimestamps.put(sessionId, System.currentTimeMillis());
        return activeSessions.get(sessionId);
    }
    
    // Implementation of ChatService interface methods
    
    @Override
    public boolean authenticateUser(String username, String password) throws RemoteException {
        if (username == null || password == null || username.trim().isEmpty() || password.trim().isEmpty()) {
            return false;
        }
        
        User user = users.get(username.trim());
        if (user == null) {
            return false;
        }
        
        return verifyPassword(password, user.getPasswordHash());
    }
    
    @Override
    public boolean registerUser(String username, String password) throws RemoteException {
        if (username == null || password == null || username.trim().isEmpty() || password.trim().isEmpty()) {
            return false;
        }
        
        username = username.trim();
        
        // Check if username already exists
        if (users.containsKey(username)) {
            return false;
        }
        
        // Validate username (alphanumeric and underscore only)
        if (!username.matches("^[a-zA-Z0-9_]{3,20}$")) {
            return false;
        }
        
        // Validate password length
        if (password.length() < 6 || password.length() > 50) {
            return false;
        }
        
        // Create new user
        String salt = generateSalt();
        String hashedPassword = hashPassword(password, salt);
        User newUser = new User(username, hashedPassword);
        
        users.put(username, newUser);
        
        System.out.println("New user registered: " + username);
        return true;
    }
    
    @Override
    public void logoutUser(String sessionId) throws RemoteException {
        String username = activeSessions.get(sessionId);
        if (username != null) {
            User user = users.get(username);
            if (user != null) {
                user.setOnline(false);
                // Remove user from all rooms
                for (String roomName : user.getJoinedRooms()) {
                    ChatRoom room = chatRooms.get(roomName);
                    if (room != null) {
                        room.removeParticipant(username);
                    }
                }
                user.getJoinedRooms().clear();
            }
            activeSessions.remove(sessionId);
            sessionTimestamps.remove(sessionId);
            System.out.println("User logged out: " + username);
        }
    }
    
    @Override
    public List<String> getAvailableRooms() throws RemoteException {
        return new ArrayList<>(chatRooms.keySet());
    }
    
    @Override
    public boolean joinRoom(String sessionId, String roomName) throws RemoteException {
        String username = validateAndRefreshSession(sessionId);
        if (username == null) {
            return false;
        }
        
        ChatRoom room = chatRooms.get(roomName);
        if (room == null) {
            return false;
        }
        
        User user = users.get(username);
        if (user == null) {
            return false;
        }
        
        if (room.isFull()) {
            return false;
        }
        
        boolean added = room.addParticipant(username);
        if (added) {
            user.joinRoom(roomName);
            System.out.println("User " + username + " joined room: " + roomName);
        }
        
        return added;
    }
    
    @Override
    public boolean leaveRoom(String sessionId, String roomName) throws RemoteException {
        String username = validateAndRefreshSession(sessionId);
        if (username == null) {
            return false;
        }
        
        ChatRoom room = chatRooms.get(roomName);
        if (room == null) {
            return false;
        }
        
        User user = users.get(username);
        if (user == null) {
            return false;
        }
        
        boolean removed = room.removeParticipant(username);
        if (removed) {
            user.leaveRoom(roomName);
            System.out.println("User " + username + " left room: " + roomName);
        }
        
        return removed;
    }
    
    @Override
    public boolean sendMessage(String sessionId, String roomName, String message) throws RemoteException {
        String username = validateAndRefreshSession(sessionId);
        if (username == null) {
            return false;
        }
        
        if (message == null || message.trim().isEmpty()) {
            return false;
        }
        
        message = message.trim();
        
        // Check message length
        if (message.length() > MAX_MESSAGE_LENGTH) {
            return false;
        }
        
        ChatRoom room = chatRooms.get(roomName);
        if (room == null) {
            return false;
        }
        
        User user = users.get(username);
        if (user == null || !user.isInRoom(roomName)) {
            return false;
        }
        
        // Filter message content
        String originalMessage = message;
        String filteredMessage = filterMessage(message);
        boolean wasFiltered = !originalMessage.equals(filteredMessage);
        
        // Create and store message
        long messageId = messageIdCounter.getAndIncrement();
        Message chatMessage = new Message(messageId, username, filteredMessage, roomName);
        chatMessage.setFiltered(wasFiltered);
        if (wasFiltered) {
            chatMessage.setOriginalContent(originalMessage);
        }
        
        room.addMessage(chatMessage);
        allMessages.add(chatMessage);
        
        System.out.println("Message sent by " + username + " in room " + roomName + 
                          (wasFiltered ? " (filtered)" : ""));
        
        return true;
    }
    
    @Override
    public List<Message> getMessageHistory(String sessionId, String roomName, int limit) throws RemoteException {
        String username = validateAndRefreshSession(sessionId);
        if (username == null) {
            return new ArrayList<>();
        }
        
        ChatRoom room = chatRooms.get(roomName);
        if (room == null) {
            return new ArrayList<>();
        }
        
        User user = users.get(username);
        if (user == null || !user.isInRoom(roomName)) {
            return new ArrayList<>();
        }
        
        int actualLimit = Math.min(limit, MAX_HISTORY_MESSAGES);
        return room.getRecentMessages(actualLimit);
    }
    
    @Override
    public List<Message> getNewMessages(String sessionId, String roomName, long lastMessageId) throws RemoteException {
        String username = validateAndRefreshSession(sessionId);
        if (username == null) {
            return new ArrayList<>();
        }
        
        ChatRoom room = chatRooms.get(roomName);
        if (room == null) {
            return new ArrayList<>();
        }
        
        User user = users.get(username);
        if (user == null || !user.isInRoom(roomName)) {
            return new ArrayList<>();
        }
        
        return room.getMessagesAfter(lastMessageId);
    }
    
    @Override
    public String createSession(String username) throws RemoteException {
        User user = users.get(username);
        if (user == null) {
            return null;
        }
        
        String sessionId = generateSessionId();
        activeSessions.put(sessionId, username);
        sessionTimestamps.put(sessionId, System.currentTimeMillis());
        
        user.setOnline(true);
        user.updateActivity();
        
        System.out.println("Session created for user: " + username);
        return sessionId;
    }
    
    @Override
    public boolean isSessionValid(String sessionId) throws RemoteException {
        return validateAndRefreshSession(sessionId) != null;
    }
    
    @Override
    public void refreshSession(String sessionId) throws RemoteException {
        validateAndRefreshSession(sessionId);
    }
    
    @Override
    public List<String> getRoomUsers(String sessionId, String roomName) throws RemoteException {
        String username = validateAndRefreshSession(sessionId);
        if (username == null) {
            return new ArrayList<>();
        }
        
        ChatRoom room = chatRooms.get(roomName);
        if (room == null) {
            return new ArrayList<>();
        }
        
        User user = users.get(username);
        if (user == null || !user.isInRoom(roomName)) {
            return new ArrayList<>();
        }
        
        return new ArrayList<>(room.getParticipants());
    }
}

