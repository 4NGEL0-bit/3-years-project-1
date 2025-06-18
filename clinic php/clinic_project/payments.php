<?php
require_once 'includes/db.php'; // For database operations
require_once 'includes/header.php'; // Includes session_start, functions.php, and HTML head

if (!is_logged_in()) {
    redirect('index.php');
}

$user_id = $_SESSION['user_id'];
$user_role = get_user_role();
$page_title = "Payments & Invoices";

$payments = [];

// Base SQL query
$sql = "SELECT p.id, p.amount, p.payment_date, p.status as payment_status, p.payment_method, 
               a.appointment_date, pat.nom as patient_name, doc.nom as doctor_name
        FROM payments p
        JOIN appointments a ON p.appointment_id = a.id
        JOIN users pat ON p.patient_id = pat.id
        JOIN users doc ON a.doctor_id = doc.id";

$params = [];
$param_types = "";

if ($user_role === 'patient') {
    $sql .= " WHERE p.patient_id = ?";
    $params[] = $user_id;
    $param_types .= "i";
} elseif ($user_role === 'admin') {
    // Admin can see all, potentially add filters later
} else {
    // Other roles might not see this page or have limited view
    // For now, let's assume they don't have direct access or it's handled by dashboard links
    // To prevent direct access for roles not intended:
    // redirect('dashboard.php'); 
}

$sql .= " ORDER BY p.payment_date DESC";

$stmt = $conn->prepare($sql);
if ($stmt) {
    if (!empty($params)) {
        $stmt->bind_param($param_types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $payments[] = $row;
    }
    $stmt->close();
} else {
    echo "<p class='error-message-login'>Error preparing SQL statement: " . $conn->error . "</p>";
}

?>
<script>
    document.title = "<?php echo $page_title; ?> - ClinicSys";
</script>

<div class="dashboard-header">
    <h1><?php echo $page_title; ?></h1>
    <p>Review your payment history and manage invoices.</p>
</div>

<?php if ($user_role === 'admin'): ?>
    <div style="margin-bottom: 20px; text-align: right;">
        <a href="add_payment.php" class="btn-login" style="padding: 10px 20px;">Add New Payment Record</a> <!-- Placeholder for admin to add payment -->
    </div>
<?php endif; ?>

<?php if (empty($payments)): ?>
    <div class="info-message" style="background-color: var(--secondary-color); color: var(--text-color); padding: 20px; border-radius: 8px; text-align: center; margin-top: 20px;">
        <p>No payment records found.</p>
    </div>
<?php else: ?>
    <table class="content-table animated-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Method</th>
                <?php if ($user_role === 'admin') echo "<th>Patient</th>"; ?>
                <th>Appointment Date</th>
                <th>Doctor</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($payments as $payment): ?>
                <tr>
                    <td><?php echo htmlspecialchars(date("Y-m-d", strtotime($payment['payment_date']))); ?></td>
                    <td>$<?php echo htmlspecialchars(number_format($payment['amount'], 2)); ?></td>
                    <td><span class="status-<?php echo strtolower(htmlspecialchars($payment['payment_status'])); ?>"><?php echo ucfirst(htmlspecialchars($payment['payment_status'])); ?></span></td>
                    <td><?php echo htmlspecialchars($payment['payment_method'] ?: 'N/A'); ?></td>
                    <?php if ($user_role === 'admin') echo "<td>" . htmlspecialchars($payment['patient_name']) . "</td>"; ?>
                    <td><?php echo htmlspecialchars($payment['appointment_date']); ?></td>
                    <td><?php echo htmlspecialchars($payment['doctor_name']); ?></td>
                    <td class="action-links">
                        <a href="#">View Details</a> <!-- Placeholder -->
                        <?php if ($user_role === 'patient' && $payment['payment_status'] === 'pending'): ?>
                            <a href="#" style="color: #28a745;">Pay Now</a> <!-- Placeholder -->
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<div style="text-align: center; margin-top: 30px;">
    <a href="dashboard.php" class="btn-secondary">Back to Dashboard</a>
</div>

<?php
require_once 'includes/footer.php';
?>

