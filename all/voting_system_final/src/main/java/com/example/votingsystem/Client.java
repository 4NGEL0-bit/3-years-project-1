package com.example.votingsystem;

import javax.swing.*;
import java.awt.*;
import java.awt.event.*;
import java.io.*;
import java.net.*;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.Map;
import java.util.Comparator;

/**
 * Client for the Simple Voting System.
 * Provides a GUI for users to register, login, cast votes, and view results.
 */
public class Client extends JFrame {
    // Client configuration
    private static final String SERVER_HOST = "localhost";
    private static final int SERVER_PORT = 9876;
    
    // Connection components
    private Socket socket;
    private PrintWriter out;
    private BufferedReader in;
    private boolean connected = false;
    
    // Session state
    private String currentUsername = null;
    private boolean isLoggedIn = false;
    private String pollQuestion = "";
    private java.util.List<String> pollOptions = new ArrayList<>();
    private Map<String, Integer> currentResults = new HashMap<>();
    
    // GUI components
    private JPanel cards;
    private final CardLayout cardLayout = new CardLayout();
    
    // Welcome panel components
    private JPanel welcomePanel;
    private JButton loginButton;
    private JButton registerButton;
    
    // Login/Register panel components
    private JPanel authPanel;
    private JTextField usernameField;
    private JPasswordField passwordField;
    private JButton submitAuthButton;
    private JButton backToWelcomeButton;
    private JLabel authStatusLabel;
    private String authMode = "login"; // "login" or "register"
    
    // Voting panel components
    private JPanel votingPanel;
    private JLabel pollQuestionLabel;
    private JComboBox<String> optionsComboBox;
    private JButton voteButton;
    private JButton viewResultsButton;
    private JButton logoutButton;
    private JTextArea outputArea;
    
    /**
     * Constructor: Set up the client GUI.
     */
    public Client() {
        // Set up the main frame
        setTitle("Voting System Client");
        setSize(600, 400);
        setDefaultCloseOperation(JFrame.EXIT_ON_CLOSE);
        setLocationRelativeTo(null);
        
        // Initialize card layout for different screens
        cards = new JPanel(cardLayout);
        
        // Create panels
        createWelcomePanel();
        createAuthPanel();
        createVotingPanel();
        
        // Add panels to card layout
        cards.add(welcomePanel, "welcome");
        cards.add(authPanel, "auth");
        cards.add(votingPanel, "voting");
        
        // Show welcome panel initially
        cardLayout.show(cards, "welcome");
        
        // Add cards to frame
        add(cards);
        
        // Display the frame
        setVisible(true);
    }
    
    /**
     * Create the welcome panel with login and register options.
     */
    private void createWelcomePanel() {
        welcomePanel = new JPanel();
        welcomePanel.setLayout(new BorderLayout());
        
        JLabel titleLabel = new JLabel("Welcome to the Voting System", JLabel.CENTER);
        titleLabel.setFont(new Font("Arial", Font.BOLD, 24));
        welcomePanel.add(titleLabel, BorderLayout.NORTH);
        
        JPanel buttonPanel = new JPanel(new GridBagLayout());
        GridBagConstraints gbc = new GridBagConstraints();
        gbc.insets = new Insets(10, 10, 10, 10);
        
        // Create larger buttons for welcome screen
        loginButton = new JButton("Login");
        registerButton = new JButton("Register");
        
        // Set larger size for welcome screen buttons
        Dimension buttonSize = new Dimension(150, 50);
        loginButton.setPreferredSize(buttonSize);
        registerButton.setPreferredSize(buttonSize);
        
        // Set larger font for buttons
        Font buttonFont = new Font("Arial", Font.BOLD, 16);
        loginButton.setFont(buttonFont);
        registerButton.setFont(buttonFont);
        
        loginButton.addActionListener(e -> {
            authMode = "login";
            authStatusLabel.setText("Login to your account");
            submitAuthButton.setText("Login");
            cardLayout.show(cards, "auth");
        });
        
        registerButton.addActionListener(e -> {
            authMode = "register";
            authStatusLabel.setText("Create a new account");
            submitAuthButton.setText("Register");
            cardLayout.show(cards, "auth");
        });
        
        gbc.gridx = 0;
        gbc.gridy = 0;
        buttonPanel.add(loginButton, gbc);
        
        gbc.gridx = 0;
        gbc.gridy = 1;
        buttonPanel.add(registerButton, gbc);
        
        welcomePanel.add(buttonPanel, BorderLayout.CENTER);
    }
    
