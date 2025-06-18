import javax.swing.*;
import java.awt.*;
import java.awt.event.*;
import java.rmi.registry.LocateRegistry;
import java.rmi.registry.Registry;

public class BankClient {
    private BankInterface bankServer;
    private JFrame frame;
    private JPasswordField pinField;
    private JTextField accountField;
    private JTextField amountField;
    private JTextArea resultArea;
    private String currentAccountNumber;
    private CardLayout cardLayout;
    private JPanel cards;
    private double currentDepositAmount = 0.0;

    public BankClient() {
        createAndShowGUI();
    }

    private void createAndShowGUI() {
        frame = new JFrame("ATM Client");
        frame.setDefaultCloseOperation(JFrame.EXIT_ON_CLOSE);
        frame.setSize(500, 600);
        frame.setLocationRelativeTo(null);

        cardLayout = new CardLayout();
        cards = new JPanel(cardLayout);

        // Connection Panel
        JPanel connectionPanel = new JPanel(new BorderLayout(10, 10));
        connectionPanel.setBorder(BorderFactory.createEmptyBorder(10, 10, 10, 10));

        JPanel serverPanel = new JPanel(new FlowLayout(FlowLayout.CENTER));
        JTextField serverField = new JTextField("localhost", 10);
        JTextField portField = new JTextField("1099", 5);
        JButton connectButton = new JButton("Connect to Server");
        serverPanel.add(new JLabel("Server:"));
        serverPanel.add(serverField);
        serverPanel.add(new JLabel("Port:"));
        serverPanel.add(portField);
        serverPanel.add(connectButton);
        
        // Auth Panel
        JPanel authPanel = new JPanel(new GridBagLayout());
        GridBagConstraints gbc = new GridBagConstraints();
        gbc.insets = new Insets(5, 5, 5, 5);
        gbc.fill = GridBagConstraints.HORIZONTAL;

        accountField = new JTextField(15);
        pinField = new JPasswordField(4);
        JButton loginButton = new JButton("Login");

        gbc.gridx = 0; gbc.gridy = 0;
        authPanel.add(new JLabel("Account Number:"), gbc);
        gbc.gridx = 1;
        authPanel.add(accountField, gbc);
        gbc.gridx = 0; gbc.gridy = 1;
        authPanel.add(new JLabel("PIN:"), gbc);
        gbc.gridx = 1;
        authPanel.add(pinField, gbc);
        gbc.gridx = 0; gbc.gridy = 2;
        gbc.gridwidth = 2;
        authPanel.add(loginButton, gbc);

        connectionPanel.add(serverPanel, BorderLayout.NORTH);
        connectionPanel.add(authPanel, BorderLayout.CENTER);

        // Transaction Selection Panel
        JPanel transactionPanel = new JPanel(new GridBagLayout());
        JButton depositButton = new JButton("Deposit");
        JButton withdrawButton = new JButton("Withdraw");
        JButton checkBalanceButton = new JButton("Check Balance");
        JButton exitButton = new JButton("Exit");

        gbc = new GridBagConstraints();
        gbc.insets = new Insets(10, 10, 10, 10);
        gbc.fill = GridBagConstraints.HORIZONTAL;
        gbc.gridwidth = 2;

        gbc.gridx = 0; gbc.gridy = 0;
        transactionPanel.add(depositButton, gbc);
        gbc.gridy = 1;
        transactionPanel.add(withdrawButton, gbc);
        gbc.gridy = 2;
        transactionPanel.add(checkBalanceButton, gbc);
        gbc.gridy = 3;
        transactionPanel.add(exitButton, gbc);

        // Deposit Panel
        JPanel depositPanel = new JPanel(new GridBagLayout());
        amountField = new JTextField(15);
        JButton confirmDepositButton = new JButton("Confirm Deposit");
        JButton addMoreButton = new JButton("Add More");
        JButton cancelDepositButton = new JButton("Cancel");

        gbc = new GridBagConstraints();
        gbc.insets = new Insets(5, 5, 5, 5);
        gbc.fill = GridBagConstraints.HORIZONTAL;

        gbc.gridx = 0; gbc.gridy = 0;
        depositPanel.add(new JLabel("Enter Amount:"), gbc);
        gbc.gridx = 1;
        depositPanel.add(amountField, gbc);
        gbc.gridx = 0; gbc.gridy = 1;
        gbc.gridwidth = 2;
        depositPanel.add(confirmDepositButton, gbc);
        gbc.gridy = 2;
        depositPanel.add(addMoreButton, gbc);
        gbc.gridy = 3;
        depositPanel.add(cancelDepositButton, gbc);

        // Withdraw Panel
        JPanel withdrawPanel = new JPanel(new GridBagLayout());
        JTextField withdrawAmountField = new JTextField(15);
        JButton confirmWithdrawButton = new JButton("Confirm Withdraw");
        JButton cancelWithdrawButton = new JButton("Cancel");

        gbc = new GridBagConstraints();
        gbc.insets = new Insets(5, 5, 5, 5);
        gbc.fill = GridBagConstraints.HORIZONTAL;

        gbc.gridx = 0; gbc.gridy = 0;
        withdrawPanel.add(new JLabel("Enter Amount:"), gbc);
        gbc.gridx = 1;
        withdrawPanel.add(withdrawAmountField, gbc);
        gbc.gridx = 0; gbc.gridy = 1;
        gbc.gridwidth = 2;
        withdrawPanel.add(confirmWithdrawButton, gbc);
        gbc.gridy = 2;
        withdrawPanel.add(cancelWithdrawButton, gbc);

        // Result area
        resultArea = new JTextArea();
        resultArea.setEditable(false);
        JScrollPane scrollPane = new JScrollPane(resultArea);

        // Add panels to card layout
        cards.add(connectionPanel, "CONNECTION");
        cards.add(transactionPanel, "TRANSACTION");
        cards.add(depositPanel, "DEPOSIT");
        cards.add(withdrawPanel, "WITHDRAW");

        // Main layout
        JPanel mainPanel = new JPanel(new BorderLayout());
        mainPanel.add(cards, BorderLayout.CENTER);
        mainPanel.add(scrollPane, BorderLayout.SOUTH);
        frame.add(mainPanel);

        // Connect button action
        connectButton.addActionListener(e -> {
            try {
                String server = serverField.getText();
                int port = Integer.parseInt(portField.getText());
                Registry registry = LocateRegistry.getRegistry(server, port);
                bankServer = (BankInterface) registry.lookup("BankService");
                resultArea.append("Connected to server successfully\n");
                loginButton.setEnabled(true);
            } catch (Exception ex) {
                resultArea.append("Error connecting to server: " + ex.getMessage() + "\n");
                loginButton.setEnabled(false);
            }
        });

        // Login button action
        loginButton.addActionListener(e -> {
            String account = accountField.getText();
            String pin = new String(pinField.getPassword());
            try {
                if (bankServer.verifyPin(account, pin)) {
                    currentAccountNumber = account;
                    cardLayout.show(cards, "TRANSACTION");
                    resultArea.append("Login successful\n");
                } else {
                    resultArea.append("Invalid account or PIN\n");
                }
            } catch (Exception ex) {
                resultArea.append("Login error: " + ex.getMessage() + "\n");
            }
        });

        // Transaction button actions
        depositButton.addActionListener(e -> {
            cardLayout.show(cards, "DEPOSIT");
            currentDepositAmount = 0.0;
            amountField.setText("");
        });

        withdrawButton.addActionListener(e -> {
            cardLayout.show(cards, "WITHDRAW");
            withdrawAmountField.setText("");
        });

        checkBalanceButton.addActionListener(e -> {
            try {
                double balance = bankServer.getBalance(currentAccountNumber);
                resultArea.append(String.format("Current balance: $%.2f\n", balance));
            } catch (Exception ex) {
                resultArea.append("Error checking balance: " + ex.getMessage() + "\n");
            }
        });

        exitButton.addActionListener(e -> {
            cardLayout.show(cards, "CONNECTION");
            currentAccountNumber = null;
            accountField.setText("");
            pinField.setText("");
            resultArea.append("Logged out successfully\n");
        });

        // Deposit panel actions
        confirmDepositButton.addActionListener(e -> {
            try {
                double amount = Double.parseDouble(amountField.getText());
                currentDepositAmount += amount;
                if (bankServer.deposit(currentAccountNumber, amount)) {
                    resultArea.append(String.format("Deposited: $%.2f\n", amount));
                    cardLayout.show(cards, "TRANSACTION");
                } else {
                    resultArea.append("Deposit failed\n");
                }
            } catch (Exception ex) {
                resultArea.append("Error processing deposit: " + ex.getMessage() + "\n");
            }
        });

        addMoreButton.addActionListener(e -> {
            try {
                double amount = Double.parseDouble(amountField.getText());
                currentDepositAmount += amount;
                resultArea.append(String.format("Added: $%.2f (Total: $%.2f)\n", 
                    amount, currentDepositAmount));
                amountField.setText("");
            } catch (Exception ex) {
                resultArea.append("Invalid amount\n");
            }
        });

        cancelDepositButton.addActionListener(e -> {
            cardLayout.show(cards, "TRANSACTION");
            currentDepositAmount = 0.0;
        });

        // Withdraw panel actions
        confirmWithdrawButton.addActionListener(e -> {
            try {
                double amount = Double.parseDouble(withdrawAmountField.getText());
                if (bankServer.withdraw(currentAccountNumber, amount)) {
                    resultArea.append(String.format("Withdrawn: $%.2f\n", amount));
                    cardLayout.show(cards, "TRANSACTION");
                } else {
                    resultArea.append("Withdrawal failed - Insufficient funds\n");
                }
            } catch (Exception ex) {
                resultArea.append("Error processing withdrawal: " + ex.getMessage() + "\n");
            }
        });

        cancelWithdrawButton.addActionListener(e -> {
            cardLayout.show(cards, "TRANSACTION");
        });

        // Create account button action
        JButton createAccountButton = new JButton("Create Account");
        createAccountButton.addActionListener(e -> {
            String name = JOptionPane.showInputDialog(frame, "Enter account holder name:");
            if (name != null && !name.trim().isEmpty()) {
                try {
                    double initialBalance = Double.parseDouble(
                        JOptionPane.showInputDialog(frame, "Enter initial balance:")
                    );
                    currentAccountNumber = bankServer.createAccount(name, initialBalance);
                    accountField.setText(currentAccountNumber);
                    log("Account created successfully: " + currentAccountNumber);
                } catch (Exception ex) {
                    log("Error creating account: " + ex.getMessage());
                }
            }
        });

        // Transaction buttons actions
        withdrawButton.addActionListener(e -> performTransaction("withdraw"));
        depositButton.addActionListener(e -> performTransaction("deposit"));
        balanceButton.addActionListener(e -> checkBalance());

        // Initially disable transaction buttons until connected
        enableTransactionButtons(false);

        frame.add(mainPanel);
        frame.setVisible(true);
    }

