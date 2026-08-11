<?php
/**
 * TrafficLens AI — View Vehicles
 * Displays all vehicles with driver info, search, and pagination.
 */
$page_title = 'Vehicles';
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
        $where = "WHERE v.vehicle_number ILIKE :search OR v.vehicle_type ILIKE :search OR v.manufacturer ILIKE :search OR v.model ILIKE :search OR d.full_name ILIKE :search";
        $params[':search'] = "%{$search}%";
    }

    $count_stmt = $pdo->prepare("SELECT COUNT(*) as total FROM vehicles v JOIN drivers d ON v.driver_id = d.driver_id {$where}");
    $count_stmt->execute($params);
    $total_records = $count_stmt->fetch()['total'];
    $total_pages = ceil($total_records / $per_page);

    $stmt = $pdo->prepare("
        SELECT v.*, d.full_name as driver_name 
        FROM vehicles v 
        JOIN drivers d ON v.driver_id = d.driver_id 
        {$where} 
        ORDER BY v.created_at DESC 
        LIMIT :limit OFFSET :offset
    ");
    foreach ($params as $key => $val) { $stmt->bindValue($key, $val, PDO::PARAM_STR); }
    $stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $vehicles = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log("View vehicles error: " . $e->getMessage());
    $vehicles = [];
    $total_records = 0;
    $total_pages = 0;
}
?>

<div class="toolbar">
    <div class="search-box">
        <i class="fas fa-search"></i>
        <form method="GET" style="display:contents;">
            <input type="text" name="search" placeholder="Search vehicles..." value="<?php echo htmlspecialchars($search); ?>">
        </form>
    </div>
    <div class="btn-group">
        <span class="text-meta"><?php echo number_format($total_records); ?> records</span>
        <a href="add_vehicle.php" class="btn-primary btn-sm">
            <i class="fas fa-plus"></i> Add Vehicle
        </a>
    </div>
</div>

<?php if (count($vehicles) > 0): ?>
<div class="table-wrapper">
    <table class="data-table">
        <thead>
            <tr>
                <th>Vehicle Number</th>
                <th>Type</th>
                <th>Manufacturer</th>
                <th>Model</th>
                <th>Color</th>
                <th>Owner</th>
                <th>Reg. Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($vehicles as $v): ?>
            <tr>
                <td class="cell-mono"><?php echo htmlspecialchars($v['vehicle_number']); ?></td>
                <td><?php echo htmlspecialchars($v['vehicle_type']); ?></td>
                <td><?php echo htmlspecialchars($v['manufacturer']); ?></td>
                <td><?php echo htmlspecialchars($v['model']); ?></td>
                <td class="cell-muted"><?php echo htmlspecialchars($v['color'] ?? '—'); ?></td>
                <td><?php echo htmlspecialchars($v['driver_name']); ?></td>
                <td class="cell-muted"><?php echo date('d M Y', strtotime($v['registration_date'])); ?></td>
                <td>
                    <div class="btn-group">
                        <a href="edit_vehicle.php?id=<?php echo $v['vehicle_id']; ?>" class="btn-icon" title="Edit"><i class="fas fa-pen"></i></a>
                        <button class="btn-icon danger" title="Delete" onclick="confirmDelete('delete_vehicle.php?id=<?php echo $v['vehicle_id']; ?>', '<?php echo htmlspecialchars($v['vehicle_number'], ENT_QUOTES); ?>')"><i class="fas fa-trash"></i></button>
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
    <i class="fas fa-car"></i>
    <p class="empty-title">No vehicles found</p>
    <p class="empty-text"><?php echo $search ? "No vehicles match your search." : "Start by registering your first vehicle."; ?></p>
    <?php if (!$search): ?><a href="add_vehicle.php" class="btn-primary btn-sm"><i class="fas fa-plus"></i> Add Vehicle</a><?php endif; ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
