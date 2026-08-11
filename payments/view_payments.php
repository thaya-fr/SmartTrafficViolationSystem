<?php
/**
 * TrafficLens AI — View Payments
 */
$page_title = 'Payments';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/db.php';

$per_page = 15;
$page = max(1, intval($_GET['page'] ?? 1));
$offset = ($page - 1) * $per_page;
$search = trim($_GET['search'] ?? '');

try {
    $where = '';
    $params = [];
    if ($search) {
        $where = "WHERE d.full_name ILIKE :search OR vh.vehicle_number ILIKE :search OR p.transaction_id ILIKE :search OR p.payment_method ILIKE :search";
        $params[':search'] = "%{$search}%";
    }

    $count_stmt = $pdo->prepare("
        SELECT COUNT(*) as total FROM payments p
        JOIN violations v ON p.violation_id = v.violation_id
        JOIN drivers d ON v.driver_id = d.driver_id
        JOIN vehicles vh ON v.vehicle_id = vh.vehicle_id
        {$where}
    ");
    $count_stmt->execute($params);
    $total_records = $count_stmt->fetch()['total'];
    $total_pages = ceil($total_records / $per_page);

    $stmt = $pdo->prepare("
        SELECT p.*, d.full_name as driver_name, vh.vehicle_number, vr.violation_type, vr.fine_amount
        FROM payments p
        JOIN violations v ON p.violation_id = v.violation_id
        JOIN drivers d ON v.driver_id = d.driver_id
        JOIN vehicles vh ON v.vehicle_id = vh.vehicle_id
        JOIN violation_rules vr ON v.rule_id = vr.rule_id
        {$where}
        ORDER BY p.created_at DESC
        LIMIT :limit OFFSET :offset
    ");
    foreach ($params as $key => $val) { $stmt->bindValue($key, $val, PDO::PARAM_STR); }
    $stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $payments = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log("View payments error: " . $e->getMessage());
    $payments = [];
    $total_records = 0;
    $total_pages = 0;
}
?>

<div class="toolbar">
    <div class="search-box">
        <i class="fas fa-search"></i>
        <form method="GET" style="display:contents;">
            <input type="text" name="search" placeholder="Search payments..." value="<?php echo htmlspecialchars($search); ?>">
        </form>
    </div>
    <div class="btn-group">
        <span class="text-meta"><?php echo number_format($total_records); ?> records</span>
        <?php if (count($payments) > 0 || $search): ?>
        <a href="add_payment.php" class="btn-primary btn-sm"><i class="fas fa-plus"></i> Add Payment</a>
        <?php endif; ?>
    </div>
</div>

<?php if (count($payments) > 0): ?>
<div class="table-wrapper">
    <table class="data-table">
        <thead>
            <tr>
                <th>Driver</th>
                <th>Vehicle</th>
                <th>Violation</th>
                <th>Amount</th>
                <th>Method</th>
                <th>Transaction ID</th>
                <th>Payment Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($payments as $p): ?>
            <tr>
                <td><?php echo htmlspecialchars($p['driver_name']); ?></td>
                <td class="cell-mono"><?php echo htmlspecialchars($p['vehicle_number']); ?></td>
                <td><?php echo htmlspecialchars($p['violation_type']); ?></td>
                <td class="cell-mono">₹<?php echo number_format($p['amount'], 2); ?></td>
                <td><?php echo htmlspecialchars($p['payment_method']); ?></td>
                <td class="cell-mono"><?php echo htmlspecialchars($p['transaction_id']); ?></td>
                <td class="cell-muted"><?php echo date('d M Y', strtotime($p['payment_date'])); ?></td>
                <td><span class="badge badge-paid"><span class="dot"></span> <?php echo $p['payment_status']; ?></span></td>
                <td>
                    <div class="btn-group">
                        <a href="edit_payment.php?id=<?php echo $p['payment_id']; ?>" class="btn-icon" title="Edit"><i class="fas fa-pen"></i></a>
                        <button class="btn-icon danger" title="Delete" onclick="confirmDelete('delete_payment.php?id=<?php echo $p['payment_id']; ?>', 'payment #<?php echo htmlspecialchars($p['transaction_id'], ENT_QUOTES); ?>')"><i class="fas fa-trash"></i></button>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if ($total_pages > 1): ?>
<div class="pagination">
    <?php if ($page > 1): ?><a href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>"><i class="fas fa-chevron-left"></i></a><?php else: ?><span class="disabled"><i class="fas fa-chevron-left"></i></span><?php endif; ?>
    <?php for ($i = max(1, $page-2); $i <= min($total_pages, $page+2); $i++): ?>
        <?php if ($i == $page): ?><span class="active"><?php echo $i; ?></span>
        <?php else: ?><a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a><?php endif; ?>
    <?php endfor; ?>
    <?php if ($page < $total_pages): ?><a href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>"><i class="fas fa-chevron-right"></i></a><?php else: ?><span class="disabled"><i class="fas fa-chevron-right"></i></span><?php endif; ?>
</div>
<?php endif; ?>

<?php else: ?>
<div class="empty-state">
    <i class="fas fa-credit-card"></i>
    <p class="empty-title">No payments found</p>
    <p class="empty-text"><?php echo $search ? "No payments match your search." : "Process your first fine payment."; ?></p>
    <?php if (!$search): ?><a href="add_payment.php" class="btn-primary btn-sm"><i class="fas fa-plus"></i> Add Payment</a><?php endif; ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
