package com.example.passwordmanager;

import java.rmi.RemoteException;
import java.rmi.server.UnicastRemoteObject;
import java.security.MessageDigest;
import java.security.NoSuchAlgorithmException;
import java.util.ArrayList;
import java.util.Base64;
import java.util.List;
import java.util.Map;
import java.util.concurrent.ConcurrentHashMap;

/**
 * Implementation of the PasswordManager RMI service.
 * Uses in-memory storage (data is lost when server stops).
 */
public class PasswordManagerImpl extends UnicastRemoteObject implements PasswordManager {

    // In-memory storage
    // Structure: <Username, HashedPassword>
    private final Map<String, String> userCredentials = new ConcurrentHashMap<>();
    // Structure: <Username, List<Map<AccountName, EntryDetails>>>
    // EntryDetails Map: <"accountName", "accountUsername", "accountPassword">
    private final Map<String, List<Map<String, String>>> userEntries = new ConcurrentHashMap<>();

    // Constructor must throw RemoteException
    protected PasswordManagerImpl() throws RemoteException {
        super();
        // Add a default test user for convenience during development/testing?
        // try {
        //     registerUser("test", "test");
        // } catch (RemoteException e) { /* ignore */ }
    }

    // Helper method for hashing passwords
    private String hashPassword(String password) {
        try {
            MessageDigest digest = MessageDigest.getInstance("SHA-256");
            byte[] hash = digest.digest(password.getBytes());
            return Base64.getEncoder().encodeToString(hash);
        } catch (NoSuchAlgorithmException e) {
            // Log this error on the server side
            System.err.println("ERROR: SHA-256 algorithm not found! " + e.getMessage());
            // In a real application, this should probably prevent operation
            return null; // Indicate failure
        }
    }

    @Override
    public boolean registerUser(String username, String password) throws RemoteException {
        System.out.println("Server: Received registration request for user: " + username);
        if (username == null || username.trim().isEmpty() || password == null || password.isEmpty()) {
            System.out.println("Server: Registration failed - username or password empty.");
            return false;
        }
        String hashedPassword = hashPassword(password);
        if (hashedPassword == null) {
             System.out.println("Server: Registration failed - password hashing error.");
            return false; // Hashing failed
        }

        // Check if user already exists (case-insensitive check might be better)
        if (userCredentials.containsKey(username)) {
            System.out.println("Server: Registration failed - username already exists.");
            return false;
        }

        // Store user and initialize their entry list
        userCredentials.put(username, hashedPassword);
        userEntries.put(username, new ArrayList<>()); // Initialize with empty list
        System.out.println("Server: User registered successfully: " + username);
        return true;
    }

    @Override
    public boolean authenticateUser(String username, String password) throws RemoteException {
        System.out.println("Server: Received authentication request for user: " + username);
        if (username == null || password == null) {
            System.out.println("Server: Authentication failed - null username or password.");
            return false;
        }
        String storedHashedPassword = userCredentials.get(username);
        if (storedHashedPassword == null) {
            System.out.println("Server: Authentication failed - user not found.");
            return false; // User not found
        }

        String providedHashedPassword = hashPassword(password);
        if (providedHashedPassword == null) {
            System.out.println("Server: Authentication failed - password hashing error.");
            return false; // Hashing failed
        }

        boolean authenticated = storedHashedPassword.equals(providedHashedPassword);
        if (authenticated) {
            System.out.println("Server: Authentication successful for user: " + username);
        } else {
            System.out.println("Server: Authentication failed - incorrect password for user: " + username);
        }
        return authenticated;
    }

    @Override
    public boolean addOrUpdateEntry(String username, String accountName, String accountUsername, String accountPassword) throws RemoteException {
        System.out.println("Server: Received add/update request from user: " + username + " for account: " + accountName);
        // Basic check if user exists (could rely on prior authentication)
        if (!userCredentials.containsKey(username)) {
            System.out.println("Server: Add/Update failed - user not found: " + username);
            return false;
        }
        if (accountName == null || accountName.trim().isEmpty()) {
             System.out.println("Server: Add/Update failed - account name is empty.");
            return false;
        }

        List<Map<String, String>> entries = userEntries.computeIfAbsent(username, k -> new ArrayList<>());

        // Check if entry for this accountName already exists
        Map<String, String> existingEntry = null;
        int existingIndex = -1;
        for (int i = 0; i < entries.size(); i++) {
            if (entries.get(i).get("accountName").equals(accountName)) {
                existingEntry = entries.get(i);
                existingIndex = i;
                break;
            }
        }

        Map<String, String> newEntry = new ConcurrentHashMap<>();
        newEntry.put("accountName", accountName);
        newEntry.put("accountUsername", accountUsername != null ? accountUsername : "");
        newEntry.put("accountPassword", accountPassword != null ? accountPassword : "");

        if (existingEntry != null) {
            // Update existing entry
            entries.set(existingIndex, newEntry);
            System.out.println("Server: Updated entry for account: " + accountName + " for user: " + username);
        } else {
            // Add new entry
            entries.add(newEntry);
            System.out.println("Server: Added new entry for account: " + accountName + " for user: " + username);
        }
        return true;
    }

    @Override
    public List<Map<String, String>> getEntries(String username) throws RemoteException {
        System.out.println("Server: Received get entries request from user: " + username);
        if (!userCredentials.containsKey(username)) {
             System.out.println("Server: Get entries failed - user not found: " + username);
            // Or throw an exception indicating user not found/authenticated
            return new ArrayList<>(); // Return empty list if user doesn't exist
        }
        // Return a copy to prevent modification of the internal list via the reference
        List<Map<String, String>> entries = userEntries.getOrDefault(username, new ArrayList<>());
        System.out.println("Server: Returning " + entries.size() + " entries for user: " + username);
        return new ArrayList<>(entries); // Return a defensive copy
    }

    @Override
    public boolean deleteEntry(String username, String accountName) throws RemoteException {
        System.out.println("Server: Received delete request from user: " + username + " for account: " + accountName);
        if (!userCredentials.containsKey(username)) {
            System.out.println("Server: Delete failed - user not found: " + username);
            return false;
        }
        if (accountName == null || accountName.trim().isEmpty()) {
            System.out.println("Server: Delete failed - account name is empty.");
            return false;
        }

        List<Map<String, String>> entries = userEntries.get(username);
        if (entries == null) {
            System.out.println("Server: Delete failed - no entries found for user: " + username);
            return false; // No entries for this user
        }

        boolean removed = entries.removeIf(entry -> accountName.equals(entry.get("accountName")));

        if (removed) {
            System.out.println("Server: Deleted entry for account: " + accountName + " for user: " + username);
        } else {
            System.out.println("Server: Delete failed - entry not found for account: " + accountName + " for user: " + username);
        }
        return removed;
    }
}

