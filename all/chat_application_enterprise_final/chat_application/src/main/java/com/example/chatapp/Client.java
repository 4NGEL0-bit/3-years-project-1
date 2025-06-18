package com.example.chatapp;

import javax.swing.*;
import javax.swing.border.EmptyBorder;
import javax.swing.text.DefaultCaret;
import java.awt.*;
import java.awt.event.ActionEvent;
import java.awt.event.ActionListener;
import java.awt.event.WindowAdapter;
import java.awt.event.WindowEvent;
import java.rmi.Naming;
import java.rmi.RemoteException;
import java.util.List;
import java.util.Timer;
import java.util.TimerTask;

/**
 * Swing GUI Client for the Chat Application
 * Provides a user-friendly interface for real-time chat functionality
 */
public class Client extends JFrame {
    private static final long serialVersionUID = 1L;
    
    // RMI connection settings
    private static final String SERVER_URL = "rmi://localhost:1099/ChatService";
    private static final int MESSAGE_REFRESH_INTERVAL = 2000; // 2 seconds
    
    // GUI Components
    private JPanel loginPanel;
    private JPanel chatPanel;
    private JTextField usernameField;
    private JPasswordField passwordField;
    private JButton loginButton;
    private JButton registerButton;
    private JTextArea chatArea;
    private JTextField messageField;
    private JButton sendButton;
    private JList<String> roomList;
    private JList<String> userList;
    private JLabel statusLabel;
    private JLabel currentRoomLabel;
    private JButton joinRoomButton;
    private JButton leaveRoomButton;
    private JButton logoutButton;
    
    // Application state
    private ChatService chatService;
    private String sessionId;
    private String currentUsername;
    private String currentRoom;
    private long lastMessageId = 0;
    private Timer messageRefreshTimer;
    private DefaultListModel<String> roomListModel;
    private DefaultListModel<String> userListModel;
    
    /**
     * Constructor - initializes the GUI
     */
    public Client() {
        initializeGUI();
        connectToServer();
    }
    
    /**
     * Initialize the graphical user interface
     */
    private void initializeGUI() {
        setTitle("Enterprise Chat Application");
        setDefaultCloseOperation(JFrame.EXIT_ON_CLOSE);
        setSize(800, 600);
        setLocationRelativeTo(null);
        
        // Initialize list models
        roomListModel = new DefaultListModel<>();
        userListModel = new DefaultListModel<>();
        
        // Create main container
        Container container = getContentPane();
        container.setLayout(new CardLayout());
        
        // Create login and chat panels
        createLoginPanel();
        createChatPanel();
        
        // Add panels to container
        container.add(loginPanel, "LOGIN");
        container.add(chatPanel, "CHAT");
        
        // Show login panel initially
        showLoginPanel();
        
        // Add window listener for cleanup
        addWindowListener(new WindowAdapter() {
            @Override
            public void windowClosing(WindowEvent e) {
                cleanup();
                System.exit(0);
            }
        });
    }
    
    /**
     * Create the login panel
     */
    private void createLoginPanel() {
        loginPanel = new JPanel(new GridBagLayout());
        loginPanel.setBorder(new EmptyBorder(20, 20, 20, 20));
        
        GridBagConstraints gbc = new GridBagConstraints();
        gbc.insets = new Insets(5, 5, 5, 5);
        
        // Title
        JLabel titleLabel = new JLabel("Enterprise Chat Application");
        titleLabel.setFont(new Font("Arial", Font.BOLD, 18));
        gbc.gridx = 0; gbc.gridy = 0; gbc.gridwidth = 2;
        gbc.anchor = GridBagConstraints.CENTER;
        loginPanel.add(titleLabel, gbc);
        
        // Username field
        gbc.gridwidth = 1;
        gbc.anchor = GridBagConstraints.EAST;
        gbc.gridx = 0; gbc.gridy = 1;
        loginPanel.add(new JLabel("Username:"), gbc);
        
        usernameField = new JTextField(20);
        gbc.gridx = 1; gbc.gridy = 1;
        gbc.anchor = GridBagConstraints.WEST;
        loginPanel.add(usernameField, gbc);
        
        // Password field
        gbc.gridx = 0; gbc.gridy = 2;
        gbc.anchor = GridBagConstraints.EAST;
        loginPanel.add(new JLabel("Password:"), gbc);
        
        passwordField = new JPasswordField(20);
        gbc.gridx = 1; gbc.gridy = 2;
        gbc.anchor = GridBagConstraints.WEST;
        loginPanel.add(passwordField, gbc);
        
        // Buttons
        JPanel buttonPanel = new JPanel(new FlowLayout());
        loginButton = new JButton("Login");
        registerButton = new JButton("Register");
        
        loginButton.addActionListener(new LoginActionListener());
        registerButton.addActionListener(new RegisterActionListener());
        
        buttonPanel.add(loginButton);
        buttonPanel.add(registerButton);
        
        gbc.gridx = 0; gbc.gridy = 3; gbc.gridwidth = 2;
        gbc.anchor = GridBagConstraints.CENTER;
        loginPanel.add(buttonPanel, gbc);
        
        // Status label
        statusLabel = new JLabel(" ");
        statusLabel.setForeground(Color.RED);
        gbc.gridx = 0; gbc.gridy = 4; gbc.gridwidth = 2;
        loginPanel.add(statusLabel, gbc);
        
        // Add Enter key support
        passwordField.addActionListener(new LoginActionListener());
    }
    
