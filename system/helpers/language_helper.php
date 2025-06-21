<?php
defined("BASEPATH") or exit("No direct script access allowed.");

/**
 * Multi-Language Helper untuk Website Topup
 * Sistem bahasa yang mendukung Indonesia dan Inggris
 */

class LanguageManager {
    private $current_language = 'id';
    private $languages = [];
    private $translations = [];
    
    public function __construct() {
        $this->loadLanguages();
        $this->setLanguage($this->detectLanguage());
    }
    
    /**
     * Memuat daftar bahasa yang tersedia
     */
    private function loadLanguages() {
        $this->languages = [
            'id' => [
                'name' => 'Bahasa Indonesia',
                'code' => 'id',
                'flag' => '🇮🇩',
                'direction' => 'ltr'
            ],
            'en' => [
                'name' => 'English',
                'code' => 'en', 
                'flag' => '🇺🇸',
                'direction' => 'ltr'
            ]
        ];
    }
    
    /**
     * Mendeteksi bahasa berdasarkan session atau browser
     */
    private function detectLanguage() {
        // Cek session terlebih dahulu
        if (isset($_SESSION['language'])) {
            return $_SESSION['language'];
        }
        
        // Cek cookie
        if (isset($_COOKIE['language'])) {
            return $_COOKIE['language'];
        }
        
        // Deteksi dari browser
        $browser_lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'id', 0, 2);
        return array_key_exists($browser_lang, $this->languages) ? $browser_lang : 'id';
    }
    
    /**
     * Mengatur bahasa aktif
     */
    public function setLanguage($lang_code) {
        if (array_key_exists($lang_code, $this->languages)) {
            $this->current_language = $lang_code;
            $_SESSION['language'] = $lang_code;
            setcookie('language', $lang_code, time() + (86400 * 30), '/'); // 30 hari
            $this->loadTranslations();
        }
    }
    
    /**
     * Memuat terjemahan untuk bahasa aktif
     */
    private function loadTranslations() {
        $this->translations = $this->getTranslations($this->current_language);
    }
    
    /**
     * Mendapatkan terjemahan berdasarkan key
     */
    public function get($key, $params = []) {
        $translation = $this->translations[$key] ?? $key;
        
        // Replace parameters jika ada
        if (!empty($params)) {
            foreach ($params as $param_key => $param_value) {
                $translation = str_replace('{' . $param_key . '}', $param_value, $translation);
            }
        }
        
        return $translation;
    }
    
    /**
     * Mendapatkan bahasa saat ini
     */
    public function getCurrentLanguage() {
        return $this->current_language;
    }
    
    /**
     * Mendapatkan daftar bahasa yang tersedia
     */
    public function getAvailableLanguages() {
        return $this->languages;
    }
    
    /**
     * Database terjemahan
     */
    private function getTranslations($lang) {
        $translations = [
            'id' => [
                // Header & Navigation
                'home' => 'Beranda',
                'services' => 'Layanan',
                'price_list' => 'Daftar Harga',
                'check_transaction' => 'Cek Transaksi',
                'deposit' => 'Deposit',
                'voucher' => 'Voucher',
                'flash_sale' => 'Flash Sale',
                'support' => 'Customer Service',
                'login' => 'Masuk',
                'register' => 'Daftar',
                'logout' => 'Keluar',
                'profile' => 'Profil',
                'admin_panel' => 'Panel Admin',
                
                // Dashboard
                'dashboard' => 'Dashboard',
                'welcome_back' => 'Selamat Datang Kembali',
                'balance' => 'Saldo',
                'total_transactions' => 'Total Transaksi',
                'pending_orders' => 'Pesanan Pending',
                'recent_activities' => 'Aktivitas Terbaru',
                
                // Voucher System
                'voucher_code' => 'Kode Voucher',
                'check_voucher' => 'Cek Voucher',
                'apply_voucher' => 'Gunakan Voucher',
                'voucher_valid' => 'Voucher Valid!',
                'voucher_invalid' => 'Voucher Tidak Valid',
                'discount' => 'Diskon',
                'minimum_purchase' => 'Minimal Pembelian',
                'voucher_expired' => 'Voucher Sudah Kadaluarsa',
                'voucher_used' => 'Voucher Sudah Digunakan',
                'voucher_limit_reached' => 'Batas Penggunaan Voucher Tercapai',
                
                // Flash Sale
                'flash_sale_active' => 'Flash Sale Aktif!',
                'time_remaining' => 'Waktu Tersisa',
                'original_price' => 'Harga Normal',
                'sale_price' => 'Harga Diskon',
                'save_amount' => 'Hemat',
                'flash_sale_ended' => 'Flash Sale Berakhir',
                'limited_stock' => 'Stok Terbatas',
                
                // Customer Service
                'create_ticket' => 'Buat Tiket',
                'ticket_subject' => 'Subjek Tiket',
                'ticket_message' => 'Pesan',
                'ticket_category' => 'Kategori',
                'ticket_priority' => 'Prioritas',
                'ticket_status' => 'Status',
                'ticket_created' => 'Tiket Berhasil Dibuat',
                'ticket_id' => 'ID Tiket',
                'reply_ticket' => 'Balas Tiket',
                'close_ticket' => 'Tutup Tiket',
                
                // Categories
                'general' => 'Umum',
                'technical' => 'Teknis',
                'billing' => 'Pembayaran',
                'complaint' => 'Keluhan',
                'suggestion' => 'Saran',
                
                // Priority Levels
                'low' => 'Rendah',
                'medium' => 'Sedang',
                'high' => 'Tinggi',
                'urgent' => 'Mendesak',
                
                // Status
                'open' => 'Terbuka',
                'in_progress' => 'Dalam Proses',
                'waiting_customer' => 'Menunggu Pelanggan',
                'resolved' => 'Selesai',
                'closed' => 'Ditutup',
                'pending' => 'Pending',
                'success' => 'Berhasil',
                'failed' => 'Gagal',
                'cancelled' => 'Dibatalkan',
                
                // Forms
                'submit' => 'Kirim',
                'cancel' => 'Batal',
                'save' => 'Simpan',
                'edit' => 'Edit',
                'delete' => 'Hapus',
                'search' => 'Cari',
                'filter' => 'Filter',
                'reset' => 'Reset',
                'refresh' => 'Refresh',
                'back' => 'Kembali',
                'next' => 'Selanjutnya',
                'previous' => 'Sebelumnya',
                
                // Messages
                'success_message' => 'Operasi berhasil dilakukan',
                'error_message' => 'Terjadi kesalahan, silakan coba lagi',
                'validation_error' => 'Data yang dimasukkan tidak valid',
                'unauthorized' => 'Anda tidak memiliki akses',
                'not_found' => 'Data tidak ditemukan',
                'server_error' => 'Terjadi kesalahan server',
                'maintenance' => 'Website sedang dalam pemeliharaan',
                
                // Admin Panel
                'admin_dashboard' => 'Dashboard Admin',
                'user_management' => 'Manajemen User',
                'transaction_management' => 'Manajemen Transaksi',
                'voucher_management' => 'Manajemen Voucher',
                'flash_sale_management' => 'Manajemen Flash Sale',
                'support_management' => 'Manajemen Customer Service',
                'system_settings' => 'Pengaturan Sistem',
                'reports' => 'Laporan',
                'analytics' => 'Analitik',
                
                // Time & Date
                'today' => 'Hari Ini',
                'yesterday' => 'Kemarin',
                'this_week' => 'Minggu Ini',
                'this_month' => 'Bulan Ini',
                'last_month' => 'Bulan Lalu',
                'days' => 'hari',
                'hours' => 'jam',
                'minutes' => 'menit',
                'seconds' => 'detik',
                
                // Currency & Numbers
                'currency_symbol' => 'Rp',
                'thousand_separator' => '.',
                'decimal_separator' => ',',
                
                // Notifications
                'new_notification' => 'Notifikasi Baru',
                'mark_as_read' => 'Tandai Sudah Dibaca',
                'no_notifications' => 'Tidak Ada Notifikasi',
                'notification_settings' => 'Pengaturan Notifikasi',
                
                // Footer
                'about_us' => 'Tentang Kami',
                'contact_us' => 'Hubungi Kami',
                'terms_of_service' => 'Syarat Layanan',
                'privacy_policy' => 'Kebijakan Privasi',
                'faq' => 'FAQ',
                'help' => 'Bantuan',
                'copyright' => 'Hak Cipta',
                'all_rights_reserved' => 'Semua Hak Dilindungi',
            ],
            
            'en' => [
                // Header & Navigation
                'home' => 'Home',
                'services' => 'Services',
                'price_list' => 'Price List',
                'check_transaction' => 'Check Transaction',
                'deposit' => 'Deposit',
                'voucher' => 'Voucher',
                'flash_sale' => 'Flash Sale',
                'support' => 'Customer Service',
                'login' => 'Login',
                'register' => 'Register',
                'logout' => 'Logout',
                'profile' => 'Profile',
                'admin_panel' => 'Admin Panel',
                
                // Dashboard
                'dashboard' => 'Dashboard',
                'welcome_back' => 'Welcome Back',
                'balance' => 'Balance',
                'total_transactions' => 'Total Transactions',
                'pending_orders' => 'Pending Orders',
                'recent_activities' => 'Recent Activities',
                
                // Voucher System
                'voucher_code' => 'Voucher Code',
                'check_voucher' => 'Check Voucher',
                'apply_voucher' => 'Apply Voucher',
                'voucher_valid' => 'Voucher Valid!',
                'voucher_invalid' => 'Invalid Voucher',
                'discount' => 'Discount',
                'minimum_purchase' => 'Minimum Purchase',
                'voucher_expired' => 'Voucher Expired',
                'voucher_used' => 'Voucher Already Used',
                'voucher_limit_reached' => 'Voucher Usage Limit Reached',
                
                // Flash Sale
                'flash_sale_active' => 'Flash Sale Active!',
                'time_remaining' => 'Time Remaining',
                'original_price' => 'Original Price',
                'sale_price' => 'Sale Price',
                'save_amount' => 'Save',
                'flash_sale_ended' => 'Flash Sale Ended',
                'limited_stock' => 'Limited Stock',
                
                // Customer Service
                'create_ticket' => 'Create Ticket',
                'ticket_subject' => 'Ticket Subject',
                'ticket_message' => 'Message',
                'ticket_category' => 'Category',
                'ticket_priority' => 'Priority',
                'ticket_status' => 'Status',
                'ticket_created' => 'Ticket Created Successfully',
                'ticket_id' => 'Ticket ID',
                'reply_ticket' => 'Reply Ticket',
                'close_ticket' => 'Close Ticket',
                
                // Categories
                'general' => 'General',
                'technical' => 'Technical',
                'billing' => 'Billing',
                'complaint' => 'Complaint',
                'suggestion' => 'Suggestion',
                
                // Priority Levels
                'low' => 'Low',
                'medium' => 'Medium',
                'high' => 'High',
                'urgent' => 'Urgent',
                
                // Status
                'open' => 'Open',
                'in_progress' => 'In Progress',
                'waiting_customer' => 'Waiting Customer',
                'resolved' => 'Resolved',
                'closed' => 'Closed',
                'pending' => 'Pending',
                'success' => 'Success',
                'failed' => 'Failed',
                'cancelled' => 'Cancelled',
                
                // Forms
                'submit' => 'Submit',
                'cancel' => 'Cancel',
                'save' => 'Save',
                'edit' => 'Edit',
                'delete' => 'Delete',
                'search' => 'Search',
                'filter' => 'Filter',
                'reset' => 'Reset',
                'refresh' => 'Refresh',
                'back' => 'Back',
                'next' => 'Next',
                'previous' => 'Previous',
                
                // Messages
                'success_message' => 'Operation completed successfully',
                'error_message' => 'An error occurred, please try again',
                'validation_error' => 'Invalid data entered',
                'unauthorized' => 'You do not have access',
                'not_found' => 'Data not found',
                'server_error' => 'Server error occurred',
                'maintenance' => 'Website under maintenance',
                
                // Admin Panel
                'admin_dashboard' => 'Admin Dashboard',
                'user_management' => 'User Management',
                'transaction_management' => 'Transaction Management',
                'voucher_management' => 'Voucher Management',
                'flash_sale_management' => 'Flash Sale Management',
                'support_management' => 'Customer Service Management',
                'system_settings' => 'System Settings',
                'reports' => 'Reports',
                'analytics' => 'Analytics',
                
                // Time & Date
                'today' => 'Today',
                'yesterday' => 'Yesterday',
                'this_week' => 'This Week',
                'this_month' => 'This Month',
                'last_month' => 'Last Month',
                'days' => 'days',
                'hours' => 'hours',
                'minutes' => 'minutes',
                'seconds' => 'seconds',
                
                // Currency & Numbers
                'currency_symbol' => '$',
                'thousand_separator' => ',',
                'decimal_separator' => '.',
                
                // Notifications
                'new_notification' => 'New Notification',
                'mark_as_read' => 'Mark as Read',
                'no_notifications' => 'No Notifications',
                'notification_settings' => 'Notification Settings',
                
                // Footer
                'about_us' => 'About Us',
                'contact_us' => 'Contact Us',
                'terms_of_service' => 'Terms of Service',
                'privacy_policy' => 'Privacy Policy',
                'faq' => 'FAQ',
                'help' => 'Help',
                'copyright' => 'Copyright',
                'all_rights_reserved' => 'All Rights Reserved',
            ]
        ];
        
        return $translations[$lang] ?? $translations['id'];
    }
}

