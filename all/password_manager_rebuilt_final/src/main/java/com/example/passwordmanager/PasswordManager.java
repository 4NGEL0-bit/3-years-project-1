package com.example.passwordmanager;

import java.rmi.Remote;
import java.rmi.RemoteException;
import java.util.List;
import java.util.Map;

/**
 * RMI Remote Interface for the Password Manager Service.
 */
public interface PasswordManager extends Remote {

    /**
     * Registers a new user.
     * The server should hash the password before storing it.
     *
     * @param username The desired username.
     * @param password The user's plain text password.
     * @return true if registration is successful, false if the username already exists.
     * @throws RemoteException if a communication-related error occurs.
     */
    boolean registerUser(String username, String password) throws RemoteException;

    /**
     * Authenticates an existing user.
     * The server should hash the provided password and compare it with the stored hash.
     *
     * @param username The username.
     * @param password The user's plain text password.
     * @return true if authentication is successful, false otherwise.
     * @throws RemoteException if a communication-related error occurs.
     */
    boolean authenticateUser(String username, String password) throws RemoteException;

    /**
     * Adds a new password entry or updates an existing one for a specific account name.
     *
     * @param username        The username of the logged-in user.
     * @param accountName     The name of the account/service (e.g., "Gmail", "GitHub"). Acts as the key.
     * @param accountUsername The username associated with the account/service.
     * @param accountPassword The password associated with the account/service.
     * @return true if the entry was added or updated successfully.
     * @throws RemoteException if a communication-related error occurs or the user is not authenticated.
     */
    boolean addOrUpdateEntry(String username, String accountName, String accountUsername, String accountPassword) throws RemoteException;

    /**
     * Retrieves all password entries for a specific user.
     *
     * @param username The username of the logged-in user.
     * @return A List of Maps, where each Map represents an entry containing keys
     *         "accountName", "accountUsername", and "accountPassword". Returns an empty list if no entries exist.
     * @throws RemoteException if a communication-related error occurs or the user is not authenticated.
     */
    List<Map<String, String>> getEntries(String username) throws RemoteException;

    /**
     * Deletes a specific password entry for a user based on the account name.
     *
     * @param username    The username of the logged-in user.
     * @param accountName The name of the account/service entry to delete.
     * @return true if the entry was found and deleted successfully, false otherwise.
     * @throws RemoteException if a communication-related error occurs or the user is not authenticated.
     */
    boolean deleteEntry(String username, String accountName) throws RemoteException;

}

