<?php
defined("BASEPATH") or exit("No direct script access allowed.");

// Check admin access
if (!isset($_SESSION['user']) || $_SESSION['user']['level'] !== 'Admin') {
    header('Location: ' . base_url('/auth/login'));
    exit;
}

// Load helper functions
require_once BASEPATH.'helpers/voucher_helper.php';
require_once BASEPATH.'helpers/flashsale_helper.php';
require_once BASEPATH.'helpers/support_helper.php';

// Get statistics
$stats = [
    'total_users' => $db->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'],
    'total_transactions' => $db->query("SELECT COUNT(*) as count FROM pembelian_pulsa UNION ALL SELECT COUNT(*) FROM pembelian_sosmed")->fetch_assoc()['count'],
    'total_revenue' => $db->query("SELECT SUM(harga) as total FROM pembelian_pulsa WHERE status = 'Success' UNION ALL SELECT SUM(harga) FROM pembelian_sosmed WHERE status = 'Success'")->fetch_assoc()['total'] ?? 0,
    'pending_deposits' => $db->query("SELECT COUNT(*) as count FROM deposit WHERE status = 'Pending'")->fetch_assoc()['count'],
];

// Get recent activities
$recent_transactions = $db->query("
    (SELECT 'pulsa' as type, id, user, layanan, harga, status, date FROM pembelian_pulsa ORDER BY date DESC LIMIT 5)
    UNION ALL
    (SELECT 'sosmed' as type, id, user, layanan, harga, status, date FROM pembelian_sosmed ORDER BY date DESC LIMIT 5)
    ORDER BY date DESC LIMIT 10
");

// Get support ticket stats
$support_stats = get_support_manager()->getTicketStats();

// Get flash sale stats
$flash_sale_stats = $db->query("
    SELECT 
        COUNT(*) as total_sales,
        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_sales,
        SUM(CASE WHEN status = 'scheduled' THEN 1 ELSE 0 END) as scheduled_sales
    FROM flash_sales
")->fetch_assoc();

// Get voucher stats
$voucher_stats = $db->query("
    SELECT 
        COUNT(*) as total_vouchers,
        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_vouchers,
        SUM(used_count) as total_usage
    FROM vouchers
")->fetch_assoc();

include BASEPATH.'layouts/header_modern.php';
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">Admin Dashboard</h1>
                    <p class="text-muted">Kelola seluruh aspek website topup Anda</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-primary btn-modern" data-bs-toggle="modal" data-bs-target="#quickActionModal">
                        <i class="fas fa-plus"></i> Quick Action
                    </button>
                    <button class="btn btn-outline-secondary btn-modern" onclick="refreshDashboard()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card card-modern border-left-primary">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Users</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($stats['total_users']) ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card card-modern border-left-success">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Revenue</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Rp <?= number_format($stats['total_revenue']) ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card card-modern border-left-info">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Transactions</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($stats['total_transactions']) ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card card-modern border-left-warning">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending Deposits</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($stats['pending_deposits']) ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-hourglass-half fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Feature Management Cards -->
    <div class="row mb-4">
        <!-- Voucher Management -->
        <div class="col-lg-4 mb-4">
            <div class="card card-modern">
                <div class="card-header bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-ticket-alt"></i> Voucher Management
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="h4 font-weight-bold text-primary"><?= $voucher_stats['total_vouchers'] ?></div>
                            <div class="small text-muted">Total</div>
                        </div>
                        <div class="col-4">
                            <div class="h4 font-weight-bold text-success"><?= $voucher_stats['active_vouchers'] ?></div>
                            <div class="small text-muted">Active</div>
                        </div>
                        <div class="col-4">
                            <div class="h4 font-weight-bold text-info"><?= $voucher_stats['total_usage'] ?></div>
                            <div class="small text-muted">Used</div>
                        </div>
                    </div>
                    <hr>
                    <div class="d-grid gap-2">
                        <a href="<?= base_url('/admin/vouchers') ?>" class="btn btn-primary btn-sm">Manage Vouchers</a>
                        <button class="btn btn-outline-primary btn-sm" onclick="createVoucher()">Create New</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Flash Sale Management -->
        <div class="col-lg-4 mb-4">
            <div class="card card-modern">
                <div class="card-header bg-warning text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-bolt"></i> Flash Sale Management
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="h4 font-weight-bold text-warning"><?= $flash_sale_stats['total_sales'] ?></div>
                            <div class="small text-muted">Total</div>
                        </div>
                        <div class="col-4">
                            <div class="h4 font-weight-bold text-success"><?= $flash_sale_stats['active_sales'] ?></div>
                            <div class="small text-muted">Active</div>
                        </div>
                        <div class="col-4">
                            <div class="h4 font-weight-bold text-info"><?= $flash_sale_stats['scheduled_sales'] ?></div>
                            <div class="small text-muted">Scheduled</div>
                        </div>
                    </div>
                    <hr>
                    <div class="d-grid gap-2">
                        <a href="<?= base_url('/admin/flash-sales') ?>" class="btn btn-warning btn-sm">Manage Flash Sales</a>
                        <button class="btn btn-outline-warning btn-sm" onclick="createFlashSale()">Create New</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Support Management -->
        <div class="col-lg-4 mb-4">
            <div class="card card-modern">
                <div class="card-header bg-info text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-headset"></i> Customer Support
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="h4 font-weight-bold text-info"><?= $support_stats['total_tickets'] ?></div>
                            <div class="small text-muted">Total</div>
                        </div>
                        <div class="col-4">
                            <div class="h4 font-weight-bold text-danger"><?= $support_stats['open_tickets'] ?></div>
                            <div class="small text-muted">Open</div>
                        </div>
                        <div class="col-4">
                            <div class="h4 font-weight-bold text-success"><?= $support_stats['resolved_tickets'] ?></div>
                            <div class="small text-muted">Resolved</div>
                        </div>
                    </div>
                    <hr>
                    <div class="d-grid gap-2">
                        <a href="<?= base_url('/admin/support') ?>" class="btn btn-info btn-sm">Manage Tickets</a>
                        <button class="btn btn-outline-info btn-sm" onclick="viewSupportStats()">View Stats</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Website Control Panel -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card card-modern">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-cogs"></i> Website Control Panel
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="card border-left-primary">
                                <div class="card-body py-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="text-xs font-weight-bold text-primary text-uppercase">Users</div>
                                            <div class="small">Manage user accounts</div>
                                        </div>
                                        <a href="<?= base_url('/admin/users') ?>" class="btn btn-primary btn-sm">
                                            <i class="fas fa-users"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="card border-left-success">
                                <div class="card-body py-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="text-xs font-weight-bold text-success text-uppercase">Services</div>
                                            <div class="small">Manage products & prices</div>
                                        </div>
                                        <a href="<?= base_url('/admin/services') ?>" class="btn btn-success btn-sm">
                                            <i class="fas fa-gamepad"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="card border-left-warning">
                                <div class="card-body py-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="text-xs font-weight-bold text-warning text-uppercase">Deposits</div>
                                            <div class="small">Manage user deposits</div>
                                        </div>
                                        <a href="<?= base_url('/admin/deposits') ?>" class="btn btn-warning btn-sm">
                                            <i class="fas fa-wallet"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="card border-left-info">
                                <div class="card-body py-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="text-xs font-weight-bold text-info text-uppercase">Settings</div>
                                            <div class="small">Website configuration</div>
                                        </div>
                                        <a href="<?= base_url('/admin/settings') ?>" class="btn btn-info btn-sm">
                                            <i class="fas fa-cog"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="row">
        <div class="col-12">
            <div class="card card-modern">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history"></i> Recent Transactions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Type</th>
                                    <th>User</th>
                                    <th>Service</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($transaction = $recent_transactions->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $transaction['id'] ?></td>
                                    <td>
                                        <span class="badge bg-<?= $transaction['type'] === 'pulsa' ? 'primary' : 'success' ?>">
                                            <?= strtoupper($transaction['type']) ?>
                                        </span>
                                    </td>
                                    <td><?= $transaction['user'] ?></td>
                                    <td><?= $transaction['layanan'] ?></td>
                                    <td>Rp <?= number_format($transaction['harga']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $transaction['status'] === 'Success' ? 'success' : ($transaction['status'] === 'Pending' ? 'warning' : 'danger') ?>">
                                            <?= $transaction['status'] ?>
                                        </span>
                                    </td>
                                    <td><?= date('d/m/Y H:i', strtotime($transaction['date'])) ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" onclick="viewTransaction('<?= $transaction['type'] ?>', <?= $transaction['id'] ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Action Modal -->
<div class="modal fade" id="quickActionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Quick Actions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="d-grid gap-2">
                    <button class="btn btn-primary" onclick="createVoucher()">
                        <i class="fas fa-ticket-alt"></i> Create Voucher
                    </button>
                    <button class="btn btn-warning" onclick="createFlashSale()">
                        <i class="fas fa-bolt"></i> Create Flash Sale
                    </button>
                    <button class="btn btn-success" onclick="addUser()">
                        <i class="fas fa-user-plus"></i> Add User
                    </button>
                    <button class="btn btn-info" onclick="sendNotification()">
                        <i class="fas fa-bell"></i> Send Notification
                    </button>
                    <button class="btn btn-secondary" onclick="backupDatabase()">
                        <i class="fas fa-database"></i> Backup Database
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function refreshDashboard() {
    location.reload();
}

function createVoucher() {
    window.location.href = '<?= base_url('/admin/vouchers/create') ?>';
}

function createFlashSale() {
    window.location.href = '<?= base_url('/admin/flash-sales/create') ?>';
}

function addUser() {
    window.location.href = '<?= base_url('/admin/users/create') ?>';
}

function sendNotification() {
    window.location.href = '<?= base_url('/admin/notifications/create') ?>';
}

function backupDatabase() {
    if (confirm('Are you sure you want to backup the database?')) {
        window.location.href = '<?= base_url('/admin/backup/database') ?>';
    }
}

function viewTransaction(type, id) {
    window.location.href = `<?= base_url('/admin/transactions/') ?>${type}/${id}`;
}

function viewSupportStats() {
    window.location.href = '<?= base_url('/admin/support/statistics') ?>';
}
</script>

<?php include BASEPATH.'layouts/footer_modern.php'; ?>
