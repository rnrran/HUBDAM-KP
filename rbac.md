Update fitur terapkan rbac

Ini adalah projek dengan laravel + vue (full stack app), vue starter kit


# wajib diterapkan
- Baca model yang sudah ada @Payroll.php 
- Pastikan page / komponen responsif
- Pastikan data yang diambil dinamis
- Konsistensi style dengan yang sudah dibangun/sudah ada

role tersedia 
- admin
- supervisor
- pengguna

# hak akses
## admin
dapat mengakses semuanya

## supervisor
- tidak bisa melakukan perubahan data pada aplikasi
- hanya dapat menggunakan fitur terkait lihat seperti lihat gaji, namun tidak untuk input gaji (tidak ada fitur pada sidebar)
  - di lihat gaji supervisor tidak dapat menghapus atau mengedit (tidak ada tombol action atau disable saja)
- tidak ada halaman atau tab edit akun dan buat akun di sidebar

- buatkan saja sidebar untuk role supervisor
file sidebar ada pada @AppSidebar.vue

- mempunyai fitur pengguna

## pengguna
hanya dapat melihat gajinya sendiri pada halaman /dashboard

# tambahan
pada halaman edit belum ada menu pilih user, tambahkan itu 
pastikan data yang terfetch dan akan diedit adalah user terkait