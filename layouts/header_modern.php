<?php defined("BASEPATH") or exit("No direct script access allowed."); ?>
<?php
$start_time = microtime(TRUE); 
if (isset($_SESSION['user'])) {
    $check_notifications = $db->query("SELECT * FROM notifications WHERE (user_id = '{$_SESSION['user']['id']}' OR is_global = 1) AND (expires_at IS NULL OR expires_at > NOW()) ORDER BY created_at DESC LIMIT 5");
    $unread_notifications = $db->query("SELECT COUNT(*) as count FROM notifications WHERE (user_id = '{$_SESSION['user']['id']}' OR is_global = 1) AND is_read = 0 AND (expires_at IS NULL OR expires_at > NOW())")->fetch_assoc()['count'];
} 

$check_reCapcha = $db->query("SELECT * FROM reCapcha WHERE id = '1'");
$data_reCapcha = $check_reCapcha->fetch_assoc();

// Load helper functions
require_once BASEPATH.'helpers/voucher_helper.php';
require_once BASEPATH.'helpers/flashsale_helper.php';
require_once BASEPATH.'helpers/support_helper.php';

// Get active flash sales for header notification
$active_flash_sales = get_active_flash_sales();

function level($s) {
    if ($s === "Admin") {
        return 'Admin <i class="fas fa-crown text-warning"></i>';
    } else if ($s === "Reseller") {
        return 'Reseller <i class="fas fa-star text-success"></i>';
    } else if ($s === "Premium") {
        return 'Premium <i class="fas fa-gem text-info"></i>';
    } else if ($s === "H2H Special") {
        return 'H2H Special <i class="fas fa-rocket text-primary"></i>';
    } else if ($s === "Member") {
        return 'Member <i class="fas fa-user text-secondary"></i>';
    } else {
        return 'Lock <i class="fas fa-lock text-danger"></i>';
    }
}  
?>
<!DOCTYPE html>
<html class="loading" lang="id" data-textdirection="ltr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta name="description" content="<?= config('web', 'description') ?>">
    <meta name="keywords" content="<?= $data_reCapcha['keyworld']; ?>">
    <meta name="author" content="<?= config('web', 'author') ?>">
    <meta name="robots" content="index, follow">
    <title><?= config('web', 'title_web') ?></title>
    
    <link rel="shortcut icon" type="image/x-icon" href="<?= opt_get('aWNvbi13ZWI=') ?>">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #6366f1;
            --secondary-color: #8b5cf6;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --info-color: #06b6d4;
            --dark-color: #1f2937;
            --light-color: #f8fafc;
            --border-color: #e5e7eb;
            --text-muted: #6b7280;
            --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        
        * {
            font-family: 'Inter', sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .navbar-modern {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border-color);
            box-shadow: var(--shadow);
        }
        
        .sidebar-modern {
            background: white;
            box-shadow: var(--shadow-lg);
            border-radius: 0 15px 15px 0;
            min-height: calc(100vh - 76px);
        }
        
        .nav-link-modern {
            color: var(--text-muted);
            padding: 12px 20px;
            border-radius: 8px;
            margin: 2px 10px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .nav-link-modern:hover {
            background: var(--primary-color);
            color: white;
            transform: translateX(5px);
        }
        
        .nav-link-modern.active {
            background: var(--primary-color);
            color: white;
        }
        
        .flash-sale-banner {
            background: linear-gradient(45deg, #ff6b6b, #feca57);
            color: white;
            padding: 8px 0;
            text-align: center;
            font-weight: 600;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.8; }
            100% { opacity: 1; }
        }
        
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--danger-color);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .card-modern {
            border: none;
            border-radius: 15px;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
        }
        
        .card-modern:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }
        
        .btn-modern {
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-primary-modern {
            background: var(--primary-color);
            border: none;
            color: white;
        }
        
        .btn-primary-modern:hover {
            background: #5856eb;
            transform: translateY(-2px);
        }
        
        .content-wrapper {
            padding: 20px;
            margin-left: 280px;
            transition: margin-left 0.3s ease;
        }
        
        @media (max-width: 768px) {
            .content-wrapper {
                margin-left: 0;
            }
            .sidebar-modern {
                transform: translateX(-100%);
                position: fixed;
                z-index: 1000;
                width: 280px;
            }
            .sidebar-modern.show {
                transform: translateX(0);
            }
        }
        
        .voucher-input {
            border: 2px dashed var(--border-color);
            border-radius: 8px;
            padding: 15px;
            background: var(--light-color);
        }
        
        .flash-sale-timer {
            background: var(--danger-color);
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .support-chat-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 60px;
            height: 60px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 50%;
            box-shadow: var(--shadow-lg);
            z-index: 1000;
            transition: all 0.3s ease;
        }
        
        .support-chat-btn:hover {
            transform: scale(1.1);
            background: #5856eb;
        }
    </style>
</head>
<body>
    <!-- Flash Sale Banner -->
    <?php if (!empty($active_flash_sales)): ?>
    <div class="flash-sale-banner">
        <i class="fas fa-bolt"></i> FLASH SALE AKTIF! 
        <?php foreach($active_flash_sales as $sale): ?>
            <?php if($sale['current_status'] === 'active'): ?>
                <?= $sale['title'] ?> - Diskon hingga <?= $sale['discount_value'] ?><?= $sale['discount_type'] === 'percentage' ? '%' : 'K' ?>!
            <?php endif; ?>
        <?php endforeach; ?>
        <i class="fas fa-fire"></i>
    </div>
    <?php endif; ?>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-modern fixed-top">
        <div class="container-fluid">
            <button class="btn d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar">
                <i class="fas fa-bars"></i>
            </button>
            
            <a class="navbar-brand fw-bold text-primary" href="<?= base_url() ?>">
                <i class="fas fa-gamepad me-2"></i>
                <?= config('web', 'title') ?>
            </a>
            
            <div class="navbar-nav ms-auto d-flex flex-row align-items-center gap-3">
                <!-- Voucher Quick Access -->
                <div class="dropdown">
                    <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-ticket-alt"></i> Voucher
                    </button>
                    <div class="dropdown-menu p-3" style="min-width: 300px;">
                        <h6 class="dropdown-header">Gunakan Voucher</h6>
                        <div class="voucher-input">
                            <input type="text" class="form-control" placeholder="Masukkan kode voucher" id="voucher-code">
                            <button class="btn btn-primary btn-sm mt-2 w-100" onclick="checkVoucher()">
                                <i class="fas fa-check"></i> Cek Voucher
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Notifications -->
                <?php if (isset($_SESSION['user'])): ?>
                <div class="dropdown">
                    <button class="btn btn-outline-secondary btn-sm position-relative" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-bell"></i>
                        <?php if ($unread_notifications > 0): ?>
                        <span class="notification-badge"><?= $unread_notifications ?></span>
                        <?php endif; ?>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end" style="min-width: 350px;">
                        <h6 class="dropdown-header">Notifikasi</h6>
                        <?php while($notif = $check_notifications->fetch_assoc()): ?>
                        <div class="dropdown-item">
                            <div class="d-flex">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1"><?= $notif['title'] ?></h6>
                                    <p class="mb-1 text-muted small"><?= $notif['message'] ?></p>
                                    <small class="text-muted"><?= date('d M Y H:i', strtotime($notif['created_at'])) ?></small>
                                </div>
                                <?php if (!$notif['is_read']): ?>
                                <span class="badge bg-primary">Baru</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="dropdown-divider"></div>
                        <?php endwhile; ?>
                        <a class="dropdown-item text-center" href="<?= base_url('/notifications') ?>">Lihat Semua</a>
                    </div>
                </div>
                
                <!-- User Menu -->
                <div class="dropdown">
                    <button class="btn btn-outline-dark btn-sm" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle"></i> <?= $_SESSION['user']['username'] ?>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <div class="dropdown-header">
                            <div><?= level($_SESSION['user']['level']) ?></div>
                            <small class="text-muted">Saldo: Rp <?= number_format($_SESSION['user']['saldo'], 0, ',', '.') ?></small>
                        </div>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="<?= base_url('/account/profile') ?>">
                            <i class="fas fa-user me-2"></i> Profile
                        </a>
                        <a class="dropdown-item" href="<?= base_url('/account/mutasi_saldo') ?>">
                            <i class="fas fa-history me-2"></i> Riwayat Transaksi
                        </a>
                        <a class="dropdown-item" href="<?= base_url('/support') ?>">
                            <i class="fas fa-headset me-2"></i> Customer Service
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item text-danger" href="<?= base_url('/account/logout') ?>">
                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <div class="offcanvas-lg offcanvas-start sidebar-modern" tabindex="-1" id="sidebar">
        <div class="offcanvas-header d-lg-none">
            <h5 class="offcanvas-title">Menu</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        
        <div class="offcanvas-body p-0">
            <div class="p-3">
                <h6 class="text-muted text-uppercase small fw-bold mb-3">Dashboard</h6>
                
                <?php if ($_SESSION['user']['level'] == 'Admin'): ?>
                <a class="nav-link-modern <?= (uri() == '/admin') ? 'active':'' ?>" href="<?= base_url('/admin') ?>">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Admin Panel</span>
                </a>
                <?php endif; ?>
                
                <a class="nav-link-modern <?= (uri() == '/id') ? 'active':'' ?>" href="<?= base_url('/id') ?>">
                    <i class="fas fa-home"></i>
                    <span>Beranda</span>
                </a>
                
                <a class="nav-link-modern <?= (uri() == '/id/check') ? 'active':'' ?>" href="<?= base_url('/id/check') ?>">
                    <i class="fas fa-search"></i>
                    <span>Cek Transaksi</span>
                </a>
                
                <a class="nav-link-modern <?= (uri() == '/page/price_list') ? 'active':'' ?>" href="<?= base_url('/page/price_list') ?>">
                    <i class="fas fa-tags"></i>
                    <span>Daftar Harga</span>
                </a>
                
                <!-- Flash Sale Menu -->
                <a class="nav-link-modern <?= (uri() == '/flash-sale') ? 'active':'' ?>" href="<?= base_url('/flash-sale') ?>">
                    <i class="fas fa-bolt text-warning"></i>
                    <span>Flash Sale</span>
                    <?php if (!empty($active_flash_sales)): ?>
                    <span class="badge bg-danger ms-auto">HOT</span>
                    <?php endif; ?>
                </a>
            </div>
            
            <div class="p-3 border-top">
                <h6 class="text-muted text-uppercase small fw-bold mb-3">Layanan</h6>
                
                <a class="nav-link-modern" href="<?= base_url('/deposit/new') ?>">
                    <i class="fas fa-wallet text-success"></i>
                    <span>Deposit</span>
                </a>
                
                <a class="nav-link-modern" href="<?= base_url('/voucher') ?>">
                    <i class="fas fa-ticket-alt text-info"></i>
                    <span>Voucher Saya</span>
                </a>
                
                <a class="nav-link-modern" href="<?= base_url('/support') ?>">
                    <i class="fas fa-headset text-primary"></i>
                    <span>Customer Service</span>
                </a>
            </div>
            
            <div class="p-3 border-top">
                <h6 class="text-muted text-uppercase small fw-bold mb-3">Bantuan</h6>
                
                <a class="nav-link-modern" href="<?= base_url('/page/terms') ?>">
                    <i class="fas fa-file-contract"></i>
                    <span>Ketentuan</span>
                </a>
                
                <a class="nav-link-modern" href="<?= base_url('/page/faq') ?>">
                    <i class="fas fa-question-circle"></i>
                    <span>FAQ</span>
                </a>
                
                <a class="nav-link-modern" href="<?= base_url('/page/contact_us') ?>">
                    <i class="fas fa-envelope"></i>
                    <span>Kontak Kami</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="content-wrapper">
        <?php if (isset($_SESSION['alert']) && $alert = $_SESSION['alert']) { ?>
        <div class="alert alert-<?= $alert[0] ?> alert-dismissible fade show card-modern" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-<?= $alert[0] === 'success' ? 'check-circle' : ($alert[0] === 'danger' ? 'exclamation-triangle' : 'info-circle') ?> me-2"></i>
                <div>
                    <strong><?= $alert[1] ?></strong>
                    <div><?= $alert[2] ?></div>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['alert']); } ?>