    /**
     * Create the authentication panel for login and registration.
     */
    private void createAuthPanel() {
        authPanel = new JPanel();
        authPanel.setLayout(new BorderLayout());
        
        authStatusLabel = new JLabel("Login to your account", JLabel.CENTER);
        authStatusLabel.setFont(new Font("Arial", Font.BOLD, 20));
        authPanel.add(authStatusLabel, BorderLayout.NORTH);
        
        // Use a more flexible layout for better alignment
        JPanel formPanel = new JPanel();
        formPanel.setLayout(new BoxLayout(formPanel, BoxLayout.Y_AXIS));
        formPanel.setBorder(BorderFactory.createEmptyBorder(30, 50, 30, 50));
        
        // Username row with proper alignment
        JPanel usernameRow = new JPanel(new BorderLayout(10, 0));
        JLabel usernameLabel = new JLabel("Username:");
        usernameLabel.setFont(new Font("Arial", Font.BOLD, 14));
        usernameRow.add(usernameLabel, BorderLayout.WEST);
        
        usernameField = new JTextField();
        usernameField.setBorder(BorderFactory.createLineBorder(Color.BLACK, 1));
        usernameField.setPreferredSize(new Dimension(250, 30));
        usernameRow.add(usernameField, BorderLayout.CENTER);
        
        // Password row with proper alignment
        JPanel passwordRow = new JPanel(new BorderLayout(10, 0));
        JLabel passwordLabel = new JLabel("Password:");
        passwordLabel.setFont(new Font("Arial", Font.BOLD, 14));
        passwordRow.add(passwordLabel, BorderLayout.WEST);
        
        passwordField = new JPasswordField();
        passwordField.setBorder(BorderFactory.createLineBorder(Color.BLACK, 1));
        passwordField.setPreferredSize(new Dimension(250, 30));
        passwordRow.add(passwordField, BorderLayout.CENTER);
        
        // Add rows to form with spacing
        formPanel.add(usernameRow);
        formPanel.add(Box.createRigidArea(new Dimension(0, 20))); // Space between fields
        formPanel.add(passwordRow);
        formPanel.add(Box.createRigidArea(new Dimension(0, 20))); // Space before buttons
        
        // Create buttons with better styling
        submitAuthButton = new JButton("Login");
        backToWelcomeButton = new JButton("Back");
        
        // Set consistent size and styling for auth screen buttons
        Dimension authButtonSize = new Dimension(100, 30);
        submitAuthButton.setPreferredSize(authButtonSize);
        backToWelcomeButton.setPreferredSize(authButtonSize);
        
        // Add borders to buttons for better visibility
        submitAuthButton.setBorder(BorderFactory.createRaisedBevelBorder());
        backToWelcomeButton.setBorder(BorderFactory.createRaisedBevelBorder());
        
        submitAuthButton.addActionListener(e -> handleAuth());
        backToWelcomeButton.addActionListener(e -> {
            usernameField.setText("");
            passwordField.setText("");
            cardLayout.show(cards, "welcome");
        });
        
        // Button panel with better alignment
        JPanel buttonPanel = new JPanel(new FlowLayout(FlowLayout.CENTER, 20, 0));
        buttonPanel.add(submitAuthButton);
        buttonPanel.add(backToWelcomeButton);
        
        // Center the button panel
        JPanel buttonContainer = new JPanel(new BorderLayout());
        buttonContainer.add(buttonPanel, BorderLayout.CENTER);
        formPanel.add(buttonContainer);
        
        authPanel.add(formPanel, BorderLayout.CENTER);
    }
    
