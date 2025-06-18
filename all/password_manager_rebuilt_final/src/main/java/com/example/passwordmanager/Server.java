package com.example.passwordmanager;

import java.rmi.registry.LocateRegistry;
import java.rmi.registry.Registry;
import java.rmi.RemoteException;

/**
 * Main class to start the RMI Password Manager Server.
 * It creates an instance of the implementation, **starts the RMI registry programmatically**,
 * and binds the service to it.
 */
public class Server {

    public static void main(String[] args) {
        int port = 1099; // Default RMI registry port
        String serviceName = "PasswordManagerService";

        try {
            // Create an instance of the implementation class
            PasswordManagerImpl passwordManager = new PasswordManagerImpl();
            System.out.println("Password Manager implementation instance created.");

            // *** Start the RMI registry programmatically within this JVM ***
            // This ensures the registry has the necessary classpath.
            Registry registry;
            try {
                registry = LocateRegistry.createRegistry(port);
                System.out.println("RMI registry created successfully on port " + port);
            } catch (RemoteException e) {
                // If registry already exists on the port, get a reference to it.
                System.out.println("RMI registry might already be running. Attempting to locate...");
                registry = LocateRegistry.getRegistry(port);
                System.out.println("Located existing RMI registry on port " + port);
            }

            // Bind the remote object's stub in the registry
            // Use rebind to overwrite any existing binding with the same name
            registry.rebind(serviceName, passwordManager);

            System.out.println("Password Manager Server ready. Service name: " + serviceName);
            System.out.println("Server bound in registry. Waiting for client connections...");

            // Server will keep running because the RMI runtime keeps the JVM alive
            // as long as there are exported remote objects.

        } catch (RemoteException e) {
            System.err.println("Server RemoteException: " + e.toString());
            e.printStackTrace();
            System.exit(1); // Exit if server setup fails
        } catch (Exception e) {
            System.err.println("Server exception: " + e.toString());
            e.printStackTrace();
            System.exit(1); // Exit if server setup fails
        }
    }
}

