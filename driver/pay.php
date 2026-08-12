<?php
/**
 * TrafficLens AI — Online Fine Payment Checkout & Digital E-Receipt
 */
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['driver_id'])) {
    header('Location: index.php');
    exit;
}

$driver_id = $_SESSION['driver_id'];
$violation_id = $_GET['violation_id'] ?? $_POST['violation_id'] ?? '';
$error = '';
$is_paid_now = isset($_GET['paid']);

if (empty($violation_id)) {
    header('Location: portal.php');
    exit;
}

try {
    // Fetch Violation
    $v_stmt = $pdo->prepare("
        SELECT v.*, vr.violation_type, vr.fine_amount, vh.vehicle_number, vh.manufacturer, vh.model, d.full_name, d.license_number, d.phone, d.email
        FROM violations v
        JOIN violation_rules vr ON v.rule_id = vr.rule_id
        JOIN vehicles vh ON v.vehicle_id = vh.vehicle_id
        JOIN drivers d ON v.driver_id = d.driver_id
        WHERE v.violation_id = :vid AND v.driver_id = :did
        LIMIT 1
    ");
    $v_stmt->execute([':vid' => $violation_id, ':did' => $driver_id]);
    $violation = $v_stmt->fetch();

    if (!$violation) {
        header('Location: portal.php');
        exit;
    }

    // Fetch existing payment record if paid
    $payment = null;
    if ($violation['payment_status'] === 'Paid') {
        $p_stmt = $pdo->prepare("SELECT * FROM payments WHERE violation_id = :vid ORDER BY created_at DESC LIMIT 1");
        $p_stmt->execute([':vid' => $violation_id]);
        $payment = $p_stmt->fetch();
    }

    // Process Online Payment Submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $violation['payment_status'] === 'Pending') {
        $payment_method = trim($_POST['payment_method'] ?? 'UPI / GPay');
        $amount = floatval($violation['fine_amount']);
        $transaction_id = 'TXN-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 10));

        // Process in transaction
        try {
            $pdo->beginTransaction();

            // Insert payment
            $ins = $pdo->prepare("
                INSERT INTO payments (violation_id, amount, payment_method, transaction_id, payment_date, payment_status)
                VALUES (:vid, :amt, :method, :txnid, CURRENT_DATE, 'Paid')
            ");
            $ins->execute([
                ':vid' => $violation_id,
                ':amt' => $amount,
                ':method' => $payment_method,
                ':txnid' => $transaction_id
            ]);

            // Update violation status
            $upd = $pdo->prepare("UPDATE violations SET payment_status = 'Paid' WHERE violation_id = :vid");
            $upd->execute([':vid' => $violation_id]);

            $pdo->commit();

            // Redirect to receipt
            header("Location: pay.php?receipt=1&violation_id=" . urlencode($violation_id) . "&paid=1");
            exit;
        } catch (PDOException $ex) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $ex;
        }
    }

} catch (PDOException $e) {
    error_log("Payment processing error: " . $e->getMessage());
    $error = 'Database error processing payment. Please try again.';
}

