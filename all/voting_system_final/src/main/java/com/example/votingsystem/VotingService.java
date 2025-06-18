package com.example.votingsystem;

import java.rmi.Remote;
import java.rmi.RemoteException;
import java.util.List;
import java.util.Map;

/**
 * RMI interface defining the remote methods for the Voting System.
 */
public interface VotingService extends Remote {

    /**
     * Registers a new voter.
     * @param username The desired username.
     * @param password The desired password.
     * @return A message indicating success or failure.
     * @throws RemoteException if a communication-related error occurs.
     */
    String registerVoter(String username, String password) throws RemoteException;

    /**
     * Authenticates a voter.
     * @param username The username.
     * @param password The password.
     * @return true if authentication is successful, false otherwise.
     * @throws RemoteException if a communication-related error occurs.
     */
    boolean loginVoter(String username, String password) throws RemoteException;

    /**
     * Gets the current poll question.
     * @return The poll question string.
     * @throws RemoteException if a communication-related error occurs.
     */
    String getPollQuestion() throws RemoteException;

    /**
     * Gets the list of options for the current poll.
     * @return A list of strings representing the voting options.
     * @throws RemoteException if a communication-related error occurs.
     */
    List<String> getPollOptions() throws RemoteException;

    /**
     * Casts a vote for a specific option.
     * Requires the voter to be logged in.
     * @param username The username of the voter casting the vote.
     * @param selectedOption The option the voter chose.
     * @return A message indicating success, failure, or if the user has already voted.
     * @throws RemoteException if a communication-related error occurs.
     */
    String castVote(String username, String selectedOption) throws RemoteException;

    /**
     * Gets the current voting results.
     * @return A map where keys are the options and values are the vote counts.
     * @throws RemoteException if a communication-related error occurs.
     */
    Map<String, Integer> getResults() throws RemoteException;

    /**
     * Checks if a specific user has already voted.
     * @param username The username to check.
     * @return true if the user has voted, false otherwise.
     * @throws RemoteException if a communication-related error occurs.
     */
    boolean hasVoted(String username) throws RemoteException;
}

