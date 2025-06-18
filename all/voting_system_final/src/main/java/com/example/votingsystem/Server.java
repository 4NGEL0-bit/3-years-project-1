package com.example.votingsystem;

import java.io.*;
import java.net.*;
import java.security.MessageDigest;
import java.security.NoSuchAlgorithmException;
import java.util.*;
import java.util.concurrent.ConcurrentHashMap;
import java.util.logging.*;

/**
 * Server for the Simple Voting System.
 * Handles client connections, user authentication, vote management, and results distribution.
 */
public class Server {
    // Server configuration
    private static final int PORT = 9876;
    private static final Logger LOGGER = Logger.getLogger(Server.class.getName());
    
    // Data storage
    private final Map<String, String> users = new ConcurrentHashMap<>(); // username -> hashed password
    private final Map<String, Boolean> hasVoted = new ConcurrentHashMap<>(); // username -> has voted flag
    private final Map<String, Integer> voteResults = new ConcurrentHashMap<>(); // option -> count
    
    // Poll configuration
    private final String pollQuestion = "What is your favorite programming language?";
    private final List<String> pollOptions = Arrays.asList("Java", "Python", "JavaScript", "C++", "Other");
    
    // Server state
    private ServerSocket serverSocket;
    private boolean running = false;
    
    /**
     * Main method to start the server.
     */
    public static void main(String[] args) {
        Server server = new Server();
        server.setupLogging();
        server.initializeData();
        server.start();
    }
    
    /**
     * Set up logging configuration.
     */
    private void setupLogging() {
        try {
            // Configure file handler for logging
            FileHandler fileHandler = new FileHandler("voting_server.log", true);
            fileHandler.setFormatter(new SimpleFormatter());
            LOGGER.addHandler(fileHandler);
            LOGGER.setLevel(Level.INFO);
            LOGGER.info("Server logging initialized");
        } catch (IOException e) {
            System.err.println("Failed to set up logging: " + e.getMessage());
            // Continue without file logging
        }
    }
    
    /**
     * Initialize vote counts and any test data.
     */
    private void initializeData() {
        // Initialize vote counts for each option
        for (String option : pollOptions) {
            voteResults.put(option, 0);
        }
        LOGGER.info("Vote data initialized");
        
        // Add a test user (optional)
        // users.put("test", hashPassword("password"));
    }
    
    /**
     * Start the server and listen for client connections.
     */
    public void start() {
        try {
            serverSocket = new ServerSocket(PORT);
            running = true;
            
            System.out.println("Voting System Server started on port " + PORT);
            LOGGER.info("Server started on port " + PORT);
            
            // Accept client connections
            while (running) {
                try {
                    Socket clientSocket = serverSocket.accept();
                    System.out.println("New client connected: " + clientSocket.getInetAddress().getHostAddress());
                    LOGGER.info("Client connected: " + clientSocket.getInetAddress().getHostAddress());
                    
                    // Handle client in a new thread
                    ClientHandler handler = new ClientHandler(clientSocket);
                    new Thread(handler).start();
                } catch (IOException e) {
                    if (running) {
                        LOGGER.log(Level.SEVERE, "Error accepting client connection", e);
                    }
                }
            }
        } catch (IOException e) {
            LOGGER.log(Level.SEVERE, "Server startup failed", e);
            System.err.println("Server startup failed: " + e.getMessage());
        } finally {
            stop();
        }
    }
    
    /**
     * Stop the server.
     */
    public void stop() {
        running = false;
        try {
            if (serverSocket != null && !serverSocket.isClosed()) {
                serverSocket.close();
            }
            LOGGER.info("Server stopped");
        } catch (IOException e) {
            LOGGER.log(Level.SEVERE, "Error stopping server", e);
        }
    }
    
    /**
     * Hash a password using SHA-256.
     */
    private String hashPassword(String password) {
        try {
            MessageDigest digest = MessageDigest.getInstance("SHA-256");
            byte[] hash = digest.digest(password.getBytes());
            StringBuilder hexString = new StringBuilder();
            for (byte b : hash) {
                String hex = Integer.toHexString(0xff & b);
                if (hex.length() == 1) hexString.append('0');
                hexString.append(hex);
            }
            return hexString.toString();
        } catch (NoSuchAlgorithmException e) {
            LOGGER.log(Level.SEVERE, "Password hashing failed", e);
            return null;
        }
    }
    
    /**
     * Inner class to handle client connections.
     */
    private class ClientHandler implements Runnable {
        private final Socket clientSocket;
        private PrintWriter out;
        private BufferedReader in;
        
        public ClientHandler(Socket socket) {
            this.clientSocket = socket;
        }
        
        @Override
        public void run() {
            try {
                // Set up communication channels
                out = new PrintWriter(clientSocket.getOutputStream(), true);
                in = new BufferedReader(new InputStreamReader(clientSocket.getInputStream()));
                
                String inputLine;
                // Process client messages
                while ((inputLine = in.readLine()) != null) {
                    String response = processRequest(inputLine);
                    out.println(response);
                }
            } catch (IOException e) {
                LOGGER.log(Level.WARNING, "Client disconnected: " + e.getMessage());
            } finally {
                closeConnection();
            }
        }
        
