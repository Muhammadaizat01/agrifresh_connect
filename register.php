<?php
require_once __DIR__ . '/config/db.php';
$pageTitle = "Register Account | AgriFresh Connect";
$activePage = 'register';

$redirect = trim($_GET['redirect'] ?? $_POST['redirect'] ?? '');
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $roleType = trim($_POST['role_type'] ?? 'buyer');

    $farmName = trim($_POST['farm_name'] ?? '');
    $district = trim($_POST['district'] ?? 'Lunas / Kulim');
    $cert = trim($_POST['cert'] ?? 'MyGAP Certified');

    if (!empty($name) && !empty($email) && !empty($password)) {
        $chk = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $chk->execute([$email]);
        if ($chk->fetch()) {
            $error = "This email is already registered. Please sign in instead.";
        } else {
            $roleId = ($roleType === 'farmer') ? 2 : 3;
            $roleSlug = ($roleType === 'farmer') ? 'farmer' : 'buyer';
            $roleName = ($roleType === 'farmer') ? 'Farmer' : 'Buyer';

            $ins = $pdo->prepare("INSERT INTO users (role_id, name, email, password, phone, address, is_active) VALUES (?, ?, ?, ?, ?, ?, 1)");
            $ins->execute([$roleId, $name, $email, password_hash($password, PASSWORD_DEFAULT), $phone, $address]);
            $userId = $pdo->lastInsertId();

            if ($roleType === 'farmer') {
                $fIns = $pdo->prepare("INSERT INTO farmers (user_id, farm_name, kedah_district, farm_location_details, farming_certification, cert_number, experience_years, famox_tier, avatar, quote, is_approved) 
                                       VALUES (?, ?, ?, ?, ?, ?, 5, 'Verified Supplier Partner', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=300', 'Excited to deliver fresh crops directly to our Kedah community.', 1)");
                $certNum = 'MYGAP-KDH-' . date('Y') . '-' . rand(1000, 9999);
                $fIns->execute([$userId, $farmName ?: ($name . ' Farm'), $district, $address, $cert, $certNum]);
            } else {
                $bIns = $pdo->prepare("INSERT INTO buyers (user_id, company_name, buyer_type) VALUES (?, ?, 'Direct Consumer')");
                $bIns->execute([$userId, $name]);
            }

            $_SESSION['user'] = [
                'id' => $userId,
                'name' => $name,
                'email' => $email,
                'role_id' => $roleId,
                'role_name' => $roleName,
                'role_slug' => $roleSlug,
                'phone' => $phone,
                'address' => $address
            ];

            $log = $pdo->prepare("INSERT INTO activity_logs (user_name, action, description, ip_address) VALUES (?, 'New Account Registered', ?, ?)");
            $log->execute([$name, "Registered as " . $roleName, $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1']);

            if (!empty($redirect)) {
                header("Location: " . $redirect);
            } elseif ($roleSlug === 'farmer') {
                header("Location: farmer_dashboard.php");
            } else {
                header("Location: index.php");
            }
            exit;
        }
    } else {
        $error = "Please fill in all required fields.";
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="auth-page-wrapper">
    <div class="auth-card-glass" style="max-width: 580px;">
        <div style="text-align: center; margin-bottom: 24px;">
            <div style="width: 60px; height: 60px; border-radius: 50%; overflow: hidden; margin: 0 auto 12px; border: 2px solid #10b981; box-shadow: 0 4px 14px rgba(16,185,129,0.25);">
                <img src="assets/img/agrifresh_logo.png" alt="AgriFresh Logo" style="width: 100%; height: 100%; object-fit: cover;">
            </div>
            <h1 style="font-size: 24px; font-weight: 900; color: #111827;">Create Your Account</h1>
            <p style="font-size: 13px; color: #6b7280; margin-top: 4px;">
                <?= $redirect === 'checkout.php' ? '🔒 Register to complete your fresh produce checkout' : 'Join the Famox Enterprise digital supplier marketplace' ?>
            </p>
        </div>

        <?php if ($error): ?>
            <div style="background: #fee2e2; color: #991b1b; padding: 12px 16px; border-radius: 14px; font-size: 12px; margin-bottom: 20px; border: 1px solid #fca5a5; text-align: center;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="register.php" style="display: flex; flex-direction: column; gap: 14px; font-size: 12px;">
            <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">

            <div>
                <label style="font-weight: 700; display: block; margin-bottom: 6px;">Select Your Account Role</label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <label style="display: flex; align-items: center; gap: 8px; padding: 10px 14px; border: 1px solid #d1fae5; background: #f0fdf4; border-radius: 12px; cursor: pointer;">
                        <input type="radio" name="role_type" value="buyer" checked onchange="toggleFarmerFields(false)">
                        <div>
                            <strong style="color: #065f46;">🛒 Buyer</strong>
                            <div style="font-size: 10px; color: #6b7280;">Purchase Fresh Produce</div>
                        </div>
                    </label>

                    <label style="display: flex; align-items: center; gap: 8px; padding: 10px 14px; border: 1px solid #fde68a; background: #fffbeb; border-radius: 12px; cursor: pointer;">
                        <input type="radio" name="role_type" value="farmer" onchange="toggleFarmerFields(true)">
                        <div>
                            <strong style="color: #92400e;">👨‍🌾 Smallholder Farmer</strong>
                            <div style="font-size: 10px; color: #6b7280;">Sell Crops & Print QR</div>
                        </div>
                    </label>
                </div>
            </div>

            <div>
                <label style="font-weight: 700; display: block; margin-bottom: 4px;">Full Name</label>
                <input type="text" name="name" required placeholder="e.g. Muhammad Aizat" class="form-input" style="width: 100%;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div>
                    <label style="font-weight: 700; display: block; margin-bottom: 4px;">Email Address</label>
                    <input type="email" name="email" required placeholder="e.g. aizat@example.my" class="form-input" style="width: 100%;">
                </div>
                <div>
                    <label style="font-weight: 700; display: block; margin-bottom: 4px;">Password</label>
                    <input type="password" name="password" required placeholder="••••••••" class="form-input" style="width: 100%;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div>
                    <label style="font-weight: 700; display: block; margin-bottom: 4px;">Phone Number</label>
                    <input type="text" name="phone" placeholder="+60 19-334 8812" class="form-input" style="width: 100%;">
                </div>
                <div>
                    <label style="font-weight: 700; display: block; margin-bottom: 4px;">Kedah District / Town</label>
                    <input type="text" name="district" placeholder="Lunas / Kulim / Baling" class="form-input" style="width: 100%;">
                </div>
            </div>

            <div>
                <label style="font-weight: 700; display: block; margin-bottom: 4px;">Full Address</label>
                <textarea name="address" rows="2" placeholder="Street address in Kedah" class="form-input" style="width: 100%;"></textarea>
            </div>

            <!-- Farmer Specific Fields -->
            <div id="farmerExtraFields" style="display: none; background: #fffbeb; border: 1px solid #fde68a; border-radius: 16px; padding: 14px;">
                <div style="font-weight: 800; color: #92400e; margin-bottom: 8px;">👨‍🌾 Farm Details (Famox Supplier Network)</div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <div>
                        <label style="font-weight: 700; display: block; margin-bottom: 4px;">Farm Name</label>
                        <input type="text" name="farm_name" placeholder="Ladang Hijau Makmur" class="form-input" style="width: 100%;">
                    </div>
                    <div>
                        <label style="font-weight: 700; display: block; margin-bottom: 4px;">Certification</label>
                        <select name="cert" class="form-input" style="width: 100%;">
                            <option value="MyGAP Certified">MyGAP Certified</option>
                            <option value="Organic Green Label">Organic Green Label</option>
                            <option value="In-Conversion Standard">In-Conversion Standard</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Centered Register Button -->
            <div style="text-align: center; margin-top: 10px;">
                <button type="submit" class="btn-emerald" style="padding: 13px 40px; font-size: 14px; width: 100%; max-width: 280px; margin: 0 auto; display: inline-flex; justify-content: center; box-shadow: 0 4px 14px rgba(5,150,105,0.3);">
                    Register Account
                </button>
            </div>
        </form>

        <div style="text-align: center; margin-top: 20px; font-size: 12px; color: #6b7280;">
            Already have an account? <a href="login.php<?= !empty($redirect) ? '?redirect=' . urlencode($redirect) : '' ?>" style="color: #059669; font-weight: 700;">Sign In &rarr;</a>
        </div>
    </div>
</div>

<script>
function toggleFarmerFields(isFarmer) {
    document.getElementById('farmerExtraFields').style.display = isFarmer ? 'block' : 'none';
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
