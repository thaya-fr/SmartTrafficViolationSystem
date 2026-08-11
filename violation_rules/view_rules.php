<?php
/**
 * TrafficLens AI — View Violation Rules
 * Displays predefined traffic violations and fine amounts.
 */
$page_title = 'Violation Rules';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/db.php';

$search = trim($_GET['search'] ?? '');

try {
    if ($search) {
        $stmt = $pdo->prepare("SELECT * FROM violation_rules WHERE violation_type ILIKE :search OR description ILIKE :search ORDER BY violation_type ASC");
        $stmt->execute([':search' => "%{$search}%"]);
    } else {
        $stmt = $pdo->query("SELECT * FROM violation_rules ORDER BY violation_type ASC");
    }
    $rules = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("View rules error: " . $e->getMessage());
    $rules = [];
}
?>

<div class="toolbar">
    <div class="search-box">
        <i class="fas fa-search"></i>
        <form method="GET" style="display:contents;">
            <input type="text" name="search" placeholder="Search violation rules..." value="<?php echo htmlspecialchars($search); ?>">
        </form>
    </div>
    <div class="btn-group">
        <span class="text-meta"><?php echo count($rules); ?> rules</span>
        <a href="add_rule.php" class="btn-primary btn-sm"><i class="fas fa-plus"></i> Add Rule</a>
    </div>
</div>

<?php if (count($rules) > 0): ?>
<div class="table-wrapper">
    <table class="data-table">
        <thead>
            <tr>
                <th>Violation Type</th>
                <th>Fine Amount</th>
                <th>Description</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rules as $r): ?>
            <tr>
                <td><?php echo htmlspecialchars($r['violation_type']); ?></td>
                <td class="cell-mono">₹<?php echo number_format($r['fine_amount'], 2); ?></td>
                <td class="cell-muted"><?php echo htmlspecialchars(mb_strimwidth($r['description'] ?? '—', 0, 60, '...')); ?></td>
                <td class="cell-muted"><?php echo date('d M Y', strtotime($r['created_at'])); ?></td>
                <td>
                    <div class="btn-group">
                        <a href="edit_rule.php?id=<?php echo $r['rule_id']; ?>" class="btn-icon" title="Edit"><i class="fas fa-pen"></i></a>
                        <button class="btn-icon danger" title="Delete" onclick="confirmDelete('delete_rule.php?id=<?php echo $r['rule_id']; ?>', '<?php echo htmlspecialchars($r['violation_type'], ENT_QUOTES); ?>')"><i class="fas fa-trash"></i></button>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
<div class="empty-state">
    <i class="fas fa-gavel"></i>
    <p class="empty-title">No violation rules found</p>
    <p class="empty-text"><?php echo $search ? "No rules match your search." : "Add predefined violation types and fine amounts."; ?></p>
    <?php if (!$search): ?><a href="add_rule.php" class="btn-primary btn-sm"><i class="fas fa-plus"></i> Add Rule</a><?php endif; ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
