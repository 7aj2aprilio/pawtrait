# 📸 Photobooth Web Application

Aplikasi web photobooth profesional dengan integrasi payment gateway Midtrans. Dibangun menggunakan PHP native dengan desain modern dan fitur lengkap.

## ✨ Fitur Utama

### User Features
- 🔐 **Sistem Autentikasi** - Register, login, dan session management
- 📷 **Photo Booth** - Capture foto langsung dari webcam dengan berbagai filter
- 🎨 **Filter & Effects** - 6 filter berbeda (None, B&W, Sepia, Blur, Brightness, Contrast)
- 📦 **Package Selection** - Berbagai paket photobooth dengan harga berbeda
- 🛒 **Shopping Cart** - Keranjang belanja dengan manajemen item
- 💳 **Midtrans Payment** - Integrasi payment gateway Midtrans Snap
- 📊 **User Dashboard** - Statistik, riwayat transaksi, dan galeri foto
- 💾 **Photo Download** - Download foto hasil capture

### Admin Features
- ⚙️ **Admin Panel** - Dashboard khusus admin
- 📊 **Statistics** - Total users, transaksi, revenue, dan foto
- 📦 **Package Management** - CRUD operations untuk paket
- 💰 **Transaction Monitoring** - Monitor semua transaksi
- 👥 **User Management** - Lihat data pengguna

## 🚀 Teknologi

- **Backend**: PHP Native
- **Database**: MySQL dengan PDO
- **Payment**: Midtrans Snap API
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Design**: Modern dark theme dengan gradients dan animations

## 📋 Persyaratan

- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- Apache Web Server dengan mod_rewrite
- Akun Midtrans (Sandbox atau Production)

## 🛠️ Instalasi

### 1. Clone atau Download Project

```bash
cd d:/Tugas\ Kuliah\ 5/Komputasi\ Awan/pawtrait
```

### 2. Setup Database

Buat database dan import schema:

```bash
mysql -u root -p
```

```sql
CREATE DATABASE photobooth_db;
USE photobooth_db;
SOURCE database.sql;
```

### 3. Konfigurasi Database

Edit file `config/database.php`:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // Password MySQL Anda
define('DB_NAME', 'photobooth_db');
```

### 4. Konfigurasi Midtrans

Edit file `config/midtrans.php` dan masukkan kredensial Midtrans Anda:

```php
// Set to true for production, false for sandbox
define('MIDTRANS_IS_PRODUCTION', false);

// Your Midtrans Server Key
define('MIDTRANS_SERVER_KEY', 'SB-Mid-server-YOUR_SERVER_KEY_HERE');

// Your Midtrans Client Key
define('MIDTRANS_CLIENT_KEY', 'SB-Mid-client-YOUR_CLIENT_KEY_HERE');
```

**Cara mendapatkan kredensial Midtrans:**
1. Daftar di [Midtrans](https://dashboard.midtrans.com/register)
2. Login ke dashboard
3. Pilih environment (Sandbox untuk testing)
4. Copy Server Key dan Client Key dari Settings → Access Keys

### 5. Set Permissions

Pastikan folder uploads dapat ditulis:

```bash
chmod -R 755 uploads/
```

### 6. Jalankan Aplikasi

Gunakan PHP built-in server atau Apache:

**PHP Built-in Server:**
```bash
php -S localhost:8000
```

**Apache:**
- Pastikan document root mengarah ke folder project
- Akses melalui `http://localhost/pawtrait`

## 👤 Default Login

### Admin
- **URL**: `http://localhost:8000/admin/login.php`
- **Username**: `admin`
- **Password**: `password`

### User
Buat akun baru melalui halaman register

## 📁 Struktur Project

```
pawtrait/
├── admin/                  # Admin panel
│   ├── dashboard.php
│   ├── login.php
│   ├── packages.php
│   └── transactions.php
├── api/                    # API endpoints
│   ├── create-transaction.php
│   ├── get-photos.php
│   ├── payment-callback.php
│   └── upload-photo.php
├── assets/
│   ├── css/
│   │   └── style.css      # Main stylesheet
│   └── js/
│       ├── payment.js     # Midtrans integration
│       └── photobooth.js  # Camera functionality
├── config/
│   ├── database.php       # Database config
│   └── midtrans.php       # Midtrans config
├── includes/
│   ├── header.php
│   └── footer.php
├── uploads/
│   └── photos/            # Uploaded photos
├── cart.php
├── checkout.php
├── dashboard.php
├── database.sql
├── index.php
├── login.php
├── logout.php
├── packages.php
├── payment-failed.php
├── payment-success.php
├── photobooth.php
├── register.php
└── .htaccess
```

## 🎯 Cara Penggunaan

### User Flow
1. **Register** - Buat akun baru
2. **Login** - Masuk ke sistem
3. **Photo Booth** - Capture foto dengan webcam
4. **Apply Filters** - Pilih filter yang diinginkan
5. **Browse Packages** - Lihat paket yang tersedia
6. **Add to Cart** - Tambahkan paket ke keranjang
7. **Checkout** - Proses pembayaran
8. **Pay with Midtrans** - Pilih metode pembayaran
9. **Dashboard** - Lihat riwayat dan foto

### Admin Flow
1. **Login** - Masuk ke admin panel
2. **Dashboard** - Lihat statistik
3. **Manage Packages** - Tambah/edit/hapus paket
4. **Monitor Transactions** - Pantau transaksi

## 🔧 Konfigurasi Midtrans Webhook

Untuk menerima notifikasi pembayaran, set webhook URL di Midtrans Dashboard:

**Notification URL:**
```
http://your-domain.com/api/payment-callback.php
```

**Finish Redirect URL:**
```
http://your-domain.com/payment-success.php
```

**Error Redirect URL:**
```
http://your-domain.com/payment-failed.php
```

## 🎨 Fitur Design

- ✅ Dark theme modern dengan gradients
- ✅ Glassmorphism effects
- ✅ Smooth animations dan transitions
- ✅ Responsive design (mobile-friendly)
- ✅ Interactive hover effects
- ✅ Loading states
- ✅ Status badges dengan warna

## 🔒 Keamanan

- Password hashing dengan `password_hash()`
- Prepared statements untuk SQL queries
- Session management
- Input validation dan sanitization
- CSRF protection ready
- XSS protection headers

## 📝 Database Schema

### Tables
- `users` - Data pengguna
- `admins` - Data admin
- `packages` - Paket photobooth
- `photos` - Foto yang di-capture
- `transactions` - Transaksi pembayaran

## 🐛 Troubleshooting

### Camera tidak berfungsi
- Pastikan browser memiliki permission untuk mengakses webcam
- Gunakan HTTPS atau localhost
- Check console browser untuk error

### Payment popup tidak muncul
- Pastikan Midtrans credentials sudah benar
- Check console browser untuk error
- Pastikan Snap.js ter-load dengan benar

### Upload foto gagal
- Check permissions folder `uploads/photos/`
- Pastikan `upload_max_filesize` di php.ini cukup besar

## 📞 Support

Untuk pertanyaan atau issue, silakan hubungi:
- Email: info@photobooth.com
- Phone: +62 812-3456-7890

## 📄 License

MIT License - Free to use for personal and commercial projects

## 🙏 Credits

- Midtrans Payment Gateway
- Google Fonts (Inter)
- Modern CSS Design Patterns

---

**Developed with ❤️ using PHP Native**
