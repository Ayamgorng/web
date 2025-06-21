# Panduan Fitur Multi-Bahasa Website Topup

## 🌍 **Fitur Multi-Bahasa yang Telah Ditambahkan**

### **Bahasa yang Didukung:**
- 🇮🇩 **Bahasa Indonesia** (Default)
- 🇺🇸 **English**

---

## 📋 **Fitur yang Telah Diimplementasikan**

### 1. **Language Helper System**
**File:** `system/helpers/language_helper.php`

**Fitur Utama:**
- ✅ Deteksi bahasa otomatis dari browser
- ✅ Penyimpanan preferensi bahasa di session dan cookie
- ✅ Database terjemahan lengkap (200+ kata/frasa)
- ✅ Format mata uang dan tanggal sesuai bahasa
- ✅ Fungsi helper yang mudah digunakan

**Cara Penggunaan:**
```php
// Mendapatkan terjemahan
echo lang('home'); // Output: "Beranda" (ID) atau "Home" (EN)

// Dengan parameter
echo lang('welcome_user', ['name' => 'John']); // "Selamat datang, John!"

// Mengubah bahasa
set_language('en'); // Ubah ke bahasa Inggris

// Format mata uang
echo format_currency(50000); // "Rp 50.000" (ID) atau "$50.00" (EN)

// Format tanggal
echo format_date(time()); // "15 Januari 2024" (ID) atau "Jan 15, 2024" (EN)
```

### 2. **Language API**
**File:** `api/language_api.php`

**Endpoint yang Tersedia:**
- `GET /api/language_api.php?action=current` - Mendapatkan bahasa saat ini
- `GET /api/language_api.php?action=available` - Daftar bahasa yang tersedia
- `GET /api/language_api.php?action=translations` - Mendapatkan terjemahan
- `POST /api/language_api.php` - Mengubah bahasa

**Contoh Penggunaan JavaScript:**
```javascript
// Mengubah bahasa
fetch('/api/language_api.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({
        action: 'set_language',
        language: 'en'
    })
})
.then(response => response.json())
.then(data => {
    if (data.success) {
        window.location.reload();
    }
});
```

### 3. **Language Switcher di Header**
**File:** `layouts/header_modern.php`

**Fitur:**
- ✅ Dropdown pemilih bahasa dengan bendera
- ✅ Indikator bahasa aktif
- ✅ JavaScript untuk pergantian bahasa yang smooth
- ✅ Toast notification saat mengubah bahasa
- ✅ Auto-reload halaman setelah pergantian bahasa

### 4. **Database Multi-Bahasa**
**File:** `database_updates.sql`

**Tabel Baru:**
- `user_preferences` - Preferensi pengguna termasuk bahasa
- `content_translations` - Konten multi-bahasa
- `website_settings` - Pengaturan bahasa website

**Kolom Baru:**
- `vouchers.name_en`, `vouchers.description_en`
- `flash_sales.title_en`, `flash_sales.description_en`
- `notifications.title_en`, `notifications.message_en`

---

## 🚀 **Cara Implementasi**

### **1. Setup Database**
```sql
-- Jalankan script database
SOURCE database_updates.sql;
```

### **2. Include Language Helper**
```php
// Di setiap file PHP
require_once BASEPATH.'helpers/language_helper.php';
```

### **3. Gunakan Fungsi Terjemahan**
```php
// Ganti teks statis dengan fungsi lang()
echo "Beranda"; // ❌ Teks statis
echo lang('home'); // ✅ Multi-bahasa
```

### **4. Update Template**
```php
// Header
<title><?= lang('dashboard') ?> - <?= config('web', 'title') ?></title>

// Navigation
<a href="/"><?= lang('home') ?></a>
<a href="/services"><?= lang('services') ?></a>

// Content
<h1><?= lang('welcome_back') ?></h1>
<p><?= lang('balance') ?>: <?= format_currency($user_balance) ?></p>
```

---

## 📖 **Kamus Terjemahan Lengkap**

### **Navigasi & Header**
| Key | Indonesia | English |
|-----|-----------|---------|
| `home` | Beranda | Home |
| `services` | Layanan | Services |
| `price_list` | Daftar Harga | Price List |
| `check_transaction` | Cek Transaksi | Check Transaction |
| `deposit` | Deposit | Deposit |
| `voucher` | Voucher | Voucher |
| `flash_sale` | Flash Sale | Flash Sale |
| `support` | Customer Service | Customer Service |
| `login` | Masuk | Login |
| `register` | Daftar | Register |
| `logout` | Keluar | Logout |
| `profile` | Profil | Profile |
| `admin_panel` | Panel Admin | Admin Panel |

### **Dashboard**
| Key | Indonesia | English |
|-----|-----------|---------|
| `dashboard` | Dashboard | Dashboard |
| `welcome_back` | Selamat Datang Kembali | Welcome Back |
| `balance` | Saldo | Balance |
| `total_transactions` | Total Transaksi | Total Transactions |
| `pending_orders` | Pesanan Pending | Pending Orders |
| `recent_activities` | Aktivitas Terbaru | Recent Activities |

### **Sistem Voucher**
| Key | Indonesia | English |
|-----|-----------|---------|
| `voucher_code` | Kode Voucher | Voucher Code |
| `check_voucher` | Cek Voucher | Check Voucher |
| `apply_voucher` | Gunakan Voucher | Apply Voucher |
| `voucher_valid` | Voucher Valid! | Voucher Valid! |
| `voucher_invalid` | Voucher Tidak Valid | Invalid Voucher |
| `discount` | Diskon | Discount |
| `minimum_purchase` | Minimal Pembelian | Minimum Purchase |