    /**
     * Create the main chat panel
     */
    private void createChatPanel() {
        chatPanel = new JPanel(new BorderLayout());
        chatPanel.setBorder(new EmptyBorder(10, 10, 10, 10));
        
        // Top panel with current room and logout
        JPanel topPanel = new JPanel(new BorderLayout());
        currentRoomLabel = new JLabel("Not in any room");
        currentRoomLabel.setFont(new Font("Arial", Font.BOLD, 14));
        topPanel.add(currentRoomLabel, BorderLayout.WEST);
        
        logoutButton = new JButton("Logout");
        logoutButton.addActionListener(e -> logout());
        topPanel.add(logoutButton, BorderLayout.EAST);
        
        chatPanel.add(topPanel, BorderLayout.NORTH);
        
        // Center panel with chat area and message input
        JPanel centerPanel = new JPanel(new BorderLayout());
        
        // Chat area
        chatArea = new JTextArea();
        chatArea.setEditable(false);
        chatArea.setFont(new Font("Monospaced", Font.PLAIN, 12));
        chatArea.setBackground(Color.WHITE);
        
        // Auto-scroll to bottom
        DefaultCaret caret = (DefaultCaret) chatArea.getCaret();
        caret.setUpdatePolicy(DefaultCaret.ALWAYS_UPDATE);
        
        JScrollPane chatScrollPane = new JScrollPane(chatArea);
        chatScrollPane.setPreferredSize(new Dimension(400, 300));
        centerPanel.add(chatScrollPane, BorderLayout.CENTER);
        
        // Message input panel
        JPanel messagePanel = new JPanel(new BorderLayout());
        messageField = new JTextField();
        sendButton = new JButton("Send");
        
        messageField.addActionListener(e -> sendMessage());
        sendButton.addActionListener(e -> sendMessage());
        
        messagePanel.add(messageField, BorderLayout.CENTER);
        messagePanel.add(sendButton, BorderLayout.EAST);
        centerPanel.add(messagePanel, BorderLayout.SOUTH);
        
        chatPanel.add(centerPanel, BorderLayout.CENTER);
        
        // Right panel with rooms and users
        JPanel rightPanel = new JPanel(new GridLayout(2, 1));
        
        // Room panel
        JPanel roomPanel = new JPanel(new BorderLayout());
        roomPanel.setBorder(BorderFactory.createTitledBorder("Chat Rooms"));
        
        roomList = new JList<>(roomListModel);
        roomList.setSelectionMode(ListSelectionModel.SINGLE_SELECTION);
        JScrollPane roomScrollPane = new JScrollPane(roomList);
        roomScrollPane.setPreferredSize(new Dimension(150, 150));
        roomPanel.add(roomScrollPane, BorderLayout.CENTER);
        
        JPanel roomButtonPanel = new JPanel(new FlowLayout());
        joinRoomButton = new JButton("Join");
        leaveRoomButton = new JButton("Leave");
        
        joinRoomButton.addActionListener(e -> joinSelectedRoom());
        leaveRoomButton.addActionListener(e -> leaveCurrentRoom());
        
        roomButtonPanel.add(joinRoomButton);
        roomButtonPanel.add(leaveRoomButton);
        roomPanel.add(roomButtonPanel, BorderLayout.SOUTH);
        
        rightPanel.add(roomPanel);
        
        // User panel
        JPanel userPanel = new JPanel(new BorderLayout());
        userPanel.setBorder(BorderFactory.createTitledBorder("Users in Room"));
        
        userList = new JList<>(userListModel);
        JScrollPane userScrollPane = new JScrollPane(userList);
        userScrollPane.setPreferredSize(new Dimension(150, 150));
        userPanel.add(userScrollPane, BorderLayout.CENTER);
        
        rightPanel.add(userPanel);
        
        chatPanel.add(rightPanel, BorderLayout.EAST);
    }
    
