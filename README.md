# 🏥 SIIMUT - Sistem Indikator Mutu untuk Rumah Sakit  

![License](https://img.shields.io/badge/License-MIT-blue?style=flat-square)
![PHP Version](https://img.shields.io/badge/PHP-8.3-blue?style=flat-square&logo=php)
![Laravel Version](https://img.shields.io/badge/Laravel-11.0-red?style=flat-square&logo=laravel)
![Filament Version](https://img.shields.io/badge/Filament-3.2-purple?style=flat-square)

**SIIMUT (Sistem Indikator Mutu untuk Rumah Sakit)** adalah platform berbasis web yang dirancang untuk **memantau, menganalisis, dan meningkatkan mutu layanan kesehatan** di rumah sakit Indonesia. Sistem ini selaras dengan standar **Kementerian Kesehatan RI, Komisi Akreditasi Rumah Sakit (KARS), dan SNARS**, memungkinkan institusi kesehatan untuk **mengotomatiskan pengelolaan indikator mutu** guna mendukung peningkatan kualitas layanan berbasis data.  

Dengan meningkatnya tuntutan transparansi, akuntabilitas, dan efisiensi dalam pelayanan kesehatan, SIIMUT hadir sebagai solusi yang **terintegrasi, adaptif, dan berbasis teknologi** untuk membantu rumah sakit dalam pengambilan keputusan strategis serta pemenuhan regulasi nasional.  

## 🎯 Tujuan  

SIIMUT dirancang untuk membantu rumah sakit dalam:  

✅ **Efisiensi & Akurasi** – Digitalisasi pencatatan dan analisis untuk mengurangi kesalahan manual.  
✅ **Kepatuhan Standar** – Memastikan standar **KARS & SNARS** melalui pemantauan sistematis.  
✅ **Analisis Data** – Laporan real-time dan visualisasi untuk keputusan berbasis bukti.  
✅ **Peningkatan Mutu** – Identifikasi tren, analisis masalah, dan optimalisasi layanan.  
✅ **Akses & Integrasi** – Data terstruktur untuk manajemen, tenaga medis, dan unit mutu. terkoneksi.  

---

## 🚀 Quick Start  

Untuk menginstal dan menjalankan **SIIMUT**, ikuti langkah-langkah berikut:  

### 1️⃣ Clone Repository  
```sh
git clone https://github.com/juniyasyos/siimut_rs_citrahusada.git SIIMUT
cd SIIMUT
```  

### 2️⃣ Install Dependensi  
```sh
composer install && npm install
composer run post-root-package-install
```  

### 3️⃣ Konfigurasi Lingkungan  
```sh
composer run post-update-cmd
composer run post-create-project-cmd
```  
Sesuaikan file `.env` untuk konfigurasi **database** dan integrasi lainnya.  

### 4️⃣ Migrasi Database  
```sh
composer run setup
```  

### 5️⃣ Jalankan Aplikasi  
```sh
composer run dev
```  

---

## ⚙️ Fitur Utama  

### 🏥 **Manajemen Indikator Mutu yang Efisien**  
- Pemantauan indikator mutu berdasarkan **standar KARS & SNARS**.  
- Penyimpanan data historis untuk **analisis tren dan evaluasi mutu**.  

### 📊 **Dashboard & Analitik Real-Time**  
- **Visualisasi data indikator mutu** dalam bentuk grafik dan tabel interaktif.  
- **Laporan otomatis** yang dapat diekspor ke berbagai format (PDF, Excel).  

### 🔐 **Keamanan & Akses Kontrol**  
- **Role-Based Access Control (RBAC)** untuk memastikan akses data hanya bagi pihak yang berwenang.  
- **Audit log** untuk melacak perubahan dan aktivitas pengguna.  

### 🔄 **Integrasi & Skalabilitas**  
- **Dukungan API** untuk menghubungkan SIIMUT dengan sistem lain di rumah sakit.  
- **Struktur modular** yang dapat dikembangkan sesuai kebutuhan rumah sakit.  

### ⚙️ **Kustomisasi & Kemudahan Penggunaan**  
- **Antarmuka intuitif** untuk tenaga medis dan administrator.  
- **Konfigurasi fleksibel** untuk menyesuaikan dengan kebijakan mutu masing-masing rumah sakit.  

---

## 🔧 Konfigurasi  

### **Konfigurasi Database**  
Edit file `.env` dengan kredensial database:  
```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=siimut
DB_USERNAME=root
DB_PASSWORD=
```  

### **Konfigurasi Email (Opsional)**  
```ini
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS="admin@rs-example.com"
MAIL_FROM_NAME="SIIMUT RS"
```  

---

## 📢 Mengapa Memilih SIIMUT?  

SIIMUT dirancang khusus untuk mendukung **rumah sakit di Indonesia** dalam:  
✔ **Efisiensi Pemantauan** – Proses pelacakan indikator mutu lebih cepat dan akurat.  
✔ **Kepatuhan Regulasi** – Memastikan rumah sakit memenuhi standar **KARS & SNARS**.  
✔ **Dukungan Keputusan** – Laporan berbasis data untuk perbaikan mutu berkelanjutan.  
✔ **Keamanan & Skalabilitas** – Sistem aman dengan kemampuan ekspansi yang fleksibel.  

---

## 🤝 Kontribusi  

Kami menyambut kontribusi dari komunitas! Untuk berkontribusi:  
1. **Fork repositori ini**  
2. **Buat branch fitur baru** (`git checkout -b feature/nama-fitur`)  
3. **Commit perubahan Anda** (`git commit -m 'Menambahkan fitur baru'`)  
4. **Push ke branch Anda** (`git push origin feature/nama-fitur`)  
5. **Buka Pull Request**  

---

## 💬 Dukungan & Komunitas  

📌 **Laporkan Bug** – [Buka Issue](https://github.com/juniyasyos/siimut_rs_citrahusada/issues)  
💡 **Usulan Fitur** – [Request Fitur](https://github.com/juniyasyos/siimut_rs_citrahusada/issues)  
📧 **Kontak** – [Email Support](mailto:your-email@example.com)  

---

## ⭐ Dukung Proyek Ini  

Jika **SIIMUT** bermanfaat, jangan lupa **beri ⭐ di GitHub** dan bantu sebarkan! 🚀  

