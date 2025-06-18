import java.rmi.registry.Registry;
import java.rmi.registry.LocateRegistry;
import java.rmi.RemoteException;
import java.rmi.server.UnicastRemoteObject;
import javax.swing.*;
import java.awt.*;
import java.util.ArrayList;
import java.util.List;

public class BankServer extends UnicastRemoteObject implements BankInterface {
    private final AccountManager accountManager;
    private JTextArea logArea;
    private int port;
    private final List<String> transactionLog;
    private Registry registry;

    public BankServer() throws RemoteException {
        accountManager = AccountManager.getInstance();
        transactionLog = new ArrayList<>();
        createAndShowGUI();
    }

    private void createAndShowGUI() {
        JFrame frame = new JFrame("Bank Server");
        frame.setDefaultCloseOperation(JFrame.EXIT_ON_CLOSE);
        frame.setSize(600, 400);

        JPanel mainPanel = new JPanel(new BorderLayout());
        
        // Port selection panel
        JPanel portPanel = new JPanel();
        JTextField portField = new JTextField("1099", 5);
        JButton startButton = new JButton("Start Server");
        portPanel.add(new JLabel("Port:"));
        portPanel.add(portField);
        portPanel.add(startButton);

        // Log panel
        logArea = new JTextArea();
        logArea.setEditable(false);
        JScrollPane scrollPane = new JScrollPane(logArea);

        mainPanel.add(portPanel, BorderLayout.NORTH);
        mainPanel.add(scrollPane, BorderLayout.CENTER);

        startButton.addActionListener(e -> {
            try {
                port = Integer.parseInt(portField.getText());
                startServer(port);
                startButton.setEnabled(false);
                portField.setEnabled(false);
                log("Server started on port " + port);
            } catch (Exception ex) {
                log("Error starting server: " + ex.getMessage());
            }
        });

        frame.add(mainPanel);
        frame.setVisible(true);
    }

    private void startServer(int port) throws RemoteException {
        try {
            // Try to create a new registry
            registry = LocateRegistry.createRegistry(port);
            log("Created new RMI registry on port " + port);
        } catch (RemoteException e) {
            // If registry already exists, get it
            registry = LocateRegistry.getRegistry(port);
            log("Connected to existing RMI registry on port " + port);
        }
        registry.rebind("BankService", this);
        log("Bank service is bound and ready");
    }

    public boolean verifyPin(String accountNumber, String pin) throws RemoteException {
        Account account = accountManager.getAccount(accountNumber);
        if (account != null) {
            boolean success = account.verifyPin(pin);
            logTransaction(accountNumber, "PIN_VERIFY", 0.0, success);
            return success;
        }
        return false;
    }

    public void logTransaction(String accountNumber, String transactionType, double amount, boolean success) throws RemoteException {
        String timestamp = java.time.LocalDateTime.now().toString();
        String logMessage = String.format("%s - Account: %s, Type: %s, Amount: $%.2f, Status: %s",
            timestamp, accountNumber, transactionType, amount, success ? "SUCCESS" : "FAILED");
        transactionLog.add(logMessage);
        log(logMessage);
    }

    private void log(String message) {
        if (logArea != null) {
            SwingUtilities.invokeLater(() -> {
                logArea.append(message + "\n");
                logArea.setCaretPosition(logArea.getDocument().getLength());
            });
        }
        transactionLog.add(message);
    }

    @Override
    public boolean withdraw(String accountNumber, double amount) throws RemoteException {
        Account account = accountManager.getAccount(accountNumber);
        boolean success = false;
        if (account != null) {
            success = account.withdraw(amount);
        }
        logTransaction(accountNumber, "WITHDRAW", amount, success);
        return success;
    }

    @Override
    public boolean deposit(String accountNumber, double amount) throws RemoteException {
        Account account = accountManager.getAccount(accountNumber);
        boolean success = false;
        if (account != null) {
            account.deposit(amount);
            success = true;
        }
        logTransaction(accountNumber, "DEPOSIT", amount, success);
        return success;
    }

    @Override
    public double getBalance(String accountNumber) throws RemoteException {
        Account account = accountManager.getAccount(accountNumber);
        if (account != null) {
            log("Balance check: Account=" + accountNumber + ", Balance=" + account.getBalance());
            return account.getBalance();
        }
        log("Balance check failed: Account " + accountNumber + " not found");
        throw new RemoteException("Account not found");
    }

    @Override
    public String createAccount(String accountHolder, double initialBalance) throws RemoteException {
        String accountNumber = accountManager.createNewAccount(accountHolder, initialBalance);
        log("New account created: Holder=" + accountHolder + ", Account=" + accountNumber);
        return accountNumber;
    }

    public static void main(String[] args) {
        try {
            new BankServer();
        } catch (RemoteException e) {
            e.printStackTrace();
        }
    }
}