    /**
     * Connect to the RMI server
     */
    private void connectToServer() {
        try {
            chatService = (ChatService) Naming.lookup(SERVER_URL);
            System.out.println("Connected to chat server successfully.");
        } catch (Exception e) {
            JOptionPane.showMessageDialog(this, 
                "Failed to connect to server: " + e.getMessage(),
                "Connection Error", 
                JOptionPane.ERROR_MESSAGE);
            System.exit(1);
        }
    }
    
    /**
     * Show the login panel
     */
    private void showLoginPanel() {
        CardLayout cardLayout = (CardLayout) getContentPane().getLayout();
        cardLayout.show(getContentPane(), "LOGIN");
        usernameField.requestFocus();
    }
    
    /**
     * Show the chat panel
     */
    private void showChatPanel() {
        CardLayout cardLayout = (CardLayout) getContentPane().getLayout();
        cardLayout.show(getContentPane(), "CHAT");
        messageField.requestFocus();
        
        // Start message refresh timer
        startMessageRefreshTimer();
        
        // Load available rooms
        loadAvailableRooms();
    }
    
    /**
     * Start the timer for refreshing messages
     */
    private void startMessageRefreshTimer() {
        if (messageRefreshTimer != null) {
            messageRefreshTimer.cancel();
        }
        
        messageRefreshTimer = new Timer(true);
        messageRefreshTimer.scheduleAtFixedRate(new TimerTask() {
            @Override
            public void run() {
                SwingUtilities.invokeLater(() -> {
                    refreshMessages();
                    refreshRoomUsers();
                });
            }
        }, 0, MESSAGE_REFRESH_INTERVAL);
    }
    
    /**
     * Stop the message refresh timer
     */
    private void stopMessageRefreshTimer() {
        if (messageRefreshTimer != null) {
            messageRefreshTimer.cancel();
            messageRefreshTimer = null;
        }
    }
    
    /**
     * Load available chat rooms
     */
    private void loadAvailableRooms() {
        try {
            List<String> rooms = chatService.getAvailableRooms();
            roomListModel.clear();
            for (String room : rooms) {
                roomListModel.addElement(room);
            }
        } catch (RemoteException e) {
            showError("Failed to load rooms: " + e.getMessage());
        }
    }
    
    /**
     * Join the selected room
     */
    private void joinSelectedRoom() {
        String selectedRoom = roomList.getSelectedValue();
        if (selectedRoom == null) {
            showError("Please select a room to join.");
            return;
        }
        
        try {
            boolean success = chatService.joinRoom(sessionId, selectedRoom);
            if (success) {
                currentRoom = selectedRoom;
                currentRoomLabel.setText("Current Room: " + currentRoom);
                chatArea.setText("");
                lastMessageId = 0;
                loadMessageHistory();
                showInfo("Joined room: " + selectedRoom);
            } else {
                showError("Failed to join room: " + selectedRoom);
            }
        } catch (RemoteException e) {
            showError("Error joining room: " + e.getMessage());
        }
    }
    
    /**
     * Leave the current room
     */
    private void leaveCurrentRoom() {
        if (currentRoom == null) {
            showError("You are not in any room.");
            return;
        }
        
        try {
            boolean success = chatService.leaveRoom(sessionId, currentRoom);
            if (success) {
                showInfo("Left room: " + currentRoom);
                currentRoom = null;
                currentRoomLabel.setText("Not in any room");
                chatArea.setText("");
                userListModel.clear();
                lastMessageId = 0;
            } else {
                showError("Failed to leave room.");
            }
        } catch (RemoteException e) {
            showError("Error leaving room: " + e.getMessage());
        }
    }
    
    /**
     * Send a message to the current room
     */
    private void sendMessage() {
        if (currentRoom == null) {
            showError("Please join a room first.");
            return;
        }
        
        String message = messageField.getText().trim();
        if (message.isEmpty()) {
            return;
        }
        
        try {
            boolean success = chatService.sendMessage(sessionId, currentRoom, message);
            if (success) {
                messageField.setText("");
                // Message will appear in the next refresh cycle
            } else {
                showError("Failed to send message. Check message length and content.");
            }
        } catch (RemoteException e) {
            showError("Error sending message: " + e.getMessage());
        }
    }
    
    /**
     * Load message history for the current room
     */
    private void loadMessageHistory() {
        if (currentRoom == null) {
            return;
        }
        
        try {
            List<Message> messages = chatService.getMessageHistory(sessionId, currentRoom, 50);
            chatArea.setText("");
            for (Message message : messages) {
                appendMessage(message);
                if (message.getMessageId() > lastMessageId) {
                    lastMessageId = message.getMessageId();
                }
            }
        } catch (RemoteException e) {
            showError("Error loading message history: " + e.getMessage());
        }
    }
    
