package com.example.passwordmanager;

import javax.swing.*;
import javax.swing.table.DefaultTableModel;
import java.awt.*;
import java.awt.event.ActionEvent;
import java.awt.event.ActionListener;
import java.rmi.registry.LocateRegistry;
import java.rmi.registry.Registry;
import java.security.MessageDigest;
import java.security.NoSuchAlgorithmException;
import java.util.Base64;
import java.util.List;
import java.util.Map;

/**
 * Rebuilt Swing GUI Client for the Password Manager RMI service.
 * Features: Welcome Screen, JTable display, explicit connection handling.
 */
public class Client extends JFrame {

    private PasswordManager passwordManager;
    private String loggedInUser = null;
    private String serverAddress = "localhost"; // Default server address
    private int serverPort = 1099; // Default RMI registry port

    // --- UI Components --- 
    // Shared
    private CardLayout cardLayout;
    private JPanel cardPanel; // Panel holding cards

    // Welcome Card Components
    private JPanel welcomePanel;
    private JTextField welcomeServerAddrField;
    private JButton welcomeLoginButton, welcomeRegisterButton;

    // Main App Card Components
    private JPanel mainAppPanel;
    // Auth Panel
    private JPanel authPanel;
    private JTextField usernameField;
    private JPasswordField passwordField;
    private JButton registerButton, loginButton, logoutButton;
    // Action Panel
    private JPanel actionPanel;
    private JTextField accountNameField, accountUserField;
    private JPasswordField accountPasswordField;
    private JButton addButton, viewButton, deleteButton, clearButton;
    // Display Panel (Table)
    private JPanel displayPanel;
    private JTable entriesTable;
    private DefaultTableModel tableModel;
    // Status Panel
    private JPanel statusPanel;
    private JTextArea statusArea;

    // Card names
    private final String WELCOME_CARD = "Welcome";
    private final String MAIN_APP_CARD = "MainApp";

    public Client() {
        super("Password Manager Client (Rebuilt)");
        // Initialize components first
        initComponents();
        // Then setup the layout including CardLayout
        setupLayout();
        // Then setup actions/listeners
        setupActions();
        // Final frame settings
        setSize(750, 650); // Adjusted size for better layout
        setDefaultCloseOperation(JFrame.EXIT_ON_CLOSE);
        setLocationRelativeTo(null); // Center window
        // Start with the welcome screen visible
        showWelcomeScreen();
    }

    /**
     * Initialize all UI components.
     */
    private void initComponents() {
        // CardLayout and main container panel
        cardLayout = new CardLayout();
        cardPanel = new JPanel(cardLayout);

        // --- Welcome Screen Components Initialization ---
        welcomePanel = new JPanel(new GridBagLayout());
        welcomeServerAddrField = new JTextField(serverAddress, 20);
        welcomeLoginButton = new JButton("Login / Connect");
        welcomeRegisterButton = new JButton("Register New User");

        // --- Main App Screen Components Initialization ---
        mainAppPanel = new JPanel(new BorderLayout(10, 10));
        authPanel = new JPanel(new GridBagLayout());
        actionPanel = new JPanel(new GridBagLayout());
        displayPanel = new JPanel(new BorderLayout());
        statusPanel = new JPanel(new BorderLayout());

        // Auth components
        usernameField = new JTextField(15);
        passwordField = new JPasswordField(15);
        registerButton = new JButton("Register");
        loginButton = new JButton("Login");
        logoutButton = new JButton("Logout");

        // Action components
        accountNameField = new JTextField(15);
        accountUserField = new JTextField(15);
        accountPasswordField = new JPasswordField(15);
        addButton = new JButton("Add/Update Entry");
        viewButton = new JButton("Refresh Entries");
        deleteButton = new JButton("Delete Entry");
        clearButton = new JButton("Clear Fields");

        // Display components (Table)
        String[] columnNames = {"Account Name", "Username", "Password"}; // WARNING: Displaying password
        tableModel = new DefaultTableModel(columnNames, 0) {
            @Override
            public boolean isCellEditable(int row, int column) { return false; }
        };
        entriesTable = new JTable(tableModel);
        entriesTable.setSelectionMode(ListSelectionModel.SINGLE_SELECTION);
        entriesTable.getColumnModel().getColumn(0).setPreferredWidth(150);
        entriesTable.getColumnModel().getColumn(1).setPreferredWidth(150);
        entriesTable.getColumnModel().getColumn(2).setPreferredWidth(150);

        // Status component
        statusArea = new JTextArea(4, 50);
        statusArea.setEditable(false);
        statusArea.setLineWrap(true);
        statusArea.setWrapStyleWord(true);
        statusArea.setForeground(Color.BLUE);
    }

