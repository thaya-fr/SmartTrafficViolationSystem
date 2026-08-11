<?php
/**
 * TrafficLens AI — View Drivers
 * Displays all drivers with search, pagination, and action buttons.
 */
$page_title = 'Drivers';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/db.php';

// Pagination
$per_page = 15;
$page = max(1, intval($_GET['page'] ?? 1));
$offset = ($page - 1) * $per_page;

// Search
$search = trim($_GET['search'] ?? '');

try {
    // Count total
    if ($search) {
        $count_stmt = $pdo->prepare("SELECT COUNT(*) as total FROM drivers WHERE full_name ILIKE :search OR license_number ILIKE :search OR phone ILIKE :search OR email ILIKE :search");
        $count_stmt->execute([':search' => "%{$search}%"]);
    } else {
        $count_stmt = $pdo->query("SELECT COUNT(*) as total FROM drivers");
    }
    $total_records = $count_stmt->fetch()['total'];
    $total_pages = ceil($total_records / $per_page);

    // Fetch drivers
    if ($search) {
        $stmt = $pdo->prepare("SELECT * FROM drivers WHERE full_name ILIKE :search OR license_number ILIKE :search OR phone ILIKE :search OR email ILIKE :search ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':search', "%{$search}%", PDO::PARAM_STR);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM drivers ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
    }
    $stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $drivers = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log("View drivers error: " . $e->getMessage());
    $drivers = [];
    $total_records = 0;
    $total_pages = 0;
}
?>

<!-- Toolbar -->
<div class="toolbar">
    <div class="search-box">
        <i class="fas fa-search"></i>
        <form method="GET" style="display:contents;">
            <input type="text" name="search" id="driverSearch" placeholder="Search drivers..." value="<?php echo htmlspecialchars($search); ?>">
        </form>
    </div>
    <div class="btn-group">
        <span class="text-meta"><?php echo number_format($total_records); ?> records</span>
        <a href="add_driver.php" class="btn-primary btn-sm">
            <i class="fas fa-plus"></i> Add Driver
        </a>
    </div>
</div>

<!-- Drivers Table -->
<?php if (count($drivers) > 0): ?>
<div class="table-wrapper">
    <table class="data-table" id="driversTable">
        <thead>
            <tr>
                <th>Full Name</th>
                <th>License Number</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Address</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($drivers as $driver): ?>
            <tr>
                <td><?php echo htmlspecialchars($driver['full_name']); ?></td>
                <td class="cell-mono"><?php echo htmlspecialchars($driver['license_number']); ?></td>
                <td class="cell-mono"><?php echo htmlspecialchars($driver['phone']); ?></td>
                <td class="cell-muted"><?php echo htmlspecialchars($driver['email'] ?? '—'); ?></td>
                <td class="cell-muted"><?php echo htmlspecialchars(mb_strimwidth($driver['address'] ?? '—', 0, 30, '...')); ?></td>
                <td class="cell-muted"><?php echo date('d M Y', strtotime($driver['created_at'])); ?></td>
                <td>
                    <div class="btn-group">
                        <a href="edit_driver.php?id=<?php echo $driver['driver_id']; ?>" class="btn-icon" title="Edit">
                            <i class="fas fa-pen"></i>
                        </a>
                        <button class="btn-icon danger" title="Delete" onclick="confirmDelete('delete_driver.php?id=<?php echo $driver['driver_id']; ?>', '<?php echo htmlspecialchars($driver['full_name'], ENT_QUOTES); ?>')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Pagination -->
<?php if ($total_pages > 1): ?>
<div class="pagination">
    <?php if ($page > 1): ?>
        <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>"><i class="fas fa-chevron-left"></i></a>
    <?php else: ?>
        <span class="disabled"><i class="fas fa-chevron-left"></i></span>
    <?php endif; ?>

    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
        <?php if ($i == $page): ?>
            <span class="active"><?php echo $i; ?></span>
        <?php else: ?>
            <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
        <?php endif; ?>
    <?php endfor; ?>

    <?php if ($page < $total_pages): ?>
        <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>"><i class="fas fa-chevron-right"></i></a>
    <?php else: ?>
        <span class="disabled"><i class="fas fa-chevron-right"></i></span>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php else: ?>
<div class="empty-state">
    <i class="fas fa-id-card"></i>
    <p class="empty-title">No drivers found</p>
    <p class="empty-text"><?php echo $search ? "No drivers match your search." : "Start by adding your first driver."; ?></p>
    <?php if (!$search): ?>
    <a href="add_driver.php" class="btn-primary btn-sm">
        <i class="fas fa-plus"></i> Add Driver
    </a>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
