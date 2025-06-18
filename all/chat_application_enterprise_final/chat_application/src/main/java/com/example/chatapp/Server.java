package com.example.chatapp;

import java.rmi.Naming;
import java.rmi.registry.LocateRegistry;
import java.rmi.registry.Registry;

/**
 * RMI Server for the Chat Application
 * Handles RMI registry setup and service binding
 */
public class Server {
    private static final int RMI_PORT = 1099;
    private static final String SERVICE_NAME = "ChatService";
    
    public static void main(String[] args) {
        try {
            System.out.println("Starting Chat Application Server...");
            
            // Create and export the chat service implementation
            ChatServiceImpl chatService = new ChatServiceImpl();
            System.out.println("Chat service implementation created.");
            
            // Try to locate existing RMI registry
            Registry registry = null;
            try {
                registry = LocateRegistry.getRegistry(RMI_PORT);
                registry.list(); // Test if registry is accessible
                System.out.println("Located existing RMI registry on port " + RMI_PORT);
            } catch (Exception e) {
                // Registry doesn't exist, create one
                try {
                    registry = LocateRegistry.createRegistry(RMI_PORT);
                    System.out.println("Created new RMI registry on port " + RMI_PORT);
                } catch (Exception createEx) {
                    System.err.println("Failed to create RMI registry: " + createEx.getMessage());
                    System.exit(1);
                }
            }
            
            // Bind the service to the registry
            String serviceUrl = "rmi://localhost:" + RMI_PORT + "/" + SERVICE_NAME;
            Naming.rebind(serviceUrl, chatService);
            System.out.println("Chat service bound to: " + serviceUrl);
            
            System.out.println("Chat Application Server is running...");
            System.out.println("Press Ctrl+C to stop the server.");
            
            // Keep the server running
            Runtime.getRuntime().addShutdownHook(new Thread(() -> {
                System.out.println("\nShutting down Chat Application Server...");
                try {
                    Naming.unbind(serviceUrl);
                    System.out.println("Service unbound successfully.");
                } catch (Exception e) {
                    System.err.println("Error during shutdown: " + e.getMessage());
                }
            }));
            
            // Keep main thread alive
            Object lock = new Object();
            synchronized (lock) {
                try {
                    lock.wait();
                } catch (InterruptedException e) {
                    System.out.println("Server interrupted.");
                }
            }
            
        } catch (Exception e) {
            System.err.println("Server error: " + e.getMessage());
            e.printStackTrace();
            System.exit(1);
        }
    }
}

