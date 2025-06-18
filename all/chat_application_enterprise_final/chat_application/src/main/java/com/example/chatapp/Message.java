package com.example.chatapp;

import java.io.Serializable;

/**
 * Message data model representing a chat message
 */
public class Message implements Serializable {
    private static final long serialVersionUID = 1L;
    
    private long messageId;
    private String username;
    private String content;
    private String roomName;
    private long timestamp;
    private boolean isFiltered;
    private String originalContent; // Store original content if filtered
    
    /**
     * Default constructor
     */
    public Message() {
        this.timestamp = System.currentTimeMillis();
        this.isFiltered = false;
    }
    
    /**
     * Constructor with basic message information
     * @param username The sender's username
     * @param content The message content
     * @param roomName The target room
     */
    public Message(String username, String content, String roomName) {
        this();
        this.username = username;
        this.content = content;
        this.roomName = roomName;
    }
    
    /**
     * Constructor with message ID
     * @param messageId The unique message ID
     * @param username The sender's username
     * @param content The message content
     * @param roomName The target room
     */
    public Message(long messageId, String username, String content, String roomName) {
        this(username, content, roomName);
        this.messageId = messageId;
    }
    
    // Getters and setters
    public long getMessageId() {
        return messageId;
    }
    
    public void setMessageId(long messageId) {
        this.messageId = messageId;
    }
    
    public String getUsername() {
        return username;
    }
    
    public void setUsername(String username) {
        this.username = username;
    }
    
    public String getContent() {
        return content;
    }
    
    public void setContent(String content) {
        this.content = content;
    }
    
    public String getRoomName() {
        return roomName;
    }
    
    public void setRoomName(String roomName) {
        this.roomName = roomName;
    }
    
    public long getTimestamp() {
        return timestamp;
    }
    
    public void setTimestamp(long timestamp) {
        this.timestamp = timestamp;
    }
    
    public boolean isFiltered() {
        return isFiltered;
    }
    
    public void setFiltered(boolean filtered) {
        isFiltered = filtered;
    }
    
    public String getOriginalContent() {
        return originalContent;
    }
    
    public void setOriginalContent(String originalContent) {
        this.originalContent = originalContent;
    }
    
    /**
     * Get formatted timestamp as string
     * @return Formatted timestamp
     */
    public String getFormattedTimestamp() {
        java.text.SimpleDateFormat sdf = new java.text.SimpleDateFormat("HH:mm:ss");
        return sdf.format(new java.util.Date(timestamp));
    }
    
    /**
     * Get formatted date and time as string
     * @return Formatted date and time
     */
    public String getFormattedDateTime() {
        java.text.SimpleDateFormat sdf = new java.text.SimpleDateFormat("yyyy-MM-dd HH:mm:ss");
        return sdf.format(new java.util.Date(timestamp));
    }
    
    /**
     * Check if this message is newer than another message
     * @param other The other message to compare
     * @return true if this message is newer
     */
    public boolean isNewerThan(Message other) {
        return this.timestamp > other.timestamp;
    }
    
    /**
     * Get a display-friendly version of the message
     * @return Formatted message string
     */
    public String getDisplayText() {
        return String.format("[%s] %s: %s", getFormattedTimestamp(), username, content);
    }
    
    @Override
    public String toString() {
        return "Message{" +
                "messageId=" + messageId +
                ", username='" + username + '\'' +
                ", content='" + content + '\'' +
                ", roomName='" + roomName + '\'' +
                ", timestamp=" + timestamp +
                ", isFiltered=" + isFiltered +
                '}';
    }
    
    @Override
    public boolean equals(Object obj) {
        if (this == obj) return true;
        if (obj == null || getClass() != obj.getClass()) return false;
        Message message = (Message) obj;
        return messageId == message.messageId;
    }
    
    @Override
    public int hashCode() {
        return Long.hashCode(messageId);
    }
}

