package com.example.chatapp;

import java.io.Serializable;
import java.util.ArrayList;
import java.util.HashSet;
import java.util.List;
import java.util.Set;

/**
 * ChatRoom data model representing a chat room
 */
public class ChatRoom implements Serializable {
    private static final long serialVersionUID = 1L;
    
    private String roomName;
    private Set<String> participants;
    private List<Message> messages;
    private long createdTimestamp;
    private int maxParticipants;
    private String description;
    private boolean isPublic;
    
    /**
     * Default constructor
     */
    public ChatRoom() {
        this.participants = new HashSet<>();
        this.messages = new ArrayList<>();
        this.createdTimestamp = System.currentTimeMillis();
        this.maxParticipants = 50; // Default max participants
        this.isPublic = true; // Default to public room
    }
    
    /**
     * Constructor with room name
     * @param roomName The name of the room
     */
    public ChatRoom(String roomName) {
        this();
        this.roomName = roomName;
    }
    
    /**
     * Constructor with room name and description
     * @param roomName The name of the room
     * @param description The room description
     */
    public ChatRoom(String roomName, String description) {
        this(roomName);
        this.description = description;
    }
    
    // Getters and setters
    public String getRoomName() {
        return roomName;
    }
    
    public void setRoomName(String roomName) {
        this.roomName = roomName;
    }
    
    public Set<String> getParticipants() {
        return participants;
    }
    
    public void setParticipants(Set<String> participants) {
        this.participants = participants;
    }
    
    public List<Message> getMessages() {
        return messages;
    }
    
    public void setMessages(List<Message> messages) {
        this.messages = messages;
    }
    
    public long getCreatedTimestamp() {
        return createdTimestamp;
    }
    
    public void setCreatedTimestamp(long createdTimestamp) {
        this.createdTimestamp = createdTimestamp;
    }
    
    public int getMaxParticipants() {
        return maxParticipants;
    }
    
    public void setMaxParticipants(int maxParticipants) {
        this.maxParticipants = maxParticipants;
    }
    
    public String getDescription() {
        return description;
    }
    
    public void setDescription(String description) {
        this.description = description;
    }
    
    public boolean isPublic() {
        return isPublic;
    }
    
    public void setPublic(boolean isPublic) {
        this.isPublic = isPublic;
    }
    
    /**
     * Add a participant to the room
     * @param username The username to add
     * @return true if added successfully, false if room is full or user already in room
     */
    public boolean addParticipant(String username) {
        if (participants.size() >= maxParticipants) {
            return false; // Room is full
        }
        return participants.add(username);
    }
    
    /**
     * Remove a participant from the room
     * @param username The username to remove
     * @return true if removed successfully, false if user was not in room
     */
    public boolean removeParticipant(String username) {
        return participants.remove(username);
    }
    
    /**
     * Check if a user is in the room
     * @param username The username to check
     * @return true if user is in the room, false otherwise
     */
    public boolean hasParticipant(String username) {
        return participants.contains(username);
    }
    
    /**
     * Add a message to the room
     * @param message The message to add
     */
    public void addMessage(Message message) {
        messages.add(message);
    }
    
    /**
     * Get the number of participants
     * @return Number of participants
     */
    public int getParticipantCount() {
        return participants.size();
    }
    
    /**
     * Get the number of messages
     * @return Number of messages
     */
    public int getMessageCount() {
        return messages.size();
    }
    
    /**
     * Check if the room is full
     * @return true if room is at maximum capacity, false otherwise
     */
    public boolean isFull() {
        return participants.size() >= maxParticipants;
    }
    
    /**
     * Get recent messages (last N messages)
     * @param limit Maximum number of messages to return
     * @return List of recent messages
     */
    public List<Message> getRecentMessages(int limit) {
        if (messages.size() <= limit) {
            return new ArrayList<>(messages);
        }
        return new ArrayList<>(messages.subList(messages.size() - limit, messages.size()));
    }
    
    /**
     * Get messages newer than a specific message ID
     * @param lastMessageId The ID of the last message the client has
     * @return List of newer messages
     */
    public List<Message> getMessagesAfter(long lastMessageId) {
        List<Message> newMessages = new ArrayList<>();
        for (Message message : messages) {
            if (message.getMessageId() > lastMessageId) {
                newMessages.add(message);
            }
        }
        return newMessages;
    }
    
    /**
     * Get formatted creation date
     * @return Formatted creation date
     */
    public String getFormattedCreationDate() {
        java.text.SimpleDateFormat sdf = new java.text.SimpleDateFormat("yyyy-MM-dd HH:mm:ss");
        return sdf.format(new java.util.Date(createdTimestamp));
    }
    
    @Override
    public String toString() {
        return "ChatRoom{" +
                "roomName='" + roomName + '\'' +
                ", participantCount=" + participants.size() +
                ", messageCount=" + messages.size() +
                ", maxParticipants=" + maxParticipants +
                ", isPublic=" + isPublic +
                '}';
    }
    
    @Override
    public boolean equals(Object obj) {
        if (this == obj) return true;
        if (obj == null || getClass() != obj.getClass()) return false;
        ChatRoom chatRoom = (ChatRoom) obj;
        return roomName != null ? roomName.equals(chatRoom.roomName) : chatRoom.roomName == null;
    }
    
    @Override
    public int hashCode() {
        return roomName != null ? roomName.hashCode() : 0;
    }
}