    /**
     * Setup the layout of components within panels and the frame.
     */
    private void setupLayout() {
        // --- Welcome Panel Layout ---
        welcomePanel.setBorder(BorderFactory.createEmptyBorder(50, 50, 50, 50));
        GridBagConstraints gbcWelcome = new GridBagConstraints();
        gbcWelcome.insets = new Insets(10, 10, 10, 10);
        gbcWelcome.anchor = GridBagConstraints.CENTER;

        JLabel welcomeTitleLabel = new JLabel("Welcome to Password Manager");
        welcomeTitleLabel.setFont(new Font("Serif", Font.BOLD, 24));
        gbcWelcome.gridx = 0; gbcWelcome.gridy = 0; gbcWelcome.gridwidth = 2;
        welcomePanel.add(welcomeTitleLabel, gbcWelcome);

        gbcWelcome.gridy = 1; gbcWelcome.gridwidth = 1;
        gbcWelcome.anchor = GridBagConstraints.EAST;
        welcomePanel.add(new JLabel("Server Address:"), gbcWelcome);
        gbcWelcome.gridx = 1;
        gbcWelcome.anchor = GridBagConstraints.WEST;
        welcomePanel.add(welcomeServerAddrField, gbcWelcome);

        gbcWelcome.gridy = 2; gbcWelcome.gridx = 0;
        gbcWelcome.anchor = GridBagConstraints.CENTER;
        welcomePanel.add(welcomeLoginButton, gbcWelcome);
        gbcWelcome.gridx = 1;
        welcomePanel.add(welcomeRegisterButton, gbcWelcome);

        // --- Main App Panel Layout ---
        // Status Panel (Top)
        statusPanel.setBorder(BorderFactory.createTitledBorder("Status"));
        statusPanel.add(new JScrollPane(statusArea), BorderLayout.CENTER);

        // Left Panel (Auth + Actions)
        JPanel leftPanel = new JPanel();
        leftPanel.setLayout(new BoxLayout(leftPanel, BoxLayout.Y_AXIS));

        // Auth Panel Layout
        authPanel.setBorder(BorderFactory.createTitledBorder("Authentication"));
        GridBagConstraints gbcAuth = new GridBagConstraints();
        gbcAuth.insets = new Insets(5, 5, 5, 5);
        gbcAuth.anchor = GridBagConstraints.WEST;
        gbcAuth.gridx = 0; gbcAuth.gridy = 0; authPanel.add(new JLabel("Username:"), gbcAuth);
        gbcAuth.gridx = 1; gbcAuth.gridy = 0; authPanel.add(usernameField, gbcAuth);
        gbcAuth.gridx = 0; gbcAuth.gridy = 1; authPanel.add(new JLabel("Password:"), gbcAuth);
        gbcAuth.gridx = 1; gbcAuth.gridy = 1; authPanel.add(passwordField, gbcAuth);
        JPanel authBtnPanel = new JPanel(new FlowLayout(FlowLayout.CENTER));
        authBtnPanel.add(registerButton);
        authBtnPanel.add(loginButton);
        authBtnPanel.add(logoutButton);
        gbcAuth.gridx = 0; gbcAuth.gridy = 2; gbcAuth.gridwidth = 2; authPanel.add(authBtnPanel, gbcAuth);

        // Action Panel Layout
        actionPanel.setBorder(BorderFactory.createTitledBorder("Manage Entries"));
        GridBagConstraints gbcAction = new GridBagConstraints();
        gbcAction.insets = new Insets(5, 5, 5, 5);
        gbcAction.anchor = GridBagConstraints.WEST;
        gbcAction.gridx = 0; gbcAction.gridy = 0; actionPanel.add(new JLabel("Account Name:"), gbcAction);
        gbcAction.gridx = 1; gbcAction.gridy = 0; actionPanel.add(accountNameField, gbcAction);
        gbcAction.gridx = 0; gbcAction.gridy = 1; actionPanel.add(new JLabel("Account Username:"), gbcAction);
        gbcAction.gridx = 1; gbcAction.gridy = 1; actionPanel.add(accountUserField, gbcAction);
        gbcAction.gridx = 0; gbcAction.gridy = 2; actionPanel.add(new JLabel("Account Password:"), gbcAction);
        gbcAction.gridx = 1; gbcAction.gridy = 2; actionPanel.add(accountPasswordField, gbcAction);
        JPanel actionBtnPanel = new JPanel(new FlowLayout(FlowLayout.CENTER));
        actionBtnPanel.add(addButton);
        actionBtnPanel.add(viewButton);
        actionBtnPanel.add(deleteButton);
        actionBtnPanel.add(clearButton);
        gbcAction.gridx = 0; gbcAction.gridy = 3; gbcAction.gridwidth = 2; actionPanel.add(actionBtnPanel, gbcAction);

        leftPanel.add(authPanel);
        leftPanel.add(actionPanel);

        // Display Panel (Center/Right - Table)
        displayPanel.setBorder(BorderFactory.createTitledBorder("Stored Entries"));
        displayPanel.add(new JScrollPane(entriesTable), BorderLayout.CENTER);

        // Assemble Main App Panel
        mainAppPanel.add(statusPanel, BorderLayout.NORTH);
        mainAppPanel.add(leftPanel, BorderLayout.WEST);
        mainAppPanel.add(displayPanel, BorderLayout.CENTER);

        // --- Add Cards to Card Panel ---
        cardPanel.add(welcomePanel, WELCOME_CARD);
        cardPanel.add(mainAppPanel, MAIN_APP_CARD);

        // Add Card Panel to Frame
        add(cardPanel, BorderLayout.CENTER);

        // Set initial enabled states for main app panel (disabled until connected/logged in)
        setMainAppPanelEnabled(false); // Disable everything initially
    }