$show_receipt = ($violation['payment_status'] === 'Paid' || isset($_GET['receipt']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="TrafficLens AI — Online Fine Payment & E-Receipt">
    <title><?php echo $show_receipt ? 'Official E-Receipt' : 'Online Payment Checkout'; ?> — TrafficLens AI</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=IBM+Plex+Mono:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
    @media print {
        .dark-header, .dark-footer, .no-print { display: none !important; }
        body { background: white !important; color: black !important; }
        .dark-card { border: 1px solid #ccc !important; background: white !important; color: black !important; }
        .text-white, h2, h3, h4, strong { color: black !important; }
    }
    </style>
</head>
<body class="dark-portal-body">

    <!-- Header Navigation -->
    <header class="dark-header">
        <div class="dark-header-left">
            <a href="portal.php" class="dark-brand">
                <i class="fas fa-video"></i>
                <div>Traffic<span>Lens</span> AI</div>
            </a>
            <span class="dark-badge-pill">
                <span class="pill-dot"></span> Payment Gateway
            </span>
        </div>

        <div class="dark-header-right">
            <a href="portal.php" class="btn-dark-text"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </div>
    </header>

    <main class="dark-main-container" style="max-width: 720px;">

        <?php if ($is_paid_now): ?>
        <div class="toast toast-success mb-24 no-print" style="position: static; animation: none; width: 100%; max-width: 100%;">
            <i class="fas fa-check-circle toast-icon"></i>
            <div>Payment Completed Successfully! Your e-challan has been marked as <strong>PAID</strong>.</div>
        </div>
        <?php endif; ?>

        <?php if ($show_receipt): ?>
        <!-- ================= MODE: DIGITAL E-RECEIPT ================= -->
        <div class="dark-card" style="border-color: rgba(152, 255, 56, 0.4); box-shadow: 0 0 40px rgba(152, 255, 56, 0.1);">
            
            <div class="flex items-center justify-between mb-24" style="border-bottom: 1px solid rgba(255, 255, 255, 0.08); padding-bottom: 20px;">
                <div class="flex items-center gap-12">
                    <div style="width: 48px; height: 48px; border-radius: 12px; background-color: rgba(152, 255, 56, 0.1); border: 1px solid rgba(152, 255, 56, 0.3); display: flex; align-items: center; justify-content: center; font-size: 22px; color: #98ff38;">
                        <i class="fas fa-receipt"></i>
                    </div>
                    <div>
                        <h2 style="font-size: 20px; font-weight: 800; color: #ffffff;">OFFICIAL E-CHALLAN RECEIPT</h2>
                        <div style="font-family: var(--font-mono); font-size: 11px; color: #9ca3af; text-transform: uppercase;">TrafficLens AI Enforcement Gateway</div>
                    </div>
                </div>

                <div class="text-right">
                    <span class="badge badge-paid" style="font-size: 13px; padding: 6px 14px;">
                        <span class="dot"></span> VERIFIED & PAID
                    </span>
                </div>
            </div>

            <!-- Receipt Grid -->
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 24px; font-size: 14px;">
                <div>
                    <span class="text-label" style="display: block; margin-bottom: 4px;">Transaction ID</span>
                    <strong class="cell-mono" style="color: #00e5ff; font-size: 15px;"><?php echo htmlspecialchars($payment['transaction_id'] ?? 'TXN-SETTLED'); ?></strong>
                </div>

                <div>
                    <span class="text-label" style="display: block; margin-bottom: 4px;">Payment Date</span>
                    <strong style="color: #ffffff;"><?php echo date('d M Y', strtotime($payment['payment_date'] ?? $violation['violation_date'])); ?></strong>
                </div>

                <div>
                    <span class="text-label" style="display: block; margin-bottom: 4px;">Payer Name</span>
                    <strong style="color: #ffffff;"><?php echo htmlspecialchars($violation['full_name']); ?></strong>
                </div>

                <div>
                    <span class="text-label" style="display: block; margin-bottom: 4px;">Driving License</span>
                    <strong class="cell-mono" style="color: #ffffff;"><?php echo htmlspecialchars($violation['license_number']); ?></strong>
                </div>

                <div>
                    <span class="text-label" style="display: block; margin-bottom: 4px;">Vehicle Number</span>
                    <strong class="cell-mono" style="color: #00e5ff;"><?php echo htmlspecialchars($violation['vehicle_number']); ?></strong>
                </div>

                <div>
                    <span class="text-label" style="display: block; margin-bottom: 4px;">Payment Method</span>
                    <strong style="color: #ffffff;"><?php echo htmlspecialchars($payment['payment_method'] ?? 'Online Payment'); ?></strong>
                </div>
            </div>

            <!-- Fine Breakdown Box -->
            <div style="background-color: #10141d; border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 14px; padding: 20px; margin-bottom: 24px;">
                <div class="flex items-center justify-between mb-8">
                    <span style="color: #9ca3af; font-size: 13px;">Violation Offense:</span>
                    <strong style="color: #ffffff; font-size: 14px;"><?php echo htmlspecialchars($violation['violation_type']); ?></strong>
                </div>
                <div class="flex items-center justify-between mb-8">
                    <span style="color: #9ca3af; font-size: 13px;">Location:</span>
                    <span style="color: #ffffff; font-size: 13px;"><?php echo htmlspecialchars($violation['location']); ?></span>
                </div>
                <div class="flex items-center justify-between" style="border-top: 1px dashed rgba(255, 255, 255, 0.1); padding-top: 12px; margin-top: 12px;">
                    <span style="color: #ffffff; font-weight: 700; font-size: 15px;">Total Fine Amount Settled:</span>
                    <strong class="cell-mono" style="color: #98ff38; font-size: 20px;">₹<?php echo number_format($violation['fine_amount'], 2); ?></strong>
                </div>
            </div>

            <div class="flex items-center justify-between no-print">
                <a href="portal.php" class="btn-ghost">
                    <i class="fas fa-arrow-left"></i> Return to Dashboard
                </a>

                <button onclick="window.print()" class="btn-primary">
                    <i class="fas fa-print"></i> Print E-Receipt
                </button>
            </div>
        </div>

        <?php else: ?>
        <!-- ================= MODE: ONLINE CHECKOUT ================= -->
        <div class="dark-card" style="border-color: rgba(0, 229, 255, 0.3);">
            
            <div class="card-header mb-24">
                <div class="flex items-center gap-12">
                    <i class="fas fa-credit-card" style="color: #00e5ff; font-size: 22px;"></i>
                    <h3 class="card-title">E-Challan Online Fine Payment</h3>
                </div>
                <span class="text-meta">Instant Settlement</span>
            </div>

            <!-- Fine Summary Box -->
            <div style="background-color: #10141d; border: 1px solid rgba(0, 229, 255, 0.2); border-radius: 16px; padding: 20px; margin-bottom: 24px;">
                <div class="flex items-center justify-between mb-12">
                    <div>
                        <div style="font-size: 18px; font-weight: 800; color: #ffffff;"><?php echo htmlspecialchars($violation['violation_type']); ?></div>
                        <div style="font-size: 13px; color: #9ca3af; margin-top: 2px;">
                            Vehicle: <strong class="cell-mono" style="color: #00e5ff;"><?php echo htmlspecialchars($violation['vehicle_number']); ?></strong> &bull; Location: <?php echo htmlspecialchars($violation['location']); ?>
                        </div>
                    </div>
                    <div class="text-right">
                        <div style="font-family: var(--font-mono); font-size: 11px; color: #9ca3af; text-transform: uppercase;">Amount Due</div>
                        <div class="cell-mono" style="font-size: 26px; font-weight: 800; color: #98ff38;">₹<?php echo number_format($violation['fine_amount'], 2); ?></div>
                    </div>
                </div>
            </div>

            <!-- Payment Form -->
            <form method="POST" action="pay.php">
                <input type="hidden" name="violation_id" value="<?php echo htmlspecialchars($violation_id); ?>">

                <div class="form-group mb-24">
                    <label class="form-label" style="margin-bottom: 12px;">Select Online Payment Method</label>

                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px;">
                        <label style="background: #10141d; border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 12px; padding: 16px; cursor: pointer; display: flex; flex-direction: column; gap: 8px; transition: all 150ms ease;">
                            <input type="radio" name="payment_method" value="UPI / GPay" checked style="accent-color: #00e5ff;">
                            <span style="font-weight: 700; color: #ffffff; font-size: 13px;"><i class="fas fa-qrcode" style="color: #00e5ff; margin-right: 4px;"></i> UPI / GPay</span>
                            <span style="font-size: 11px; color: #9ca3af;">Instant QR & VPA</span>
                        </label>

                        <label style="background: #10141d; border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 12px; padding: 16px; cursor: pointer; display: flex; flex-direction: column; gap: 8px; transition: all 150ms ease;">
                            <input type="radio" name="payment_method" value="Credit / Debit Card" style="accent-color: #00e5ff;">
                            <span style="font-weight: 700; color: #ffffff; font-size: 13px;"><i class="fas fa-credit-card" style="color: #98ff38; margin-right: 4px;"></i> Cards</span>
                            <span style="font-size: 11px; color: #9ca3af;">Visa, MasterCard, RuPay</span>
                        </label>

                        <label style="background: #10141d; border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 12px; padding: 16px; cursor: pointer; display: flex; flex-direction: column; gap: 8px; transition: all 150ms ease;">
                            <input type="radio" name="payment_method" value="Net Banking" style="accent-color: #00e5ff;">
                            <span style="font-weight: 700; color: #ffffff; font-size: 13px;"><i class="fas fa-building-columns" style="color: #f59e0b; margin-right: 4px;"></i> NetBanking</span>
                            <span style="font-size: 11px; color: #9ca3af;">All Major Banks</span>
                        </label>
                    </div>
                </div>

                <div class="flex items-center justify-between" style="border-top: 1px solid rgba(255, 255, 255, 0.08); padding-top: 20px;">
                    <a href="portal.php" class="btn-ghost">Cancel</a>

                    <button type="submit" class="btn-primary" style="padding: 14px 28px; font-size: 15px;">
                        <span>Complete Payment (₹<?php echo number_format($violation['fine_amount'], 2); ?>)</span>
                        <i class="fas fa-lock"></i>
                    </button>
                </div>
            </form>

        </div>
        <?php endif; ?>

    </main>

    <footer class="dark-footer no-print">
        <div>© <?php echo date('Y'); ?> TrafficLens AI. Secure Public Payment Gateway.</div>
    </footer>

</body>
</html>