    /**
     * Create the voting panel for casting votes and viewing results.
     */
    private void createVotingPanel() {
        votingPanel = new JPanel();
        votingPanel.setLayout(new BorderLayout());
        
        // Top panel with user info and logout
        JPanel topPanel = new JPanel(new BorderLayout());
        JLabel userLabel = new JLabel("Not logged in");
        userLabel.setFont(new Font("Arial", Font.PLAIN, 14));
        logoutButton = new JButton("Logout");
        logoutButton.setPreferredSize(new Dimension(100, 30));
        logoutButton.addActionListener(e -> handleLogout());
        topPanel.add(userLabel, BorderLayout.WEST);
        topPanel.add(logoutButton, BorderLayout.EAST);
        votingPanel.add(topPanel, BorderLayout.NORTH);
        
        // Center panel with poll question and options
        JPanel centerPanel = new JPanel(new BorderLayout());
        centerPanel.setBorder(BorderFactory.createEmptyBorder(20, 20, 20, 20));
        
        pollQuestionLabel = new JLabel("Question will appear here", JLabel.CENTER);
        pollQuestionLabel.setFont(new Font("Arial", Font.BOLD, 18));
        centerPanel.add(pollQuestionLabel, BorderLayout.NORTH);
        
        JPanel optionsPanel = new JPanel();
        optionsComboBox = new JComboBox<>();
        optionsComboBox.setPreferredSize(new Dimension(200, 30));
        JLabel selectLabel = new JLabel("Select option: ");
        selectLabel.setFont(new Font("Arial", Font.PLAIN, 14));
        optionsPanel.add(selectLabel);
        optionsPanel.add(optionsComboBox);
        centerPanel.add(optionsPanel, BorderLayout.CENTER);
        
        // Button panel
        JPanel buttonPanel = new JPanel();
        voteButton = new JButton("Cast Vote");
        viewResultsButton = new JButton("View Results");
        
        // Set consistent button size
        Dimension voteButtonSize = new Dimension(120, 35);
        voteButton.setPreferredSize(voteButtonSize);
        viewResultsButton.setPreferredSize(voteButtonSize);
        
        voteButton.addActionListener(e -> handleVote());
        viewResultsButton.addActionListener(e -> handleViewResults());
        
        buttonPanel.add(voteButton);
        buttonPanel.add(viewResultsButton);
        centerPanel.add(buttonPanel, BorderLayout.SOUTH);
        
        votingPanel.add(centerPanel, BorderLayout.CENTER);
        
        // Output area at bottom - now only for status messages, not results
        outputArea = new JTextArea(4, 40);
        outputArea.setEditable(false);
        JScrollPane scrollPane = new JScrollPane(outputArea);
        votingPanel.add(scrollPane, BorderLayout.SOUTH);
    }
    
    /**
     * Connect to the server.
     */
    private boolean connectToServer() {
        try {
            socket = new Socket(SERVER_HOST, SERVER_PORT);
            out = new PrintWriter(socket.getOutputStream(), true);
            in = new BufferedReader(new InputStreamReader(socket.getInputStream()));
            connected = true;
            outputArea.append("Connected to server.\n");
            return true;
        } catch (IOException e) {
            outputArea.append("Error connecting to server: " + e.getMessage() + "\n");
            connected = false;
            return false;
        }
    }
    
    /**
     * Send a request to the server and get the response.
     */
    private String sendRequest(String request) {
        if (!connected) {
            if (!connectToServer()) {
                return "ERROR|Not connected to server";
            }
        }
        
        try {
            out.println(request);
            return in.readLine();
        } catch (IOException e) {
            outputArea.append("Error communicating with server: " + e.getMessage() + "\n");
            connected = false;
            return "ERROR|Communication error: " + e.getMessage();
        }
    }
    
