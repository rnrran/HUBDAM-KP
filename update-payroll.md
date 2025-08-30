Update fitur payroll (gaji)

Ini adalah projek dengan laravel + vue (full stack app), vue starter kit

User dapat menginput gaji user lain melalui halaman LihatGaji dengan memilih user terkait, halaman sudah ada namun belum bisa memilih usernya

Ketika input berhasil disimpan, akan muncul popup untuk cetak/print struk gaji di atas

Untuk halaman Lihat Gaji, tambahkan action untuk print struk di tabel (install saja library), kemudian bagian visualisasi / tab grafik print visualisasi yang dapat difilter dalam rentang waktu, opsi /minggu, /bulan, /tahun, custom range (install library)
Untuk sumbu x, gunakan timestampnya/waktunya, sumbu y value gaji/payrollnya, ada tiga titik di indikasikan dengan warna
warna merah = potongan
warna hijau = total gaji bersih
warna biru = total gaji bersih setelah potongan

# wajib diterapkan
- Baca model yang sudah ada @Payroll.php 
- Pastikan page / komponen responsif
- Pastikan data yang diambil dinamis
- Konsistensi style dengan yang sudah dibangun/sudah ada