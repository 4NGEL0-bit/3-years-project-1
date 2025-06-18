package com.example.votingsystem;

import java.rmi.RemoteException;
import java.rmi.server.UnicastRemoteObject;
import java.security.MessageDigest;
import java.security.NoSuchAlgorithmException;
import java.util.ArrayList;
import java.util.Collections;
import java.util.List;
import java.util.Map;
import java.util.concurrent.ConcurrentHashMap;
import java.util.logging.FileHandler;
import java.util.logging.Level;
import java.util.logging.Logger;
import java.util.logging.SimpleFormatter;
import java.io.IOException;
import java.nio.charset.StandardCharsets;

/**
 * Implementation of the VotingService RMI interface.
 * Handles user authentication, vote casting, results calculation,
 * and basic logging.
 */
public class VotingServiceImpl extends UnicastRemoteObject implements VotingService {

    // --- Data Storage (In-Memory) ---
    // Stores registered voters: username -> hashedPassword
    private final Map<String, String> registeredVoters = new ConcurrentHashMap<>();
    // Stores votes: option -> count
    private final Map<String, Integer> voteCounts = new ConcurrentHashMap<>();
    // Stores which users have voted: username -> true
    private final Map<String, Boolean> hasUserVoted = new ConcurrentHashMap<>();

    // --- Poll Configuration ---
    private final String pollQuestion = "What is your favorite programming language?";
    private final List<String> pollOptions = List.of("Java", "Python", "JavaScript", "C++", "Other");

    // --- Logging ---
    private static final Logger LOGGER = Logger.getLogger(VotingServiceImpl.class.getName());
    private FileHandler fileHandler;

    /**
     * Constructor: Initializes vote counts and sets up logging.
     * @throws RemoteException if object export fails.
     */
    protected VotingServiceImpl() throws RemoteException {
        super(); // Export this object

        // Initialize vote counts for each option to 0
        for (String option : pollOptions) {
            voteCounts.put(option, 0);
        }

        // Setup logging
        try {
            // Log to a file named voting_system.log in the current directory
            fileHandler = new FileHandler("voting_system.log", true); // Append mode
            SimpleFormatter formatter = new SimpleFormatter();
            fileHandler.setFormatter(formatter);
            LOGGER.addHandler(fileHandler);
            LOGGER.setLevel(Level.INFO); // Log INFO level and above
            LOGGER.info("VotingServiceImpl initialized and logging started.");
        } catch (IOException e) {
            LOGGER.log(Level.SEVERE, "Failed to initialize logger FileHandler.", e);
            // Continue without file logging if setup fails
        }

        // Add a default user for testing (optional)
        // registeredVoters.put("testuser", hashPassword("password123"));
        // LOGGER.info("Added default test user.");
    }

    // --- Helper Methods ---

    /**
     * Hashes a password using SHA-256.
     * @param password The plain text password.
     * @return The hashed password as a hex string, or null if hashing fails.
     */
    private String hashPassword(String password) {
        if (password == null || password.isEmpty()) {
            return null;
        }
        try {
            MessageDigest digest = MessageDigest.getInstance("SHA-256");
            byte[] hash = digest.digest(password.getBytes(StandardCharsets.UTF_8));
            StringBuilder hexString = new StringBuilder(2 * hash.length);
            for (byte b : hash) {
                String hex = Integer.toHexString(0xff & b);
                if (hex.length() == 1) {
                    hexString.append("0");
                }
                hexString.append(hex);
            }
            return hexString.toString();
        } catch (NoSuchAlgorithmException e) {
            LOGGER.log(Level.SEVERE, "SHA-256 Algorithm not found", e);
            return null; // Should not happen with standard Java installs
        }
    }

    // --- RMI Interface Methods Implementation ---

