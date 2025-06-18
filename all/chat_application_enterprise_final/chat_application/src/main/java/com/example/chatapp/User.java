package com.example.chatapp;

import java.io.Serializable;
import java.util.HashSet;
import java.util.Set;

/**
 * User data model representing a chat application user
 */
public class User implements Serializable {
    private static final long serialVersionUID = 1L;
    
    private String username;
    private String passwordHash;
    private long lastActivity;
    private boolean isOnline;
    private Set<String> joinedRooms;
    private long registrationDate;
    
    /**
     * Default constructor
     */
    public User() {
        this.joinedRooms = new HashSet<>();
        this.isOnline = false;
        this.registrationDate = System.currentTimeMillis();
    }
    
    /**
     * Constructor with username and password hash
     * @param username The username
     * @param passwordHash The hashed password
     */
    public User(String username, String passwordHash) {
        this();
        this.username = username;
        this.passwordHash = passwordHash;
        this.lastActivity = System.currentTimeMillis();
    }
    
    // Getters and setters
    public String getUsername() {
        return username;
    }
    
    public void setUsername(String username) {
        this.username = username;
    }
    
    public String getPasswordHash() {
        return passwordHash;
    }
    
    public void setPasswordHash(String passwordHash) {
        this.passwordHash = passwordHash;
    }
    
    public long getLastActivity() {
        return lastActivity;
    }
    
    public void setLastActivity(long lastActivity) {
        this.lastActivity = lastActivity;
    }
    
    public boolean isOnline() {
        return isOnline;
    }
    
    public void setOnline(boolean online) {
        isOnline = online;
    }
    
    public Set<String> getJoinedRooms() {
        return joinedRooms;
    }
    
    public void setJoinedRooms(Set<String> joinedRooms) {
        this.joinedRooms = joinedRooms;
    }
    
    public long getRegistrationDate() {
        return registrationDate;
    }
    
    public void setRegistrationDate(long registrationDate) {
        this.registrationDate = registrationDate;
    }
    
    /**
     * Add a room to the user's joined rooms
     * @param roomName The room to join
     */
    public void joinRoom(String roomName) {
        this.joinedRooms.add(roomName);
    }
    
    /**
     * Remove a room from the user's joined rooms
     * @param roomName The room to leave
     */
    public void leaveRoom(String roomName) {
        this.joinedRooms.remove(roomName);
    }
    
    /**
     * Check if user is in a specific room
     * @param roomName The room to check
     * @return true if user is in the room, false otherwise
     */
    public boolean isInRoom(String roomName) {
        return this.joinedRooms.contains(roomName);
    }
    
    /**
     * Update the user's last activity timestamp
     */
    public void updateActivity() {
        this.lastActivity = System.currentTimeMillis();
    }
    
    @Override
    public String toString() {
        return "User{" +
                "username='" + username + '\'' +
                ", isOnline=" + isOnline +
                ", joinedRooms=" + joinedRooms +
                ", lastActivity=" + lastActivity +
                '}';
    }
    
    @Override
    public boolean equals(Object obj) {
        if (this == obj) return true;
        if (obj == null || getClass() != obj.getClass()) return false;
        User user = (User) obj;
        return username != null ? username.equals(user.username) : user.username == null;
    }
    
    @Override
    public int hashCode() {
        return username != null ? username.hashCode() : 0;
    }
}

