import javax.swing.*;
import java.awt.*;
import java.rmi.RemoteException;
import java.rmi.registry.LocateRegistry;
import java.rmi.registry.Registry;

public class ATMGui extends JFrame {
    private BankInterface bankInterface;
    private JTextField accountField;
    private JTextField amountField;
    private JTextArea outputArea;
    
    public ATMGui() {
        bankInterface = null;
        setupGUI();
    }
    
    private void setupGUI() {
        setTitle("ATM System");
        setSize(400, 500);
        setDefaultCloseOperation(JFrame.EXIT_ON_CLOSE);
        setLocationRelativeTo(null);
        
        // Main panel
        JPanel mainPanel = new JPanel();
        mainPanel.setLayout(new BorderLayout(10, 10));
        mainPanel.setBorder(BorderFactory.createEmptyBorder(10, 10, 10, 10));
        
        // Server connection panel
        JPanel connectionPanel = new JPanel(new FlowLayout(FlowLayout.LEFT));
        JTextField serverField = new JTextField("localhost", 10);
        JTextField portField = new JTextField("1099", 5);
        JButton connectButton = new JButton("Connect to Server");
        connectionPanel.add(new JLabel("Server:"));
        connectionPanel.add(serverField);
        connectionPanel.add(new JLabel("Port:"));
        connectionPanel.add(portField);
        connectionPanel.add(connectButton);
        
        // Input panel
        JPanel inputPanel = new JPanel(new GridLayout(2, 2, 5, 5));
        
        accountField = new JTextField();
        amountField = new JTextField();
        
        inputPanel.add(new JLabel("Account Number:"));
        inputPanel.add(accountField);
        inputPanel.add(new JLabel("Amount:"));
        inputPanel.add(amountField);
        
        // Button panel
        JPanel buttonPanel = new JPanel(new GridLayout(2, 2, 5, 5));
        
        JButton checkBalanceBtn = new JButton("Check Balance");
        JButton withdrawBtn = new JButton("Withdraw");
        JButton depositBtn = new JButton("Deposit");
        JButton createAccountBtn = new JButton("Create Account");
        
        buttonPanel.add(checkBalanceBtn);
        buttonPanel.add(withdrawBtn);
        buttonPanel.add(depositBtn);
        buttonPanel.add(createAccountBtn);
        
        // Output area
        outputArea = new JTextArea();
        outputArea.setEditable(false);
        JScrollPane scrollPane = new JScrollPane(outputArea);
        
        // Add components to main panel
        mainPanel.add(connectionPanel, BorderLayout.NORTH);
        mainPanel.add(inputPanel, BorderLayout.CENTER);
        JPanel southPanel = new JPanel(new BorderLayout());
        southPanel.add(buttonPanel, BorderLayout.NORTH);
        southPanel.add(scrollPane, BorderLayout.CENTER);
        mainPanel.add(southPanel, BorderLayout.SOUTH);
        
        // Disable transaction buttons until connected
        checkBalanceBtn.setEnabled(false);
        withdrawBtn.setEnabled(false);
        depositBtn.setEnabled(false);
        createAccountBtn.setEnabled(false);

        // Add action listeners
        connectButton.addActionListener(e -> {
            try {
                String host = serverField.getText().trim();
                int port = Integer.parseInt(portField.getText().trim());
                Registry registry = LocateRegistry.getRegistry(host, port);
                bankInterface = (BankInterface) registry.lookup("BankService");
                outputArea.setText("Connected to server successfully!");
                
                // Enable buttons after successful connection
                checkBalanceBtn.setEnabled(true);
                withdrawBtn.setEnabled(true);
                depositBtn.setEnabled(true);
                createAccountBtn.setEnabled(true);
            } catch (Exception ex) {
                outputArea.setText("Error connecting to server: " + ex.getMessage());
            }
        });
        
        checkBalanceBtn.addActionListener(e -> checkBalance());
        withdrawBtn.addActionListener(e -> withdraw());
        depositBtn.addActionListener(e -> deposit());
        createAccountBtn.addActionListener(e -> createAccount());
        
        add(mainPanel);
    }
    
    private void checkBalance() {
        if (bankInterface == null) {
            outputArea.setText("Error: Please connect to server first");
            return;
        }
        
        String accountNumber = accountField.getText();
        
        try {
            double balance = bankInterface.getBalance(accountNumber);
            outputArea.setText("Current balance: $" + balance);
        } catch (RemoteException e) {
            outputArea.setText("Error: " + e.getMessage());
        }
    }
    
    private void withdraw() {
        if (bankInterface == null) {
            outputArea.setText("Error: Please connect to server first");
            return;
        }
        
        String accountNumber = accountField.getText();
        double amount;
        
        try {
            amount = Double.parseDouble(amountField.getText());
            boolean success = bankInterface.withdraw(accountNumber, amount);
            if (success) {
                outputArea.setText("Successfully withdrew $" + amount);
            } else {
                outputArea.setText("Withdrawal failed. Insufficient funds or invalid account.");
            }
        } catch (NumberFormatException e) {
            outputArea.setText("Error: Please enter a valid amount");
        } catch (RemoteException e) {
            outputArea.setText("Error: " + e.getMessage());
        }
    }
    
    private void deposit() {
        if (bankInterface == null) {
            outputArea.setText("Error: Please connect to server first");
            return;
        }
        
        String accountNumber = accountField.getText();
        double amount;
        
        try {
            amount = Double.parseDouble(amountField.getText());
            boolean success = bankInterface.deposit(accountNumber, amount);
            if (success) {
                outputArea.setText("Successfully deposited $" + amount);
            } else {
                outputArea.setText("Deposit failed. Invalid account.");
            }
        } catch (NumberFormatException e) {
            outputArea.setText("Error: Please enter a valid amount");
        } catch (RemoteException e) {
            outputArea.setText("Error: " + e.getMessage());
        }
    }
    
    private void createAccount() {
        if (bankInterface == null) {
            outputArea.setText("Error: Please connect to server first");
            return;
        }
        
        double initialBalance;
        try {
            initialBalance = Double.parseDouble(amountField.getText());
            String accountNumber = bankInterface.createAccount("New Account Holder", initialBalance);
            outputArea.setText("Account created successfully!\nYour account number is: " + accountNumber);
            accountField.setText(accountNumber);
        } catch (NumberFormatException e) {
            outputArea.setText("Error: Please enter a valid initial balance");
        } catch (RemoteException e) {
            outputArea.setText("Error: " + e.getMessage());
        }
    }
}