    /**
     * Setup action listeners for all buttons.
     */
    private void setupActions() {
        // Welcome Screen Buttons
        welcomeLoginButton.addActionListener(e -> attemptShowMainApp(false)); // Attempt login flow
        welcomeRegisterButton.addActionListener(e -> attemptShowMainApp(true)); // Attempt register flow

        // Main App Screen Buttons
        registerButton.addActionListener(e -> registerUser());
        loginButton.addActionListener(e -> loginUser());
        logoutButton.addActionListener(e -> logoutUser());
        addButton.addActionListener(e -> addOrUpdateEntry());
        viewButton.addActionListener(e -> viewEntries()); // Refresh table
        deleteButton.addActionListener(e -> deleteEntry());
        clearButton.addActionListener(e -> clearActionFields());

        // Table selection listener
        entriesTable.getSelectionModel().addListSelectionListener(event -> {
            if (!event.getValueIsAdjusting() && entriesTable.getSelectedRow() != -1) {
                int selectedRow = entriesTable.getSelectedRow();
                accountNameField.setText(tableModel.getValueAt(selectedRow, 0).toString());
                accountUserField.setText(tableModel.getValueAt(selectedRow, 1).toString());
                accountPasswordField.setText(tableModel.getValueAt(selectedRow, 2).toString());
            }
        });
    }

    // --- Control Methods ---

    private void showWelcomeScreen() {
        cardLayout.show(cardPanel, WELCOME_CARD);
        setTitle("Password Manager - Welcome");
        passwordManager = null; // Ensure disconnected state
        loggedInUser = null;
        setMainAppPanelEnabled(false); // Ensure main panel is disabled
    }

    private void attemptShowMainApp(boolean isRegistering) {
        // Attempt to connect to server first
        if (connectToServer()) {
            // If connection successful, show main app panel
            cardLayout.show(cardPanel, MAIN_APP_CARD);
            setTitle("Password Manager - " + (isRegistering ? "Register" : "Login"));
            setMainAppPanelEnabled(true); // Enable main panel components
            setLoggedInState(false); // Set initial state (not logged in)
            // Set focus
            usernameField.requestFocusInWindow();
        } else {
            // Connection failed, show error message on welcome screen
            JOptionPane.showMessageDialog(this, 
                "Failed to connect to the server at " + welcomeServerAddrField.getText() + ".\nPlease check the address and ensure the server is running.", 
                "Connection Error", 
                JOptionPane.ERROR_MESSAGE);
        }
    }

    /** Helper to enable/disable all relevant components on the main app panel */
    private void setMainAppPanelEnabled(boolean enabled) {
         // Enable/disable components in Auth panel (except logout)
        usernameField.setEnabled(enabled);
        passwordField.setEnabled(enabled);
        registerButton.setEnabled(enabled);
        loginButton.setEnabled(enabled);
        // Logout button is handled by setLoggedInState

        // Enable/disable components in Action panel
        accountNameField.setEnabled(enabled);
        accountUserField.setEnabled(enabled);
        accountPasswordField.setEnabled(enabled);
        addButton.setEnabled(enabled);
        viewButton.setEnabled(enabled);
        deleteButton.setEnabled(enabled);
        clearButton.setEnabled(enabled);

        // Enable/disable table
        entriesTable.setEnabled(enabled);

        // If disabling, also clear fields and logout state
        if (!enabled) {
            setLoggedInState(false); // Ensure logged out state visually
            clearActionFields();
            clearTable();
            usernameField.setText("");
            passwordField.setText("");
        }
    }