    /**
     * Handle authentication (login or register).
     */
    private void handleAuth() {
        String username = usernameField.getText().trim();
        String password = new String(passwordField.getPassword());
        
        if (username.isEmpty() || password.isEmpty()) {
            JOptionPane.showMessageDialog(this, 
                "Username and password cannot be empty", 
                "Input Error", 
                JOptionPane.ERROR_MESSAGE);
            return;
        }
        
        // Connect to server if not already connected
        if (!connected && !connectToServer()) {
            JOptionPane.showMessageDialog(this, 
                "Could not connect to server", 
                "Connection Error", 
                JOptionPane.ERROR_MESSAGE);
            return;
        }
        
        String command = authMode.equals("login") ? "LOGIN" : "REGISTER";
        String request = command + "|" + username + "|" + password;
        String response = sendRequest(request);
        
        String[] parts = response.split("\\|");
        if (parts[0].equals("SUCCESS")) {
            if (authMode.equals("login")) {
                currentUsername = username;
                isLoggedIn = true;
                
                // Update user label
                JLabel userLabel = (JLabel) ((JPanel) votingPanel.getComponent(0)).getComponent(0);
                userLabel.setText("Logged in as: " + currentUsername);
                
                // Fetch poll information
                fetchPollInfo();
                
                // Switch to voting panel
                cardLayout.show(cards, "voting");
            } else {
                // Registration successful, switch to login
                JOptionPane.showMessageDialog(this, 
                    "Registration successful. Please login.", 
                    "Success", 
                    JOptionPane.INFORMATION_MESSAGE);
                authMode = "login";
                authStatusLabel.setText("Login to your account");
                submitAuthButton.setText("Login");
            }
        } else {
            // Error handling
            String errorMsg = parts.length > 1 ? parts[1] : "Unknown error";
            JOptionPane.showMessageDialog(this, 
                errorMsg, 
                "Authentication Error", 
                JOptionPane.ERROR_MESSAGE);
        }
    }
    
    /**
     * Fetch poll information from the server.
     */
    private void fetchPollInfo() {
        String response = sendRequest("POLL");
        String[] parts = response.split("\\|");
        
        if (parts[0].equals("SUCCESS") && parts.length > 1) {
            pollQuestion = parts[1];
            pollQuestionLabel.setText(pollQuestion);
            
            // Clear and update options
            pollOptions.clear();
            optionsComboBox.removeAllItems();
            
            for (int i = 2; i < parts.length; i++) {
                pollOptions.add(parts[i]);
                optionsComboBox.addItem(parts[i]);
            }
            
            // Check if user has already voted
            checkVoteStatus();
        } else {
            outputArea.append("Error fetching poll information\n");
        }
    }
    
    /**
     * Check if the current user has already voted.
     */
    private void checkVoteStatus() {
        if (currentUsername != null) {
            String response = sendRequest("HASVOTED|" + currentUsername);
            String[] parts = response.split("\\|");
            
            if (parts[0].equals("SUCCESS") && parts.length > 1) {
                boolean hasVoted = Boolean.parseBoolean(parts[1]);
                voteButton.setEnabled(!hasVoted);
                
                if (hasVoted) {
                    outputArea.append("You have already cast your vote.\n");
                }
            }
        }
    }
    
    /**
     * Handle vote casting.
     */
    private void handleVote() {
        if (!isLoggedIn || currentUsername == null) {
            outputArea.append("You must be logged in to vote.\n");
            return;
        }
        
        String selectedOption = (String) optionsComboBox.getSelectedItem();
        if (selectedOption == null) {
            outputArea.append("Please select an option to vote.\n");
            return;
        }
        
        // Confirm vote
        int confirm = JOptionPane.showConfirmDialog(this,
            "Are you sure you want to vote for: " + selectedOption + "?\nThis action cannot be undone.",
            "Confirm Vote",
            JOptionPane.YES_NO_OPTION);
        
        if (confirm != JOptionPane.YES_OPTION) {
            return;
        }
        
        String request = "VOTE|" + currentUsername + "|" + selectedOption;
        String response = sendRequest(request);
        String[] parts = response.split("\\|");
        
        if (parts[0].equals("SUCCESS")) {
            outputArea.append("Vote cast successfully for: " + selectedOption + "\n");
            voteButton.setEnabled(false);
        } else {
            String errorMsg = parts.length > 1 ? parts[1] : "Unknown error";
            outputArea.append("Error casting vote: " + errorMsg + "\n");
        }
    }
    