// Global language functions
function get_language_manager() {
    static $language_manager = null;
    if ($language_manager === null) {
        $language_manager = new LanguageManager();
    }
    return $language_manager;
}

function lang($key, $params = []) {
    return get_language_manager()->get($key, $params);
}

function set_language($lang_code) {
    get_language_manager()->setLanguage($lang_code);
}

function get_current_language() {
    return get_language_manager()->getCurrentLanguage();
}

function get_available_languages() {
    return get_language_manager()->getAvailableLanguages();
}

// Format currency berdasarkan bahasa
function format_currency($amount, $currency = null) {
    $lang = get_current_language();
    
    if ($lang === 'id') {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    } else {
        return '$' . number_format($amount, 2, '.', ',');
    }
}

// Format tanggal berdasarkan bahasa
function format_date($date, $format = null) {
    $lang = get_current_language();
    $timestamp = is_string($date) ? strtotime($date) : $date;
    
    if ($lang === 'id') {
        $months = [
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
        $days = [
            'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
        ];
        
        if ($format === null) {
            $format = 'd F Y H:i';
        }
        
        $formatted = date($format, $timestamp);
        $formatted = str_replace(array_keys($months), array_values($months), $formatted);
        $formatted = str_replace(array_keys($days), array_values($days), $formatted);
        
        return $formatted;
    } else {
        return date($format ?: 'M d, Y H:i', $timestamp);
    }
}
?>