    /** Helper to set UI state based on login status */
    private void setLoggedInState(boolean loggedIn) {
        // Assumes main app panel is already enabled
        loginButton.setEnabled(!loggedIn);
        registerButton.setEnabled(!loggedIn);
        usernameField.setEditable(!loggedIn);
        passwordField.setEditable(!loggedIn);

        logoutButton.setEnabled(loggedIn);
        // Enable action panel components only when logged in
        accountNameField.setEnabled(loggedIn);
        accountUserField.setEnabled(loggedIn);
        accountPasswordField.setEnabled(loggedIn);
        addButton.setEnabled(loggedIn);
        viewButton.setEnabled(loggedIn);
        deleteButton.setEnabled(loggedIn);
        clearButton.setEnabled(loggedIn);
        entriesTable.setEnabled(loggedIn);

        if (!loggedIn) {
            loggedInUser = null;
            clearActionFields();
            clearTable();
            setTitle("Password Manager - Login / Register");
        } else {
             setTitle("Password Manager - User: " + loggedInUser);
        }
    }

    // --- Action Methods ---

    private boolean connectToServer() {
        serverAddress = welcomeServerAddrField.getText().trim(); // Get address from welcome screen field
        if (serverAddress.isEmpty()) {
            updateStatus("ERROR: Server address cannot be empty.", true);
            return false;
        }
        try {
            // Ensure RMI registry is running (handled by run_server.sh)
            Registry registry = LocateRegistry.getRegistry(serverAddress, serverPort);
            passwordManager = (PasswordManager) registry.lookup("PasswordManagerService");
            updateStatus("Successfully connected to server at " + serverAddress + ":" + serverPort, false);
            welcomeServerAddrField.setEditable(false); // Lock address field after connect
            return true;
        } catch (Exception ex) {
            updateStatus("ERROR: Could not connect to server - " + ex.getMessage(), true);
            // Show detailed error in status area
            statusArea.append(ex.toString() + "\n"); 
            passwordManager = null;
            return false;
        }
    }

    private void registerUser() {
        if (passwordManager == null) {
            updateStatus("ERROR: Not connected to server.", true);
            return;
        }
        String username = usernameField.getText().trim();
        String password = new String(passwordField.getPassword());

        if (username.isEmpty() || password.isEmpty()) {
            updateStatus("ERROR: Username and password cannot be empty for registration.", true);
            return;
        }

        try {
            boolean success = passwordManager.registerUser(username, password);
            if (success) {
                updateStatus("User registered successfully: " + username + ". You can now login.", false);
                passwordField.setText(""); // Clear password field after registration
            } else {
                updateStatus("Registration failed: Username might already exist.", true);
            }
        } catch (Exception ex) {
            updateStatus("ERROR during registration: " + ex.getMessage(), true);
        }
    }

    private void loginUser() {
        if (passwordManager == null) {
            updateStatus("ERROR: Not connected to server.", true);
            return;
        }
        String username = usernameField.getText().trim();
        String password = new String(passwordField.getPassword());

        if (username.isEmpty() || password.isEmpty()) {
            updateStatus("ERROR: Username and password cannot be empty for login.", true);
            return;
        }

        try {
            boolean success = passwordManager.authenticateUser(username, password);
            if (success) {
                loggedInUser = username;
                updateStatus("Login successful for user: " + loggedInUser, false);
                setLoggedInState(true);
                viewEntries(); // Load entries into table upon login
            } else {
                updateStatus("Login failed: Invalid username or password.", true);
                loggedInUser = null;
                setLoggedInState(false);
            }
        } catch (Exception ex) {
            updateStatus("ERROR during login: " + ex.getMessage(), true);
            loggedInUser = null;
            setLoggedInState(false);
        }
    }
    
    private void logoutUser() {
        // Simply update the UI state
        updateStatus("Logged out successfully.", false);
        setLoggedInState(false);
        usernameField.setText("");
        passwordField.setText("");
        // Optionally switch back to welcome screen?
        // showWelcomeScreen(); 
    }

