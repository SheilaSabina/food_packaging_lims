Berikut adalah salinan lengkap dokumen **PRD Sistem Manajemen Pengujian Keamanan Kemasan Produk Pangan** dalam format Markdown (.md) untuk kebutuhan konteks pengembangan Anda.

---

# PRD Sistem Manajemen Pengujian Keamanan Kemasan Produk Pangan

[cite_start]**Pengujian dan Penjaminan Kualitas Perangkat Lunak** [cite: 1]
[cite_start]**Laporan Kelompok 4** [cite: 2]
[cite_start]**Tahun:** 2026 [cite: 5]

### Anggota Kelompok
| Nama | NIM |
| :--- | :--- |
| Zahra Nabila | [cite_start]2310817320007 [cite: 4] |
| Sheila Sabina | [cite_start]2310817220028 [cite: 4] |
| Alysa Armelia | [cite_start]2310817120009 [cite: 4] |
| Muhammad Azwin Hakim | [cite_start]2310817310012 [cite: 4] |

---

## 1. Latar Belakang
[cite_start]Keamanan kemasan pangan merupakan isu krusial karena potensi migrasi senyawa kimia berbahaya (seperti *plasticizers* dan *phthalates*) ke produk konsumsi yang berisiko bagi kesehatan[cite: 10, 11]. [cite_start]Selain migrasi kimia, aspek keselamatan radiologis seperti konsentrasi radon pada air minum dalam kemasan juga memerlukan pengawasan ketat[cite: 12].

[cite_start]Laboratorium pengujian menghadapi tantangan efisiensi operasional dan konsistensi data[cite: 14]. [cite_start]Implementasi **Laboratory Information Management System (LIMS) 4.0** dan kepatuhan terhadap standar **ISO/IEC 17025** menjadi solusi untuk memastikan akurasi data serta validitas hasil pengujian[cite: 15, 16]. [cite_start]Dokumen PRD ini disusun untuk memetakan *User Journey* dan *User Story* guna memastikan sistem memiliki kriteria penerimaan (*Acceptance Criteria*) yang terukur demi menjaga standar keamanan pangan[cite: 21, 22].

---

## 2. User Journey

### User Journey 1: Alur Registrasi Pelanggan & Pengajuan Sampel Uji
[cite_start]**Actor:** Klien (Produsen Kemasan Pangan), Sistem, Admin Lab [cite: 26]

| Phase | Registrasi & Profiling | Pengajuan Uji (e-Form) | Logistik & Pengiriman | Terima & Serah Sampel |
| :--- | :--- | :--- | :--- | :--- |
| **Actions** | [cite_start]Klien mendaftarkan akun perusahaan dan mengunggah dokumen legalitas (NIB/NPWP)[cite: 27]. | [cite_start]Klien memilih jenis material kemasan dan parameter uji (migrasi, kontaminasi) melalui e-form[cite: 27]. | [cite_start]Klien men-generate QR Code, mencetak label, dan menginput data pengiriman[cite: 27]. | [cite_start]Admin memindai QR Code melalui sistem dan menginput kondisi sampel[cite: 27]. |
| **Touchpoint** | [cite_start]Halaman Registrasi Vendor[cite: 27]. | [cite_start]Dashboard - Form Order Pengujian[cite: 27]. | [cite_start]Modul Cetak Label Pengiriman[cite: 27]. | [cite_start]Dashboard Admin (Logistik Masuk)[cite: 27]. |
| **Respons Sistem** | [cite_start]Validasi otomatis dokumen; akses langsung ke dashboard[cite: 27]. | [cite_start]Menampilkan estimasi biaya transparan berdasarkan regulasi (SNI/BPOM)[cite: 27]. | [cite_start]Menghasilkan nomor resi internal pelacakan[cite: 27]. | [cite_start]Notifikasi "Sampel Diterima" via email dan WhatsApp[cite: 27]. |
| **Aturan Bisnis** | [cite_start]Format PDF dan masih berlaku[cite: 27]. | [cite_start]Wajib mencantumkan jenis produk konsumsi (kering, berlemak, cair)[cite: 27]. | [cite_start]Label harus ditempel di kemasan luar[cite: 27]. | [cite_start]Jika rusak, sistem meminta kiriman ulang[cite: 27]. |
| **Status Data** | [cite_start]Akun Aktif[cite: 29]. | [cite_start]Menunggu Pengiriman[cite: 29]. | [cite_start]Sampel Dalam Perjalanan[cite: 29]. | [cite_start]Sampel Diterima (In-Lab)[cite: 29]. |

