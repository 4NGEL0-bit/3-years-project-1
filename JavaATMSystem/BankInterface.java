import java.rmi.Remote;
import java.rmi.RemoteException;

public interface BankInterface extends Remote {
    boolean withdraw(String accountNumber, double amount) throws RemoteException;
    boolean deposit(String accountNumber, double amount) throws RemoteException;
    double getBalance(String accountNumber) throws RemoteException;
    String createAccount(String accountHolder, double initialBalance) throws RemoteException;
    boolean verifyPin(String accountNumber, String pin) throws RemoteException;
    void logTransaction(String accountNumber, String transactionType, double amount, boolean success) throws RemoteException;
}
