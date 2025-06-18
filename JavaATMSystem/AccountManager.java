import java.util.HashMap;
import java.util.Map;

public class AccountManager {
    private static AccountManager instance;
    private Map<String, Account> accounts;

    private AccountManager() {
        accounts = new HashMap<>();
    }

    public static AccountManager getInstance() {
        if (instance == null) {
            instance = new AccountManager();
        }
        return instance;
    }

    public void addAccount(Account account) {
        accounts.put(account.getAccountNumber(), account);
    }

    public Account getAccount(String accountNumber) {
        return accounts.get(accountNumber);
    }

    public boolean accountExists(String accountNumber) {
        return accounts.containsKey(accountNumber);
    }

    public String createNewAccount(String accountHolder, double initialBalance) {
        String accountNumber = generateAccountNumber();
        Account account = new Account(accountNumber, accountHolder, initialBalance);
        addAccount(account);
        return accountNumber;
    }

    private String generateAccountNumber() {
        return String.format("%010d", accounts.size() + 1);
    }
}
