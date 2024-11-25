# DocGen Implementation WP Class

Library WordPress untuk menyediakan fungsionalitas pembuatan dokumen yang dapat digunakan kembali untuk plugin WordPress. Library ini berbasis pada DocGen Implementation Plugin, dibuat sebagai class yang dapat dengan mudah diintegrasikan ke plugin WordPress manapun.

## 🎯 Tujuan

Library ini bertujuan untuk menyediakan cara mudah dalam menangani pembuatan dokumen di plugin WordPress tanpa perlu mengimplementasikan ulang fungsionalitas umum. Library ini menyediakan:

- Penanganan pembuatan dokumen
- Manajemen direktori (temp dan template)
- Manajemen template dokumen
- Konfigurasi yang mudah disesuaikan

## 🔧 Instalasi

1. Tambahkan repository ini sebagai submodule ke plugin WordPress Anda:
```bash
git submodule add https://github.com/arisciwek/docgen-wpclass.git includes/docgen-wpclass
```

2. Sertakan main class di file utama plugin Anda:
```php
require_once plugin_dir_path(__FILE__) . 'includes/docgen-wpclass/class-docgen-wpclass.php';
```

## 📋 Penggunaan

### Setup Dasar dalam Plugin Anda

```php
// Di file aktivasi plugin Anda
function activate_your_plugin() {
    // Setup DocGen dengan pengaturan default
    docgen_wpclass()->setup();
    
    // Atau dengan pengaturan kustom
    docgen_wpclass()->setup([
        'temp_dir' => 'custom-temp',
        'template_dir' => 'custom-templates',
        'output_format' => 'docx',
        'debug_mode' => false
    ]);
}
```

### Menggunakan Settings Default

DocGen WPClass menyediakan settings default:
```php
$default_settings = [
    'temp_dir' => 'docgen-temp',
    'template_dir' => 'docgen-templates',
    'output_format' => 'docx',
    'debug_mode' => false
];
```

### Integrasi dengan Form Settings Plugin

```php
// Di halaman settings plugin Anda
$settings = docgen_wpclass()->get_settings();
$default_settings = docgen_wpclass()->get_default_settings();

// Contoh form field
echo '<input type="text" name="temp_dir" 
    value="' . esc_attr($settings['temp_dir']) . '" 
    placeholder="' . esc_attr($default_settings['temp_dir']) . '">';
```

### Penggunaan untuk Membuat Dokumen

```php
// Generate dokumen
$data = [
    'title' => 'Dokumen Baru',
    'content' => 'Isi dokumen'
];

docgen_wpclass()->generate('template-name', $data);
```

## 🏗️ Struktur

```
docgen-wpclass/
├── class-docgen-wpclass.php  (Main Class)
├── LICENSE
└── README.md
```

## ✅ TODO

### Versi 1.0
- [ ] Fungsi Dasar
  - [ ] Implementasi generate document
  - [ ] Validasi direktori
  - [ ] Penanganan template
  - [ ] Sistem settings dasar

### Versi 1.1
- [ ] Peningkatan
  - [ ] Preview dokumen
  - [ ] Batch processing
  - [ ] Progress tracking
  - [ ] Sistem logging

### Versi 1.2
- [ ] Fitur Lanjutan
  - [ ] Kontrol versi template
  - [ ] Riwayat dokumen
  - [ ] Event system
  - [ ] API dokumentasi

## 🔒 Keamanan

Library ini menerapkan beberapa fitur keamanan:
- Validasi path
- Validasi tipe file
- Direktori yang aman
- Sanitasi input

## 📝 Lisensi

GPL v2 atau yang lebih baru

## 🤝 Kontribusi

Kontribusi sangat diterima! Silakan kirim Pull Request. Untuk perubahan besar, harap buka Issue terlebih dahulu untuk mendiskusikan apa yang ingin Anda ubah.

## 👥 Tim & Kredit

### Author & Maintainer
- Arisciwek ([@arisciwek](https://github.com/arisciwek))

### Base Project
- DocGen Implementation Plugin oleh Ari Sciwek
- WP Document Generator (WP DocGen) oleh Ari Sciwek

### Special Thanks
- WordPress Community
- Para pengguna dan kontributor DocGen Implementation Plugin
- Semua yang telah memberikan feedback dan saran

## 📦 Requirements

- WordPress 5.8 atau lebih tinggi
- PHP 7.4 atau lebih tinggi
- Plugin WP DocGen terinstall dan aktif

## ❓ FAQ

### Apakah perlu menginstall DocGen Implementation Plugin?
Tidak, library ini adalah versi mandiri yang dapat digunakan langsung di plugin Anda.

### Bagaimana jika plugin saya sudah memiliki sistem settings?
Library ini dapat dengan mudah diintegrasikan dengan sistem settings yang sudah ada. Gunakan method `get_settings()` dan `update_settings()`.

### Apakah bisa menggunakan direktori kustom?
Ya, Anda dapat menentukan direktori kustom saat setup atau melalui form settings.

## 🔄 Pembaruan

Dokumen ini akan terus diperbarui seiring perkembangan library. Periksa kembali untuk pembaruan dan fitur baru.