    private void enableTransactionButtons(boolean enable) {
        Component[] components = frame.getContentPane().getComponents();
        for (Component component : components) {
            if (component instanceof JPanel) {
                for (Component c : ((JPanel) component).getComponents()) {
                    if (c instanceof JButton && !((JButton) c).getText().equals("Connect to Server")) {
                        c.setEnabled(enable);
                    }
                }
            }
        }
    }

    private void performTransaction(String type) {
        try {
            String accountNumber = accountField.getText();
            double amount = Double.parseDouble(amountField.getText());
            boolean success;

            if (type.equals("withdraw")) {
                success = bankServer.withdraw(accountNumber, amount);
                if (success) {
                    log("Withdrawal successful: $" + amount);
                } else {
                    log("Withdrawal failed: Insufficient funds or invalid account");
                }
            } else {
                success = bankServer.deposit(accountNumber, amount);
                if (success) {
                    log("Deposit successful: $" + amount);
                } else {
                    log("Deposit failed: Invalid account or amount");
                }
            }
            checkBalance();
        } catch (Exception ex) {
            log("Transaction error: " + ex.getMessage());
        }
    }

    private void checkBalance() {
        try {
            String accountNumber = accountField.getText();
            double balance = bankServer.getBalance(accountNumber);
            log("Current balance: $" + balance);
        } catch (Exception ex) {
            log("Error checking balance: " + ex.getMessage());
        }
    }

    private void log(String message) {
        SwingUtilities.invokeLater(() -> {
            resultArea.append(message + "\n");
            resultArea.setCaretPosition(resultArea.getDocument().getLength());
        });
    }

    public static void main(String[] args) {
        SwingUtilities.invokeLater(BankClient::new);
    }
}