    private void addOrUpdateEntry() {
        if (loggedInUser == null) {
            updateStatus("ERROR: Please login first.", true);
            return;
        }
        String accountName = accountNameField.getText().trim();
        String accountUsername = accountUserField.getText().trim();
        String accountPassword = new String(accountPasswordField.getPassword());

        if (accountName.isEmpty() || accountUsername.isEmpty() || accountPassword.isEmpty()) {
            updateStatus("ERROR: Account Name, Username, and Password cannot be empty.", true);
            return;
        }

        try {
            boolean success = passwordManager.addOrUpdateEntry(loggedInUser, accountName, accountUsername, accountPassword);
            if (success) {
                updateStatus("Entry added/updated successfully for account: " + accountName, false);
                clearActionFields();
                viewEntries(); // Refresh table
            } else {
                // This path might not be reachable if server always returns true or throws exception
                updateStatus("Failed to add/update entry for account: " + accountName, true);
            }
        } catch (Exception ex) {
            updateStatus("ERROR adding/updating entry: " + ex.getMessage(), true);
        }
    }

    private void viewEntries() {
        if (loggedInUser == null) {
            updateStatus("ERROR: Please login first.", true);
            return;
        }
        try {
            updateStatus("Refreshing entries for user: " + loggedInUser + "...", false);
            List<Map<String, String>> entries = passwordManager.getEntries(loggedInUser);
            
            // Clear existing table data
            tableModel.setRowCount(0);

            if (entries == null || entries.isEmpty()) {
                updateStatus("No entries found for user: " + loggedInUser, false);
            } else {
                for (Map<String, String> entry : entries) {
                    tableModel.addRow(new Object[]{
                        entry.get("accountName"), 
                        entry.get("accountUsername"), 
                        entry.get("accountPassword") // WARNING: Displaying password!
                    });
                }
                updateStatus("Displayed " + entries.size() + " entries.", false);
            }
        } catch (Exception ex) {
            updateStatus("ERROR viewing entries: " + ex.getMessage(), true);
            clearTable();
        }
    }

    private void deleteEntry() {
        if (loggedInUser == null) {
            updateStatus("ERROR: Please login first.", true);
            return;
        }
        
        String accountName = accountNameField.getText().trim();
        int selectedRow = entriesTable.getSelectedRow();
        
        if (selectedRow != -1) {
             accountName = tableModel.getValueAt(selectedRow, 0).toString();
        } else if (accountName.isEmpty()) {
             updateStatus("ERROR: Please select an entry from the table or enter an Account Name to delete.", true);
             return;
        }

        int confirmation = JOptionPane.showConfirmDialog(this, 
                "Are you sure you want to delete the entry for \'" + accountName + "\'?", 
                "Confirm Deletion", 
                JOptionPane.YES_NO_OPTION,
                JOptionPane.WARNING_MESSAGE); // Add warning icon

        if (confirmation == JOptionPane.YES_OPTION) {
            try {
                boolean success = passwordManager.deleteEntry(loggedInUser, accountName);
                if (success) {
                    updateStatus("Entry deleted successfully for account: " + accountName, false);
                    clearActionFields();
                    viewEntries(); // Refresh table
                } else {
                    updateStatus("Failed to delete entry: Account \'" + accountName + "\' not found.", true);
                }
            } catch (Exception ex) {
                updateStatus("ERROR deleting entry: " + ex.getMessage(), true);
            }
        } else {
             updateStatus("Deletion cancelled for account: " + accountName, false);
        }
    }

    private void clearActionFields() {
        accountNameField.setText("");
        accountUserField.setText("");
        accountPasswordField.setText("");
        entriesTable.clearSelection();
    }

    private void clearTable() {
        tableModel.setRowCount(0);
    }

    // Helper method to update status area
    private void updateStatus(String message, boolean isError) {
        SwingUtilities.invokeLater(() -> { // Ensure UI updates happen on the EDT
            statusArea.setForeground(isError ? Color.RED : Color.BLUE);
            statusArea.append(message + "\n");
            statusArea.setCaretPosition(statusArea.getDocument().getLength()); 
        });
    }

    // --- Main Method ---
    public static void main(String[] args) {
        // Set Look and Feel for better appearance (optional)
        try {
            UIManager.setLookAndFeel(UIManager.getSystemLookAndFeelClassName());
        } catch (Exception e) {
            System.err.println("Couldn't set system look and feel: " + e.getMessage());
        }

        // Run the client GUI on the Event Dispatch Thread
        SwingUtilities.invokeLater(() -> {
            Client client = new Client();
            client.setVisible(true);
        });
    }
}

