<?php
require_once __DIR__ . '/config/db.php';
$pageTitle = "Sign In | AgriFresh Connect";
$activePage = 'login';

$redirect = trim($_GET['redirect'] ?? $_POST['redirect'] ?? '');
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $demoRole = trim($_POST['demo_login'] ?? '');

    if (!empty($demoRole)) {
        if ($demoRole === 'admin') {
            $email = 'admin@famox.my';
        } elseif ($demoRole === 'farmer') {
            $email = 'azman@ladangmakmur.my';
        } elseif ($demoRole === 'buyer') {
            $email = 'aizat@aimst.edu.my';
        }
        $password = 'password';
    }

    if (!empty($email)) {
        $stmt = $pdo->prepare("SELECT u.*, r.name as role_name, r.slug as role_slug 
                               FROM users u 
                               JOIN roles r ON u.role_id = r.id 
                               WHERE u.email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && ($password === $user['password'] || password_verify($password, $user['password']) || (!empty($demoRole) && $password === 'password'))) {
            $_SESSION['user'] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role_id' => $user['role_id'],
                'role_name' => $user['role_name'],
                'role_slug' => $user['role_slug'],
                'phone' => $user['phone'],
                'address' => $user['address'],
                'avatar' => $user['avatar'] ?? null
            ];

            $log = $pdo->prepare("INSERT INTO activity_logs (user_name, action, description, ip_address) VALUES (?, 'User Signed In', ?, ?)");
            $log->execute([$user['name'], "Signed in as " . $user['role_name'], $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1']);

            if (!empty($redirect)) {
                header("Location: " . $redirect);
            } elseif ($user['role_slug'] === 'farmer') {
                header("Location: farmer_dashboard.php");
            } elseif ($user['role_slug'] === 'admin') {
                header("Location: admin_panel.php");
            } elseif ($user['role_slug'] === 'buyer') {
                header("Location: buyer_dashboard.php");
            } else {
                header("Location: index.php");
            }
            exit;
        } else {
            $error = "Invalid email or password. Please try again.";
        }
    } else {
        $error = "Please provide your email and password.";
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="auth-page-wrapper">
    <div class="auth-card-glass">
        <div style="text-align: center; margin-bottom: 24px;">
            <div style="width: 60px; height: 60px; border-radius: 50%; overflow: hidden; margin: 0 auto 12px; border: 2px solid #10b981; box-shadow: 0 4px 14px rgba(16,185,129,0.25);">
                <img src="assets/img/agrifresh_logo.png" alt="AgriFresh Logo" style="width: 100%; height: 100%; object-fit: cover;">
            </div>
            <h1 style="font-size: 24px; font-weight: 900; color: #111827;">Sign In to AgriFresh</h1>
            <p style="font-size: 13px; color: #6b7280; margin-top: 4px;">
                <?= $redirect === 'checkout.php' ? '🔒 Please sign in to complete your checkout and track delivery' : 'Direct marketplace for Famox Kedah supplier farmers' ?>
            </p>
        </div>

        <?php if ($redirect === 'checkout.php'): ?>
            <div style="background: #ecfdf5; color: #065f46; padding: 12px 16px; border-radius: 14px; font-size: 12px; margin-bottom: 20px; border: 1px solid #a7f3d0; text-align: center;">
                🛒 <strong>Your produce bag is saved!</strong> Sign in below to finish your order.
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div style="background: #fee2e2; color: #991b1b; padding: 12px 16px; border-radius: 14px; font-size: 12px; margin-bottom: 20px; border: 1px solid #fca5a5; text-align: center;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- 1-Click Demo Logins for Lecturer Presentation -->
        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 20px; padding: 16px; margin-bottom: 24px;">
            <div style="font-size: 11px; font-weight: 800; color: #166534; text-transform: uppercase; margin-bottom: 8px; text-align: center;">
                ⚡ 1-Click Demo Logins (For Lecturer Presentation)
            </div>
            <div style="display: grid; grid-template-columns: 1fr; gap: 8px;">
                <form method="POST" action="login.php" style="margin: 0;">
                    <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
                    <input type="hidden" name="demo_login" value="buyer">
                    <button type="submit" class="btn-block" style="background: #ffffff; border: 1px solid #86efac; color: #15803d; padding: 9px 14px; border-radius: 12px; font-size: 12px; font-weight: 700; cursor: pointer; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                        <span>🛒 <strong>Muhammad Aizat</strong> (Direct Buyer)</span>
                        <span style="font-size: 11px; color: #16a34a; font-weight: 800; white-space: nowrap;">Click to Login &rarr;</span>
                    </button>
                </form>

                <form method="POST" action="login.php" style="margin: 0;">
                    <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
                    <input type="hidden" name="demo_login" value="farmer">
                    <button type="submit" class="btn-block" style="background: #ffffff; border: 1px solid #fde68a; color: #92400e; padding: 9px 14px; border-radius: 12px; font-size: 12px; font-weight: 700; cursor: pointer; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                        <span>👨‍🌾 <strong>Pak Cik Azman</strong> (Farmer Portal)</span>
                        <span style="font-size: 11px; color: #d97706; font-weight: 800; white-space: nowrap;">Click to Login &rarr;</span>
                    </button>
                </form>

                <form method="POST" action="login.php" style="margin: 0;">
                    <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
                    <input type="hidden" name="demo_login" value="admin">
                    <button type="submit" class="btn-block" style="background: #ffffff; border: 1px solid #bfdbfe; color: #1e40af; padding: 9px 14px; border-radius: 12px; font-size: 12px; font-weight: 700; cursor: pointer; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                        <span>🏢 <strong>Famox Lunas Admin</strong> (Hub Oversight)</span>
                        <span style="font-size: 11px; color: #2563eb; font-weight: 800; white-space: nowrap;">Click to Login &rarr;</span>
                    </button>
                </form>
            </div>
        </div>

        <div style="position: relative; text-align: center; margin: 20px 0;">
            <hr style="border: 0; border-top: 1px solid #e5e7eb;">
            <span style="position: absolute; top: -10px; left: 50%; transform: translateX(-50%); background: #ffffff; padding: 0 12px; font-size: 11px; color: #9ca3af; text-transform: uppercase;">Or with credentials</span>
        </div>

        <!-- Standard Login Form -->
        <form method="POST" action="login.php" style="display: flex; flex-direction: column; gap: 14px; font-size: 12px;">
            <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">

            <div>
                <label style="font-weight: 700; display: block; margin-bottom: 4px;">Email Address</label>
                <input type="email" name="email" required placeholder="e.g. aizat@aimst.edu.my" class="form-input" style="width: 100%;">
            </div>

            <div>
                <label style="font-weight: 700; display: block; margin-bottom: 4px;">Password</label>
                <input type="password" name="password" required placeholder="••••••••" class="form-input" style="width: 100%;">
            </div>

            <div style="text-align: center; margin-top: 6px;">
                <button type="submit" class="btn-primary" style="padding: 12px 36px; font-size: 13px; width: 100%; max-width: 260px; margin: 0 auto; display: inline-flex; justify-content: center;">
                    Sign In
                </button>
            </div>
        </form>

        <div style="text-align: center; margin-top: 20px; font-size: 12px; color: #6b7280;">
            Don't have an account? <a href="register.php<?= !empty($redirect) ? '?redirect=' . urlencode($redirect) : '' ?>" style="color: #059669; font-weight: 700;">Register Account &rarr;</a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
