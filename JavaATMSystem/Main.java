public class Main {
    public static void main(String[] args) {
        // Create and show the GUI
        javax.swing.SwingUtilities.invokeLater(() -> {
            ATMGui gui = new ATMGui();
            gui.setVisible(true);
        });
    }
}