    /**
     * Refresh messages in the current room
     */
    private void refreshMessages() {
        if (currentRoom == null || sessionId == null) {
            return;
        }
        
        try {
            List<Message> newMessages = chatService.getNewMessages(sessionId, currentRoom, lastMessageId);
            for (Message message : newMessages) {
                appendMessage(message);
                if (message.getMessageId() > lastMessageId) {
                    lastMessageId = message.getMessageId();
                }
            }
        } catch (RemoteException e) {
            // Silently handle refresh errors to avoid spam
            System.err.println("Error refreshing messages: " + e.getMessage());
        }
    }
    
    /**
     * Refresh the list of users in the current room
     */
    private void refreshRoomUsers() {
        if (currentRoom == null || sessionId == null) {
            return;
        }
        
        try {
            List<String> users = chatService.getRoomUsers(sessionId, currentRoom);
            userListModel.clear();
            for (String user : users) {
                userListModel.addElement(user);
            }
        } catch (RemoteException e) {
            // Silently handle refresh errors
            System.err.println("Error refreshing room users: " + e.getMessage());
        }
    }
    
    /**
     * Append a message to the chat area
     */
    private void appendMessage(Message message) {
        String displayText = message.getDisplayText();
        if (message.isFiltered()) {
            displayText += " [filtered]";
        }
        chatArea.append(displayText + "\n");
        chatArea.setCaretPosition(chatArea.getDocument().getLength());
    }
    
    /**
     * Logout from the application
     */
    private void logout() {
        try {
            if (sessionId != null) {
                chatService.logoutUser(sessionId);
            }
        } catch (RemoteException e) {
            System.err.println("Error during logout: " + e.getMessage());
        }
        
        cleanup();
        resetToLogin();
    }
    
    /**
     * Reset the application to login state
     */
    private void resetToLogin() {
        sessionId = null;
        currentUsername = null;
        currentRoom = null;
        lastMessageId = 0;
        
        usernameField.setText("");
        passwordField.setText("");
        statusLabel.setText(" ");
        chatArea.setText("");
        messageField.setText("");
        currentRoomLabel.setText("Not in any room");
        roomListModel.clear();
        userListModel.clear();
        
        showLoginPanel();
    }
    
    /**
     * Cleanup resources
     */
    private void cleanup() {
        stopMessageRefreshTimer();
    }
    
    /**
     * Show an error message
     */
    private void showError(String message) {
        statusLabel.setText(message);
        statusLabel.setForeground(Color.RED);
        JOptionPane.showMessageDialog(this, message, "Error", JOptionPane.ERROR_MESSAGE);
    }
    
    /**
     * Show an info message
     */
    private void showInfo(String message) {
        statusLabel.setText(message);
        statusLabel.setForeground(Color.BLUE);
    }
    
    /**
     * Login action listener
     */
    private class LoginActionListener implements ActionListener {
        @Override
        public void actionPerformed(ActionEvent e) {
            String username = usernameField.getText().trim();
            String password = new String(passwordField.getPassword());
            
            if (username.isEmpty() || password.isEmpty()) {
                showError("Please enter both username and password.");
                return;
            }
            
            try {
                boolean authenticated = chatService.authenticateUser(username, password);
                if (authenticated) {
                    sessionId = chatService.createSession(username);
                    if (sessionId != null) {
                        currentUsername = username;
                        showChatPanel();
                        showInfo("Welcome, " + username + "!");
                    } else {
                        showError("Failed to create session.");
                    }
                } else {
                    showError("Invalid username or password.");
                }
            } catch (RemoteException ex) {
                showError("Login failed: " + ex.getMessage());
            }
        }
    }
    
    /**
     * Register action listener
     */
    private class RegisterActionListener implements ActionListener {
        @Override
        public void actionPerformed(ActionEvent e) {
            String username = usernameField.getText().trim();
            String password = new String(passwordField.getPassword());
            
            if (username.isEmpty() || password.isEmpty()) {
                showError("Please enter both username and password.");
                return;
            }
            
            if (username.length() < 3 || username.length() > 20) {
                showError("Username must be 3-20 characters long.");
                return;
            }
            
            if (password.length() < 6) {
                showError("Password must be at least 6 characters long.");
                return;
            }
            
            try {
                boolean registered = chatService.registerUser(username, password);
                if (registered) {
                    showInfo("Registration successful! You can now login.");
                } else {
                    showError("Registration failed. Username may already exist.");
                }
            } catch (RemoteException ex) {
                showError("Registration failed: " + ex.getMessage());
            }
        }
    }
    
    /**
     * Main method to start the client application
     */
    public static void main(String[] args) {
        // Set look and feel
        try {
            UIManager.setLookAndFeel("javax.swing.plaf.metal.MetalLookAndFeel");
        } catch (Exception e) {
            System.err.println("Could not set look and feel: " + e.getMessage());
        }
        
        // Create and show the client
        SwingUtilities.invokeLater(() -> {
            new Client().setVisible(true);
        });
    }
}