        /**
         * Process client requests according to the protocol.
         */
        private String processRequest(String request) {
            LOGGER.info("Received request: " + request);
            String[] parts = request.split("\\|");
            String command = parts[0];
            
            try {
                switch (command) {
                    case "REGISTER":
                        return handleRegister(parts);
                    case "LOGIN":
                        return handleLogin(parts);
                    case "POLL":
                        return handlePollRequest();
                    case "VOTE":
                        return handleVote(parts);
                    case "RESULTS":
                        return handleResultsRequest();
                    case "HASVOTED":
                        return handleHasVotedRequest(parts);
                    default:
                        return "ERROR|Unknown command: " + command;
                }
            } catch (Exception e) {
                LOGGER.log(Level.SEVERE, "Error processing request: " + request, e);
                return "ERROR|Server error: " + e.getMessage();
            }
        }
        
        /**
         * Handle user registration.
         */
        private String handleRegister(String[] parts) {
            if (parts.length < 3) {
                return "ERROR|Invalid registration format";
            }
            
            String username = parts[1];
            String password = parts[2];
            
            // Validate input
            if (username == null || username.trim().isEmpty() || password == null || password.isEmpty()) {
                LOGGER.warning("Registration failed: Invalid username or password");
                return "ERROR|Username and password cannot be empty";
            }
            
            // Check if user already exists
            if (users.containsKey(username)) {
                LOGGER.warning("Registration failed: Username already exists: " + username);
                return "ERROR|Username already exists";
            }
            
            // Hash password and store user
            String hashedPassword = hashPassword(password);
            if (hashedPassword == null) {
                return "ERROR|Server error during password hashing";
            }
            
            users.put(username, hashedPassword);
            LOGGER.info("User registered successfully: " + username);
            return "SUCCESS|User registered successfully";
        }
        
        /**
         * Handle user login.
         */
        private String handleLogin(String[] parts) {
            if (parts.length < 3) {
                return "ERROR|Invalid login format";
            }
            
            String username = parts[1];
            String password = parts[2];
            
            // Validate input
            if (username == null || username.trim().isEmpty() || password == null || password.isEmpty()) {
                LOGGER.warning("Login failed: Invalid username or password");
                return "ERROR|Username and password cannot be empty";
            }
            
            // Check if user exists
            String storedHash = users.get(username);
            if (storedHash == null) {
                LOGGER.warning("Login failed: User not found: " + username);
                return "ERROR|Invalid username or password";
            }
            
            // Verify password
            String inputHash = hashPassword(password);
            if (inputHash != null && inputHash.equals(storedHash)) {
                LOGGER.info("User logged in successfully: " + username);
                return "SUCCESS|Login successful";
            } else {
                LOGGER.warning("Login failed: Incorrect password for user: " + username);
                return "ERROR|Invalid username or password";
            }
        }
        
        /**
         * Handle poll information request.
         */
        private String handlePollRequest() {
            StringBuilder response = new StringBuilder("SUCCESS|");
            response.append(pollQuestion).append("|");
            
            // Add options
            for (String option : pollOptions) {
                response.append(option).append("|");
            }
            
            // Remove trailing separator
            if (response.charAt(response.length() - 1) == '|') {
                response.deleteCharAt(response.length() - 1);
            }
            
            return response.toString();
        }
        
        /**
         * Handle vote casting.
         */
        private String handleVote(String[] parts) {
            if (parts.length < 3) {
                return "ERROR|Invalid vote format";
            }
            
            String username = parts[1];
            String selectedOption = parts[2];
            
            // Validate user
            if (!users.containsKey(username)) {
                LOGGER.warning("Vote failed: User not registered: " + username);
                return "ERROR|User not registered";
            }
            
            // Check if user has already voted
            if (hasVoted.containsKey(username)) {
                LOGGER.warning("Vote failed: User has already voted: " + username);
                return "ERROR|You have already cast your vote";
            }
            
            // Validate option
            if (!pollOptions.contains(selectedOption)) {
                LOGGER.warning("Vote failed: Invalid option selected: " + selectedOption);
                return "ERROR|Invalid option selected";
            }
            
            // Record vote
            voteResults.compute(selectedOption, (key, count) -> count + 1);
            hasVoted.put(username, true);
            
            LOGGER.info("Vote recorded for user " + username + ": " + selectedOption);
            return "SUCCESS|Vote cast successfully for: " + selectedOption;
        }
        
        /**
         * Handle results request.
         */
        private String handleResultsRequest() {
            StringBuilder response = new StringBuilder("SUCCESS|");
            
            // Add results for each option
            for (String option : pollOptions) {
                Integer count = voteResults.get(option);
                response.append(option).append(":").append(count).append("|");
            }
            
            // Remove trailing separator
            if (response.charAt(response.length() - 1) == '|') {
                response.deleteCharAt(response.length() - 1);
            }
            
            return response.toString();
        }
        
        /**
         * Handle has voted check.
         */
        private String handleHasVotedRequest(String[] parts) {
            if (parts.length < 2) {
                return "ERROR|Invalid format";
            }
            
            String username = parts[1];
            boolean voted = hasVoted.containsKey(username);
            
            return "SUCCESS|" + voted;
        }
        
        /**
         * Close the client connection.
         */
        private void closeConnection() {
            try {
                if (out != null) out.close();
                if (in != null) in.close();
                if (clientSocket != null && !clientSocket.isClosed()) {
                    clientSocket.close();
                    LOGGER.info("Client connection closed: " + clientSocket.getInetAddress().getHostAddress());
                }
            } catch (IOException e) {
                LOGGER.log(Level.WARNING, "Error closing client connection", e);
            }
        }
    }
}
