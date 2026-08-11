<?php
/**
 * TrafficLens AI — View Violations
 * Displays all recorded traffic violations with details.
 */
$page_title = 'Violations';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/db.php';

$per_page = 15;
$page = max(1, intval($_GET['page'] ?? 1));
$offset = ($page - 1) * $per_page;
$search = trim($_GET['search'] ?? '');
$status_filter = $_GET['status'] ?? '';

try {
    $where_clauses = [];
    $params = [];

    if ($search) {
        $where_clauses[] = "(d.full_name ILIKE :search OR vh.vehicle_number ILIKE :search OR vr.violation_type ILIKE :search OR v.location ILIKE :search OR v.officer_name ILIKE :search)";
        $params[':search'] = "%{$search}%";
    }
    if ($status_filter) {
        $where_clauses[] = "v.payment_status = :status";
        $params[':status'] = $status_filter;
    }

    $where = count($where_clauses) > 0 ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

    $count_stmt = $pdo->prepare("
        SELECT COUNT(*) as total 
        FROM violations v 
        JOIN drivers d ON v.driver_id = d.driver_id 
        JOIN vehicles vh ON v.vehicle_id = vh.vehicle_id 
        JOIN violation_rules vr ON v.rule_id = vr.rule_id 
        {$where}
    ");
    $count_stmt->execute($params);
    $total_records = $count_stmt->fetch()['total'];
    $total_pages = ceil($total_records / $per_page);

    $stmt = $pdo->prepare("
        SELECT v.*, d.full_name as driver_name, vh.vehicle_number, vr.violation_type, vr.fine_amount
        FROM violations v
        JOIN drivers d ON v.driver_id = d.driver_id
        JOIN vehicles vh ON v.vehicle_id = vh.vehicle_id
        JOIN violation_rules vr ON v.rule_id = vr.rule_id
        {$where}
        ORDER BY v.created_at DESC
        LIMIT :limit OFFSET :offset
    ");
    foreach ($params as $key => $val) { $stmt->bindValue($key, $val, PDO::PARAM_STR); }
    $stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $violations = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log("View violations error: " . $e->getMessage());
    $violations = [];
    $total_records = 0;
    $total_pages = 0;
}
?>

<div class="toolbar">
    <div class="search-box">
        <i class="fas fa-search"></i>
        <form method="GET" style="display:contents;">
            <input type="text" name="search" placeholder="Search violations..." value="<?php echo htmlspecialchars($search); ?>">
            <input type="hidden" name="status" value="<?php echo htmlspecialchars($status_filter); ?>">
        </form>
    </div>
    <div class="btn-group">
        <div class="filter-group">
            <a href="?search=<?php echo urlencode($search); ?>" class="btn-ghost btn-sm <?php echo !$status_filter ? 'btn-secondary' : ''; ?>">All</a>
            <a href="?search=<?php echo urlencode($search); ?>&status=Pending" class="btn-ghost btn-sm <?php echo $status_filter === 'Pending' ? 'btn-secondary' : ''; ?>">Pending</a>
            <a href="?search=<?php echo urlencode($search); ?>&status=Paid" class="btn-ghost btn-sm <?php echo $status_filter === 'Paid' ? 'btn-secondary' : ''; ?>">Paid</a>
        </div>
        <span class="text-meta"><?php echo number_format($total_records); ?> records</span>
        <a href="add_violation.php" class="btn-primary btn-sm"><i class="fas fa-plus"></i> Add Violation</a>
    </div>
</div>

<?php if (count($violations) > 0): ?>
<div class="table-wrapper">
    <table class="data-table">
        <thead>
            <tr>
                <th>Driver</th>
                <th>Vehicle</th>
                <th>Violation</th>
                <th>Fine</th>
                <th>Location</th>
                <th>Officer</th>
                <th>Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($violations as $v): ?>
            <tr>
                <td><?php echo htmlspecialchars($v['driver_name']); ?></td>
                <td class="cell-mono"><?php echo htmlspecialchars($v['vehicle_number']); ?></td>
                <td><?php echo htmlspecialchars($v['violation_type']); ?></td>
                <td class="cell-mono">₹<?php echo number_format($v['fine_amount'], 2); ?></td>
                <td class="cell-muted"><?php echo htmlspecialchars(mb_strimwidth($v['location'], 0, 25, '...')); ?></td>
                <td class="cell-muted"><?php echo htmlspecialchars($v['officer_name']); ?></td>
                <td class="cell-muted"><?php echo date('d M Y', strtotime($v['violation_date'])); ?></td>
                <td>
                    <span class="badge badge-<?php echo strtolower($v['payment_status']); ?>">
                        <span class="dot"></span>
                        <?php echo $v['payment_status']; ?>
                    </span>
                </td>
                <td>
                    <div class="btn-group">
                        <a href="edit_violation.php?id=<?php echo $v['violation_id']; ?>" class="btn-icon" title="Edit"><i class="fas fa-pen"></i></a>
                        <button class="btn-icon danger" title="Delete" onclick="confirmDelete('delete_violation.php?id=<?php echo $v['violation_id']; ?>', 'this violation')"><i class="fas fa-trash"></i></button>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if ($total_pages > 1): ?>
<div class="pagination">
    <?php if ($page > 1): ?><a href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>"><i class="fas fa-chevron-left"></i></a><?php else: ?><span class="disabled"><i class="fas fa-chevron-left"></i></span><?php endif; ?>
    <?php for ($i = max(1, $page-2); $i <= min($total_pages, $page+2); $i++): ?>
        <?php if ($i == $page): ?><span class="active"><?php echo $i; ?></span>
        <?php else: ?><a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>"><?php echo $i; ?></a><?php endif; ?>
    <?php endfor; ?>
    <?php if ($page < $total_pages): ?><a href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>"><i class="fas fa-chevron-right"></i></a><?php else: ?><span class="disabled"><i class="fas fa-chevron-right"></i></span><?php endif; ?>
</div>
<?php endif; ?>

<?php else: ?>
<div class="empty-state">
    <i class="fas fa-exclamation-triangle"></i>
    <p class="empty-title">No violations found</p>
    <p class="empty-text"><?php echo ($search || $status_filter) ? "No violations match your filters." : "Record your first traffic violation."; ?></p>
    <?php if (!$search && !$status_filter): ?><a href="add_violation.php" class="btn-primary btn-sm"><i class="fas fa-plus"></i> Add Violation</a><?php endif; ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