    /**
     * Handle viewing results - now opens a separate window.
     */
    private void handleViewResults() {
        String response = sendRequest("RESULTS");
        String[] parts = response.split("\\|");
        
        if (parts[0].equals("SUCCESS") && parts.length > 1) {
            currentResults.clear();
            
            // Parse results
            for (int i = 1; i < parts.length; i++) {
                String[] optionCount = parts[i].split(":");
                if (optionCount.length == 2) {
                    try {
                        currentResults.put(optionCount[0], Integer.parseInt(optionCount[1]));
                    } catch (NumberFormatException e) {
                        outputArea.append("Error parsing result for " + optionCount[0] + "\n");
                    }
                }
            }
            
            // Display results in a separate window
            showResultsWindow();
        } else {
            outputArea.append("Error fetching results\n");
        }
    }
    
    /**
     * Create and show a separate window for results.
     */
    private void showResultsWindow() {
        // Create a new JFrame for results
        JFrame resultsFrame = new JFrame("Voting Results");
        resultsFrame.setSize(400, 300);
        resultsFrame.setLocationRelativeTo(this);
        
        // Create results panel
        JPanel resultsPanel = new JPanel(new BorderLayout());
        resultsPanel.setBorder(BorderFactory.createEmptyBorder(10, 10, 10, 10));
        
        // Add title
        JLabel titleLabel = new JLabel("Voting Results", JLabel.CENTER);
        titleLabel.setFont(new Font("Arial", Font.BOLD, 18));
        resultsPanel.add(titleLabel, BorderLayout.NORTH);
        
        // Create text area for results
        JTextArea resultsArea = new JTextArea();
        resultsArea.setEditable(false);
        resultsArea.setFont(new Font("Monospaced", Font.PLAIN, 14));
        JScrollPane scrollPane = new JScrollPane(resultsArea);
        resultsPanel.add(scrollPane, BorderLayout.CENTER);
        
        // Format and display results
        StringBuilder sb = new StringBuilder();
        
        int totalVotes = currentResults.values().stream().mapToInt(Integer::intValue).sum();
        sb.append("Total votes: ").append(totalVotes).append("\n\n");
        
        // Sort options by vote count (descending)
        java.util.List<Map.Entry<String, Integer>> sortedResults = new ArrayList<>(currentResults.entrySet());
        sortedResults.sort(Map.Entry.<String, Integer>comparingByValue().reversed());
        
        for (Map.Entry<String, Integer> entry : sortedResults) {
            String option = entry.getKey();
            int count = entry.getValue();
            double percentage = totalVotes > 0 ? (count * 100.0 / totalVotes) : 0;
            
            sb.append(option).append(": ")
              .append(count).append(" votes (")
              .append(String.format("%.1f", percentage)).append("%)\n");
        }
        
        resultsArea.setText(sb.toString());
        
        // Add close button
        JButton closeButton = new JButton("Close");
        closeButton.addActionListener(e -> resultsFrame.dispose());
        resultsPanel.add(closeButton, BorderLayout.SOUTH);
        
        // Add panel to frame and show
        resultsFrame.add(resultsPanel);
        resultsFrame.setVisible(true);
    }
    
    /**
     * Handle user logout.
     */
    private void handleLogout() {
        currentUsername = null;
        isLoggedIn = false;
        
        // Reset fields
        usernameField.setText("");
        passwordField.setText("");
        outputArea.setText("");
        
        // Switch back to welcome panel
        cardLayout.show(cards, "welcome");
    }
    
    /**
     * Close the connection to the server.
     */
    private void closeConnection() {
        try {
            if (out != null) out.close();
            if (in != null) in.close();
            if (socket != null && !socket.isClosed()) socket.close();
            connected = false;
        } catch (IOException e) {
            System.err.println("Error closing connection: " + e.getMessage());
        }
    }
    
    /**
     * Main method to start the client.
     */
    public static void main(String[] args) {
        // Use the Event Dispatch Thread for Swing components
        SwingUtilities.invokeLater(() -> {
            new Client();
        });
    }
}
