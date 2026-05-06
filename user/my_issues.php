<?php
require_once "../includes/auth.php";
requireUserOrAdmin();

require_once "../config/db.php";

$stmt = $pdo->prepare("
    SELECT
        issues.*,
        categories.name AS category_name,
        users.first_name AS assigned_first_name,
        users.surname AS assigned_surname
    FROM issues
    LEFT JOIN categories ON issues.category_id = categories.id
    LEFT JOIN users ON issues.assigned_admin = users.id
    WHERE issues.created_by = ?
    ORDER BY issues.created_at DESC
");

$stmt->execute([$_SESSION["user_id"]]);
$issues = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Requests</title>
</head>
<body>

<h2>My Requests</h2>

<p><a href="dashboard.php">Back to Dashboard</a></p>
<p><a href="create_issue.php">Create New Request</a></p>

<table border="1" cellpadding="10">
<tr>
    <th>ID</th>
    <th>Title</th>
    <th>Request Type</th>
    <th>Category</th>
    <th>Status</th>
    <th>Assigned Admin</th>
    <th>Created At</th>
</tr>

<?php foreach ($issues as $issue): ?>
<tr>
    <td><?php echo htmlspecialchars($issue["id"]); ?></td>
    <td><?php echo htmlspecialchars($issue["title"]); ?></td>
    <td><?php echo htmlspecialchars($issue["request_type"]); ?></td>
    <td><?php echo htmlspecialchars($issue["category_name"] ?? "N/A"); ?></td>
    <td><?php echo htmlspecialchars($issue["status"]); ?></td>
    <td>
        <?php
        if (!empty($issue["assigned_first_name"])) {
            echo htmlspecialchars($issue["assigned_first_name"] . " " . $issue["assigned_surname"]);
        } else {
            echo "Unassigned";
        }
        ?>
    </td>
    <td><?php echo htmlspecialchars($issue["created_at"]); ?></td>
</tr>
<?php endforeach; ?>

</table>
<script>
window.addEventListener("load", () => {
    document.body.classList.add("loaded");
});
</script>
</body>
</html>