### User Journey 2: Pelaksanaan Pengujian Laboratorium Teknis
[cite_start]**Actor:** Teknisi Lab, Sistem, Supervisor QC [cite: 32]

| Phase | Persiapan & Kalibrasi | Eksekusi Pengujian Keamanan | Input Temuan Laboratorium | Verifikasi Hasil Uji |
| :--- | :--- | :--- | :--- | :--- |
| **Actions** | [cite_start]Teknisi mengecek status kalibrasi alat di sistem[cite: 33]. | [cite_start]Simulasi penggunaan nyata (suhu tinggi, kelembaban, kontak asam/lemak)[cite: 33]. | [cite_start]Input data numerik (nilai migrasi, BPA/radon) dan unggah foto bukti[cite: 33]. | [cite_start]Supervisor meninjau data dan menekan tombol approve/reject[cite: 33]. |
| **Respons Sistem** | [cite_start]Menampilkan status "Siap Pakai" atau "Expired"[cite: 33]. | [cite_start]Mencatat timestamp mulai dan berakhir pengujian[cite: 33]. | [cite_start]Otomatis membandingkan hasil dengan threshold SNI/BPOM/FDA/EFSA[cite: 33]. | [cite_start]Mengunci data terverifikasi agar tidak bisa diubah[cite: 33]. |
| **Status Data** | [cite_start]Alat Siap[cite: 35]. | [cite_start]Proses (In-Progress)[cite: 35]. | [cite_start]Hasil Uji Tersimpan (Draft)[cite: 35]. | [cite_start]Data Terverifikasi (Verified)[cite: 35]. |

### User Journey 3: Penerbitan Sertifikat & Laporan Hasil Uji (LHU)
[cite_start]**Actor:** Manajer Mutu, Sistem, Klien [cite: 39]

| Phase | Kompilasi Laporan Akhir | Persetujuan & Tanda Tangan | Publikasi Sertifikat Resmi | Pengambilan Hasil oleh Klien |
| :--- | :--- | :--- | :--- | :--- |
| **Actions** | [cite_start]Manajer Mutu meninjau draft dan men-generate laporan akhir[cite: 40]. | [cite_start]Manajer Mutu melakukan e-signature melalui sistem[cite: 40]. | [cite_start]Klien mengunduh sertifikat berformat PDF melalui dashboard[cite: 40]. | [cite_start]Klien meninjau detail laporan hasil uji[cite: 40]. |
| **Respons Sistem** | [cite_start]Menampilkan status "AMAN" atau "TIDAK MEMENUHI STANDAR"[cite: 40]. | [cite_start]Menyematkan QR Code segel keamanan untuk mencegah pemalsuan[cite: 40]. | [cite_start]Notifikasi publikasi LHU dikirim ke klien[cite: 40]. | [cite_start]Akses unduh terbuka jika status pembayaran 'PAID'[cite: 40]. |
| **Status Data** | [cite_start]Draft Laporan[cite: 40]. | [cite_start]Laporan Disetujui[cite: 40]. | [cite_start]Sertifikat Terbit[cite: 40]. | [cite_start]Selesai (Completed)[cite: 40]. |

### User Journey 4: Penanganan Sampel Gagal & Uji Ulang
[cite_start]**Actor:** Admin Lab, Klien [cite: 44]

