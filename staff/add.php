<?php
$pageTitle = 'Add Staff - Multimedia Equipment Watcher';
require_once '../includes/header.php';
requireAdmin();

$pdo = getDBConnection();
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'staff';
    
    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Please fill in all required fields';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        try {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'Email already exists';
            } else {
                // Create user
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
                $stmt->execute([$name, $email, $hashedPassword, $role]);
                $success = 'Staff member added successfully!';
                
                // Clear form
                $name = $email = '';
                $role = 'staff';
            }
        } catch (Exception $e) {
            $error = 'Error adding staff: ' . $e->getMessage();
        }
    }
}
?>

<!-- Page Header -->
<div class="flex justify-between items-center mb-6">
    <h2 class="text-3xl font-bold text-gray-800">Add New Staff</h2>
    <a href="list.php" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg transition">← Back to List</a>
</div>

<!-- Form Container -->
<div class="max-w-3xl bg-white rounded-lg shadow-sm p-6">
    <?php if ($success): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <form method="POST" action="" class="space-y-5">
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
            <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($name ?? ''); ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
        </div>
        
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address *</label>
            <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($email ?? ''); ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
        </div>
        
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password *</label>
            <input type="password" id="password" name="password" required minlength="6" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
            <p class="text-xs text-gray-500 mt-1">Minimum 6 characters</p>
        </div>
        
        <div>
            <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-2">Confirm Password *</label>
            <input type="password" id="confirm_password" name="confirm_password" required minlength="6" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
        </div>
        
        <div>
            <label for="role" class="block text-sm font-medium text-gray-700 mb-2">Role *</label>
            <select id="role" name="role" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                <option value="staff" <?php echo ($role ?? 'staff') === 'staff' ? 'selected' : ''; ?>>Staff</option>
                <option value="admin" <?php echo ($role ?? '') === 'admin' ? 'selected' : ''; ?>>Admin</option>
            </select>
        </div>
        
        <div class="flex gap-3 pt-4">
            <button type="submit" class="px-6 py-3 bg-primary hover:bg-primary-dark text-white font-semibold rounded-lg transition">Add Staff</button>
            <a href="list.php" class="px-6 py-3 bg-gray-400 hover:bg-gray-500 text-white font-semibold rounded-lg transition">Cancel</a>
        </div>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>