    @Override
    public synchronized String registerVoter(String username, String password) throws RemoteException {
        LOGGER.log(Level.INFO, "Registration attempt for user: {0}", username);
        if (username == null || username.trim().isEmpty() || password == null || password.isEmpty()) {
            // Fixed logging call
            LOGGER.log(Level.WARNING, "Registration failed for user {0}: Invalid username or password.", username);
            return "Registration failed: Username and password cannot be empty.";
        }
        if (registeredVoters.containsKey(username)) {
            // Fixed logging call
            LOGGER.log(Level.WARNING, "Registration failed for user {0}: Username already exists.", username);
            return "Registration failed: Username already exists.";
        }
        String hashedPassword = hashPassword(password);
        if (hashedPassword == null) {
             // Fixed logging call
             LOGGER.log(Level.SEVERE, "Registration failed for user {0}: Password hashing failed.", username);
            return "Registration failed: Server error during password hashing.";
        }
        registeredVoters.put(username, hashedPassword);
        LOGGER.log(Level.INFO, "User {0} registered successfully.", username);
        return "User registered successfully.";
    }

    @Override
    public boolean loginVoter(String username, String password) throws RemoteException {
        LOGGER.log(Level.INFO, "Login attempt for user: {0}", username);
        if (username == null || username.trim().isEmpty() || password == null || password.isEmpty()) {
            // Fixed logging call
            LOGGER.log(Level.WARNING, "Login failed for user {0}: Invalid username or password.", username);
            return false;
        }
        String storedHash = registeredVoters.get(username);
        if (storedHash == null) {
            // Fixed logging call
            LOGGER.log(Level.WARNING, "Login failed for user {0}: User not found.", username);
            return false; // User not found
        }
        String inputHash = hashPassword(password);
        if (inputHash != null && inputHash.equals(storedHash)) {
            LOGGER.log(Level.INFO, "User {0} logged in successfully.", username);
            return true; // Login successful
        } else {
            // Fixed logging call
            LOGGER.log(Level.WARNING, "Login failed for user {0}: Incorrect password.", username);
            return false; // Incorrect password or hashing error
        }
    }

    @Override
    public String getPollQuestion() throws RemoteException {
        LOGGER.fine("Poll question requested.");
        return pollQuestion;
    }

    @Override
    public List<String> getPollOptions() throws RemoteException {
        LOGGER.fine("Poll options requested.");
        // Return an immutable copy to prevent modification
        return Collections.unmodifiableList(new ArrayList<>(pollOptions));
    }

    @Override
    public synchronized String castVote(String username, String selectedOption) throws RemoteException {
        LOGGER.log(Level.INFO, "Vote attempt by user: {0} for option: {1}", new Object[]{username, selectedOption});

        // 1. Validate input
        if (username == null || username.trim().isEmpty() || selectedOption == null || selectedOption.trim().isEmpty()) {
            // Fixed logging call
            LOGGER.log(Level.WARNING, "Vote failed for user {0}: Invalid username or option.", username);
            return "Vote failed: Invalid username or option provided.";
        }

        // 2. Check if user is registered (basic check, login should precede this)
        if (!registeredVoters.containsKey(username)) {
            // Fixed logging call
            LOGGER.log(Level.WARNING, "Vote failed for user {0}: User not registered.", username);
            return "Vote failed: User not registered."; // Should ideally be caught by login
        }

        // 3. Check if user has already voted
        if (hasUserVoted.containsKey(username)) {
            // Fixed logging call
            LOGGER.log(Level.WARNING, "Vote failed for user {0}: User has already voted.", username);
            return "Vote failed: You have already cast your vote.";
        }

        // 4. Validate the selected option
        if (!pollOptions.contains(selectedOption)) {
            // Fixed logging call
            LOGGER.log(Level.WARNING, "Vote failed for user {0}: Invalid option ''{1}'' selected.", new Object[]{username, selectedOption});
            return "Vote failed: Invalid option selected.";
        }

        // 5. Record the vote
        voteCounts.compute(selectedOption, (key, count) -> (count == null) ? 1 : count + 1);
        hasUserVoted.put(username, true);

        LOGGER.log(Level.INFO, "Vote successfully cast by user: {0} for option: {1}", new Object[]{username, selectedOption});
        return "Vote cast successfully for: " + selectedOption;
    }

    @Override
    public Map<String, Integer> getResults() throws RemoteException {
        LOGGER.fine("Results requested.");
        // Return an immutable copy
        return Collections.unmodifiableMap(new ConcurrentHashMap<>(voteCounts));
    }

    @Override
    public boolean hasVoted(String username) throws RemoteException {
        LOGGER.fine(() -> "Checking if user " + username + " has voted.");
        return hasUserVoted.containsKey(username);
    }
}