| Phase | Notifikasi Ketidaksesuaian | Konsultasi Analisis Kegagalan | Perbaikan Spesifikasi Produk | Pengajuan Retesting |
| :--- | :--- | :--- | :--- | :--- |
| **Actions** | [cite_start]Klien menerima notifikasi otomatis dan melihat parameter yang gagal[cite: 45]. | [cite_start]Klien berkonsultasi dengan Admin Lab melalui sistem[cite: 45]. | [cite_start]Klien menginput deskripsi perbaikan produk ke sistem[cite: 45]. | [cite_start]Klien men-submit pengajuan retesting[cite: 45]. |
| **Respons Sistem** | [cite_start]Status sampel berubah menjadi "GAGAL/NOT COMPLIANT"[cite: 45]. | [cite_start]Menyediakan histori data uji untuk analisis penyebab (kontaminasi/material)[cite: 45]. | [cite_start]Menyimpan versi "Revisi 1" untuk pelacakan[cite: 45]. | [cite_start]Rekomendasi perbaikan material agar sesuai standar food-grade[cite: 45]. |
| **Status Data** | [cite_start]Tidak Lulus (Failed)[cite: 45]. | [cite_start]Dalam Analisis[cite: 45]. | [cite_start]Perbaikan Formulasi[cite: 45]. | [cite_start]Pengajuan Uji Ulang[cite: 45]. |

---

## 3. User Stories

### Journey 1: Registrasi & Pengajuan
* **US-1.1 Registrasi Akun Perusahaan**
    * **Prioritas:** High | [cite_start]**Estimasi:** 8 SP [cite: 52]
    * [cite_start]**Story:** Sebagai klien, saya ingin mendaftar dengan mengunggah NIB/NPWP PDF agar dapat akses tanpa verifikasi manual[cite: 51, 52].
    * [cite_start]**AC:** Validasi PDF otomatis; tolak format lain (.jpg/.docx); tolak dokumen kedaluwarsa[cite: 52].
* **US-1.2 Login Klien**
    * **Prioritas:** High | [cite_start]**Estimasi:** 5 SP [cite: 56]
    * [cite_start]**AC:** Kunci akun 15 menit jika salah password 3x; fitur reset password via email (masa aktif 30 menit)[cite: 56].
* **US-1.3 Pengajuan Order via e-Form**
    * **Prioritas:** High | [cite_start]**Estimasi:** 13 SP [cite: 60]
    * [cite_start]**AC:** Wajib isi jenis produk konsumsi; sistem memberikan ID order dan status "Menunggu Pengiriman"[cite: 60].
* **US-1.4 Estimasi Biaya**
    * **Prioritas:** Medium | [cite_start]**Estimasi:** 5 SP [cite: 67, 70]
    * [cite_start]**AC:** Tampil rincian per item & acuan regulasi; update otomatis tanpa reload jika parameter diubah[cite: 76, 79].
* **US-1.5 Label QR Code**
    * **Prioritas:** High | [cite_start]**Estimasi:** 8 SP [cite: 83]
    * [cite_start]**AC:** Generate label siap cetak layout A4; peringatan jika mencoba generate ulang untuk ID yang sama[cite: 83].
* **US-1.6 Input Data Pengiriman**
    * **Prioritas:** High | [cite_start]**Estimasi:** 5 SP [cite: 87]
    * [cite_start]**AC:** Wajib nomor resi; status berubah menjadi "Sampel Dalam Perjalanan"[cite: 87].
* **US-1.7 Konfirmasi Penerimaan Lab**
    * **Prioritas:** High | [cite_start]**Estimasi:** 8 SP [cite: 91]
    * [cite_start]**AC:** Scan QR valid; input kondisi (Baik/Rusak); notifikasi otomatis ke klien[cite: 91].
* **US-1.8 Permintaan Kirim Ulang**
    * **Prioritas:** Medium | [cite_start]**Estimasi:** 5 SP [cite: 95]
    * [cite_start]**AC:** Status "Pengiriman Ulang Diperlukan"; klien melihat catatan kerusakan dan tombol kirim data baru[cite: 95].

### Journey 2: Pelaksanaan Pengujian
* **US-2.1 Login Staff Lab**
    * **Prioritas:** High | [cite_start]**Estimasi:** 3 SP [cite: 100]
    * [cite_start]**AC:** Autentikasi sesuai peran (Teknisi/Supervisor); proteksi brute-force[cite: 100].
* **US-2.2 Cek Kalibrasi Alat**
    * **Prioritas:** High | [cite_start]**Estimasi:** 5 SP [cite: 104]
    * [cite_start]**AC:** Kunci alat jika kalibrasi kedaluwarsa; catat alat yang digunakan ke data pengujian[cite: 104].
* **US-2.3 Simulasi Pengujian**
    * **Prioritas:** High | [cite_start]**Estimasi:** 5 SP [cite: 108]
    * [cite_start]**AC:** Form otomatis isi ID & standar; catat timestamp mulai; validasi metode sesuai standar keamanan[cite: 108].