### **Flash Sale**
| Key | Indonesia | English |
|-----|-----------|---------|
| `flash_sale_active` | Flash Sale Aktif! | Flash Sale Active! |
| `time_remaining` | Waktu Tersisa | Time Remaining |
| `original_price` | Harga Normal | Original Price |
| `sale_price` | Harga Diskon | Sale Price |
| `save_amount` | Hemat | Save |
| `limited_stock` | Stok Terbatas | Limited Stock |

### **Customer Service**
| Key | Indonesia | English |
|-----|-----------|---------|
| `create_ticket` | Buat Tiket | Create Ticket |
| `ticket_subject` | Subjek Tiket | Ticket Subject |
| `ticket_message` | Pesan | Message |
| `ticket_category` | Kategori | Category |
| `ticket_priority` | Prioritas | Priority |
| `ticket_status` | Status | Status |
| `reply_ticket` | Balas Tiket | Reply Ticket |
| `close_ticket` | Tutup Tiket | Close Ticket |

### **Status & Prioritas**
| Key | Indonesia | English |
|-----|-----------|---------|
| `open` | Terbuka | Open |
| `in_progress` | Dalam Proses | In Progress |
| `resolved` | Selesai | Resolved |
| `closed` | Ditutup | Closed |
| `low` | Rendah | Low |
| `medium` | Sedang | Medium |
| `high` | Tinggi | High |
| `urgent` | Mendesak | Urgent |

---

## ⚙️ **Konfigurasi Lanjutan**

### **Pengaturan Website**
```php
// Mengatur bahasa default
update_setting('default_language', 'id');

// Bahasa yang didukung
update_setting('supported_languages', 'id,en,ar'); // Tambah bahasa Arab

// Auto-detect bahasa browser
update_setting('auto_detect_language', '1');

// Enable/disable language switcher
update_setting('language_switcher_enabled', '1');
```

### **Menambah Bahasa Baru**
1. **Update Language Helper:**
```php
// Tambah di $this->languages
'ar' => [
    'name' => 'العربية',
    'code' => 'ar',
    'flag' => '🇸🇦',
    'direction' => 'rtl' // Right-to-left
]

// Tambah terjemahan di getTranslations()
'ar' => [
    'home' => 'الرئيسية',
    'services' => 'الخدمات',
    // ... dst
]
```

2. **Update Database:**
```sql
-- Tambah kolom bahasa baru
ALTER TABLE vouchers ADD COLUMN name_ar varchar(255);
ALTER TABLE flash_sales ADD COLUMN title_ar varchar(255);
```

### **RTL Support (Right-to-Left)**
```css
/* Tambah di CSS */
[dir="rtl"] {
    direction: rtl;
    text-align: right;
}

[dir="rtl"] .navbar-nav {
    margin-left: auto;
    margin-right: 0;
}
```

---

## 🔧 **Troubleshooting**

### **Masalah Umum:**

1. **Terjemahan tidak muncul:**
   - Pastikan `language_helper.php` sudah di-include
   - Cek apakah key terjemahan ada di database
   - Periksa session dan cookie bahasa

2. **Language switcher tidak berfungsi:**
   - Pastikan JavaScript tidak ada error
   - Cek endpoint API language
   - Periksa CSRF token jika digunakan

3. **Format mata uang salah:**
   - Periksa fungsi `format_currency()`
   - Pastikan locale server mendukung format yang diinginkan

### **Debug Mode:**
```php
// Tambah di development
if (config('web', 'environment') === 'development') {
    echo "Current Language: " . get_current_language();
    echo "Available Languages: " . print_r(get_available_languages(), true);
}
```

---

## 📈 **Manfaat Fitur Multi-Bahasa**

### **Untuk Pengguna:**
- ✅ Pengalaman yang lebih personal
- ✅ Kemudahan memahami interface
- ✅ Format mata uang dan tanggal yang familiar
- ✅ Aksesibilitas yang lebih baik

### **Untuk Bisnis:**
- ✅ Jangkauan pasar yang lebih luas
- ✅ Peningkatan konversi pengguna
- ✅ Profesionalitas website
- ✅ Competitive advantage

### **Untuk Developer:**
- ✅ Code yang mudah di-maintain
- ✅ Sistem yang scalable
- ✅ API yang lengkap
- ✅ Documentation yang comprehensive

---

## 🎯 **Pengembangan Selanjutnya**

### **Fitur yang Bisa Ditambahkan:**
1. **Auto-translation dengan Google Translate API**
2. **Language-specific SEO URLs** (`/id/beranda`, `/en/home`)
3. **Admin panel untuk manage terjemahan**
4. **Import/export terjemahan ke Excel/CSV**
5. **Pluralization support** (1 item, 2 items)
6. **Date/time localization yang lebih advanced**
7. **Number formatting per region**
8. **Currency conversion real-time**

### **Optimisasi Performance:**
1. **Caching terjemahan di Redis/Memcached**
2. **Lazy loading untuk terjemahan**
3. **CDN untuk static language files**
4. **Minification untuk language files**

---

## 📞 **Support & Dokumentasi**

Untuk bantuan implementasi fitur multi-bahasa:

1. **Baca dokumentasi lengkap** di file ini
2. **Cek contoh penggunaan** di code yang sudah ada
3. **Test di development environment** terlebih dahulu
4. **Monitor performance** setelah implementasi

**Catatan Penting:** 
- Selalu backup database sebelum menjalankan script SQL
- Test semua fitur setelah implementasi
- Monitor error logs untuk debugging
- Update terjemahan secara berkala

---

**🎉 Selamat! Website topup Anda sekarang mendukung multi-bahasa dengan fitur enterprise-level!**
