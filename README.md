# SPK Bingxue Rancaekek
## Sistem Pendukung Keputusan Rekrutmen Karyawan (SAW Method)

### Setup Instructions

1. **Pastikan XAMPP berjalan** (Apache + MySQL).

2. **Import database:**
   - Buka phpMyAdmin (`http://localhost/phpmyadmin`)
   - Klik "Import"
   - Pilih file `database.sql` dari folder ini
   - Klik "Go"

3. **Salin folder ke htdocs:**
   - Copy seluruh folder `spk-bingxue` ke `C:/xampp/htdocs/`

4. **Akses aplikasi:**
   - Buka browser: `http://localhost/spk-bingxue/`

### Default Login
| Role  | Email                  | Password   |
|-------|------------------------|------------|
| Admin | admin@bingxue.com      | password   |
| User  | (Register baru)        | (bebas)    |

### Alur Penggunaan

#### Sebagai Pelamar (User):
1. Register akun baru
2. Login → akan masuk ke Dashboard Pelamar
3. Isi **Data Diri** (Pendidikan C4, Pengalaman C2, Umur C5)
4. Isi **Self-Assessment** (Komunikasi, Kerjasama, Etika, Teknis → C1)
5. Kerjakan **Pre-Test** pilihan ganda → skor otomatis → C3
6. Tunggu keputusan Admin

#### Sebagai Admin:
1. Login → Dashboard SAW
2. Lihat tabel pemeringkatan berdasarkan nilai Vi SAW
3. Klik **Terima** atau **Tolak** untuk setiap pelamar
4. Kelola Kriteria: ubah bobot W dan nilai sub-kriteria
5. Kelola Bank Soal: tambah/edit/hapus soal pre-test
6. Lihat Data Karyawan: karyawan masuk & karyawan keluar

### Rumus SAW
```
Normalisasi (Benefit): Rij = Xij / max(Xj)
Vi = Σ (Wj × Rij)
```

### Kriteria Default
| Kode | Nama            | Bobot |
|------|-----------------|-------|
| C1   | Skill           | 30%   |
| C2   | Pengalaman      | 25%   |
| C3   | Pre-Test        | 20%   |
| C4   | Pendidikan      | 15%   |
| C5   | Umur            | 10%   |