* **US-2.4 Input Data Numerik**
    * **Prioritas:** High | [cite_start]**Estimasi:** 8 SP [cite: 112]
    * [cite_start]**AC:** Hanya terima angka; otomatis bandingkan dengan threshold SNI/BPOM/FDA; tautkan foto bukti[cite: 112].
* **US-2.5 Perbandingan Otomatis**
    * **Prioritas:** High | [cite_start]**Estimasi:** 5 SP [cite: 116]
    * [cite_start]**AC:** Indikator BERHASIL/GAGAL real-time; eskalasi ke admin jika threshold belum diatur[cite: 116].
* **US-2.6 Approval Supervisor**
    * **Prioritas:** High | [cite_start]**Estimasi:** 5 SP [cite: 120]
    * [cite_start]**AC:** Status "Verified"; kunci data permanen; wajib isi alasan jika melakukan 'Reject'[cite: 120, 122].

### Journey 3: Sertifikasi & LHU
* [cite_start]**US-3.1 Generate Laporan** (Prioritas: High, 5 SP): Status awal "Draft Laporan"[cite: 128].
* [cite_start]**US-3.2 Review Laporan** (Prioritas: Medium, 3 SP): Manajer Mutu bisa edit data jika ditemukan kesalahan sebelum *approval*[cite: 132].
* [cite_start]**US-3.3 E-Signature** (Prioritas: High, 5 SP): Validasi tanda tangan digital; status berubah jadi "Laporan Disetujui"[cite: 136].
* [cite_start]**US-3.4 Publikasi Sertifikat** (Prioritas: Medium, 3 SP): Notifikasi otomatis ke klien saat terbit[cite: 140].
* [cite_start]**US-3.5 Unduh Sertifikat** (Prioritas: High, 2 SP): Format PDF; hanya aktif jika pembayaran 'PAID'[cite: 144].

### Journey 4: Penanganan Gagal
* [cite_start]**US-4.1 Notifikasi Gagal** (Prioritas: High, 2 SP): Alert otomatis saat sampel tidak memenuhi standar[cite: 150].
* [cite_start]**US-4.2 Detail Kegagalan** (Prioritas: High, 3 SP): Menampilkan nilai uji vs threshold secara jelas[cite: 154].
* [cite_start]**US-4.3 Modul Konsultasi** (Prioritas: Medium, 5 SP): Fitur chat antara klien dan admin; simpan histori pesan[cite: 158].
* [cite_start]**US-4.4 Input Perbaikan** (Prioritas: Medium, 3 SP): Simpan deskripsi perubahan material sebagai dasar retest[cite: 162].
* [cite_start]**US-4.5 Pengajuan Retesting** (Prioritas: High, 5 SP): Form khusus uji ulang; status "Pengajuan Uji Ulang"[cite: 167].

---

## Daftar Pustaka
1.  Ong, H.-T., et al. (2022). Migration of endocrine-disrupting chemicals into food. *Crit. Rev. Food Sci. [cite_start]Nutr.*[cite: 177, 178].
2.  Gazi, M., et al. (2026). Radiological safety assessment of packaged bottle drinking water in India. *J. Radiat. Res. Appl. [cite_start]Sci.*[cite: 179, 180].
3.  Gupta, R. K., et al. (2024). Migration of Chemical Compounds from Packaging Materials into Packaged Foods. [cite_start]*Foods*.[cite: 181, 182].
4.  Yuen, N. H.-Y., et al. (2025). Laboratory Information Management System (LIMS) 4.0. *Int. J. Eng. Bus. [cite_start]Manag.*[cite: 183, 184].
5.  Petrovic, B., et al. (2023). EURADOS ISO/IEC 17025 guidance for IMS. *Radiat. Prot. [cite_start]Dosimetry*.[cite: 185, 186].
6.  Xu, X., et al. (2023). A Requirement Quality Assessment Method Based on User Stories. [cite_start]*Electronics*.[cite: 187, 188].
7.  Silva, D. I. & Siriwardana, L. K. B. (2023). [cite_start]Comparative Analysis of Software Quality Assurance Approaches.[cite: 189].