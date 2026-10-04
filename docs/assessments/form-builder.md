# Dokumentasi Form Builder Assessment

Dokumen ini menjelaskan seluruh field yang tersedia untuk menyusun satu
assessment melalui menu **Assessment → Buat Assessment**, termasuk fungsi,
aturan pengisian, dan struktur data setelah assessment disimpan.

Dokumentasi ini mengikuti implementasi saat ini pada:

- `AssessmentController`;
- `Assessment`, `AssessmentForm`, dan `AssessmentFormField`;
- view Form Builder assessment; dan
- resolver auto-fill, lookup, dependency, serta scoring.

## 1. Konsep dan hierarki

Satu assessment memiliki struktur bertingkat berikut:

```text
Assessment
├── Metadata assessment
├── Form 1
│   ├── Metadata form
│   └── Field / pertanyaan 1..n
├── Form 2
│   ├── Metadata form
│   └── Field / pertanyaan 1..n
└── ...
```

Perbedaan istilahnya:

| Istilah | Definisi | Kegunaan |
|---|---|---|
| Assessment | Wadah utama instrumen penilaian | Menentukan identitas, target pengguna, status, dan jenis instrumen |
| Form | Bagian atau kelompok indikator dalam assessment | Mengelompokkan pertanyaan berdasarkan kompetensi/indikator |
| Field | Satu pertanyaan atau komponen input | Menentukan data yang diisi peserta dan cara menilainya |

Assessment minimal harus memiliki satu form, dan setiap form minimal harus
memiliki satu field.

## 2. Field metadata assessment

Field berikut berada di bagian atas halaman Form Builder.

| Field | Nama data | Wajib | Definisi dan kegunaan |
|---|---|:---:|---|
| Kode Assessment | `kode_assessment` | Tidak | Identitas unik assessment. Jika dikosongkan, kode dibuat otomatis saat disimpan. Kode manual harus unik dan maksimal 100 karakter. |
| Judul Assessment | `judul` | Ya | Nama yang ditampilkan untuk mengenali assessment. Judul juga digunakan untuk membuat `slug` otomatis. Maksimal 255 karakter. |
| Status | `status` | Ya | Siklus publikasi assessment: `draft`, `publish`, atau `nonaktif`. |
| Ketenagaan Assessment | `target_ketenagaan` | Ya* | Menentukan kelompok sasaran dan kelompok pengguna yang menerima penugasan otomatis. Pilih `Tenaga Pendidik`, `Tenaga Kependidikan`, atau `Stakeholder`. Tidak ditampilkan pada alur Bank Soal Evaluasi Pelaksanaan. |
| Jenis Instrumen Penilaian | `instrument_type` | Tidak | Menentukan konteks instrumen dan konfigurasi bobot/default scoring. |
| Deskripsi | `deskripsi` | Tidak | Ringkasan tujuan, cakupan, atau konteks assessment yang dibaca pengguna. |
| Petunjuk Pengisian | `petunjuk` | Tidak | Panduan umum yang ditampilkan sebelum pengguna mengisi assessment. |
| Aktifkan Assessment | `is_active` | Tidak | Mengaktifkan atau menonaktifkan assessment. Checkbox yang tidak dicentang dikirim sebagai `false`. |

Tanda `*` berarti wajib pada assessment biasa. Pada kategori Evaluasi
Pelaksanaan, target ketenagaan diatur oleh alur khusus dan tidak diminta pada
form ini.

### 2.1 Pilihan target ketenagaan

| Nilai tersimpan | Label |
|---|---|
| `tenaga_pendidik` | Tenaga Pendidik |
| `tenaga_kependidikan` | Tenaga Kependidikan |
| `stakeholder` | Stakeholder |

Pilihan ini ikut menentukan konteks penugasan dan dapat membantu sistem
menyarankan master jabatan yang sesuai ketika field menggunakan lookup.

### 2.2 Pilihan jenis instrumen

| Nilai tersimpan | Label | Bobot default |
|---|---|---:|
| `portofolio` | Portofolio | 0,30 |
| `pilihan_ganda_kompleks` | Pilihan Ganda Kompleks | 0,40 |
| `skala_likert` | Skala Likert | 1,00 |
| `studi_kasus` | Studi Kasus | 0,30 |
| `monitoring_observasi_eviden` | Monitoring / Observasi / Eviden | 0,20 |

Bobot tersebut menjadi nilai awal `scoring_config.weight` pada assessment.
Jika beberapa instrumen digabung, sistem dapat menormalisasi bobot sesuai
instrumen yang tersedia.

### 2.3 Data yang dibuat otomatis

Field berikut bukan input utama pengguna, tetapi akan tersedia setelah data
disimpan:

| Data | Cara dibuat |
|---|---|
| `id` | ID database otomatis. |
| `slug` | Slug dari judul; jika sudah digunakan, ditambah akhiran `-2`, `-3`, dan seterusnya. |
| `kode_assessment` | Jika kosong, dibuat dengan pola seperti `ASM-LIKERT-001`, `ASM-PG-001`, atau prefix dari judul. |
| `kategori` | `assessment` untuk assessment biasa atau `evaluasi_pelaksanaan` untuk Bank Soal Evaluasi Pelaksanaan. |
| `scoring_config` | Dibuat dari jenis instrumen, dengan konfigurasi verifikasi dan ambang jawaban kosong bawaan. |
| `created_at`, `updated_at` | Timestamp Laravel. |

## 3. Field metadata form

Klik **Tambah Form** untuk membuat satu bagian assessment.

| Field | Nama data | Wajib | Definisi dan kegunaan |
|---|---|:---:|---|
| ID | `forms[*][id]` | Tidak | ID form lama saat edit. Tidak perlu diisi ketika membuat form baru. |
| Judul Form | `forms[*][judul_form]` | Ya | Nama bagian, misalnya `Profil Peserta`, `Kompetensi Pedagogik`, atau `Refleksi Praktik`. |
| Kode Form | `forms[*][kode_form]` | Tidak | Kode unik/semantik untuk bagian form, misalnya `FORM-PED-01`. Jika kosong, sistem membuat `FORM-01`, `FORM-02`, dan seterusnya berdasarkan urutan form. |
| Urutan | `forms[*][urutan]` | Tidak | Urutan tampilan dan urutan pengolahan form. Nilai minimal 1. Default mengikuti posisi form. |
| Kompetensi | `forms[*][kompetensi]` | Kondisional | Pemetaan form ke kompetensi guru. Wajib untuk form yang `Masuk penilaian` pada assessment biasa. |
| Kode Indikator | `forms[*][indikator_kode]` | Tidak | Kode indikator yang menjadi acuan form, misalnya `1.1` atau `P2`. |
| Label Indikator | `forms[*][indikator_label]` | Tidak | Nama atau uraian indikator yang ditampilkan sebagai konteks penilaian. |
| Deskripsi Form | `forms[*][deskripsi]` | Tidak | Penjelasan cakupan, tujuan, atau konteks bagian form. |
| Masuk penilaian | `forms[*][is_scoreable]` | Tidak | Jika aktif, jawaban form dapat masuk ke perhitungan skor. Jika tidak aktif, form tetap dapat mengumpulkan data tetapi tidak dihitung sebagai skor kompetensi. Default aktif. |
| Aktif | `forms[*][is_active]` | Tidak | Menentukan apakah form tampil pada peserta/preview. Form nonaktif tidak ditampilkan. Default aktif untuk form baru. |

### 3.1 Pengaturan skor form

Bagian **Pengaturan Skor Form** menyimpan konfigurasi di
`forms[*][scoring]`, kemudian disimpan sebagai `assessment_forms.scoring_config`.

| Field | Nama data | Definisi dan kegunaan |
|---|---|---|
| Profil Scoring | `scoring[profile]` | Konteks aturan scoring. Pilihan yang tersedia: `generic`, `portofolio`, `study_case_default`, `pilihan_ganda_kompleks`, dan `skala_likert`. Jika kosong, mengikuti instrumen assessment. |
| Bobot Form | `scoring[weight]` | Bobot relatif form ketika beberapa form digabung. Nilai numerik minimal 0. |
| Keluarkan dari kompetensi | `scoring[exclude_from_competency]` | Flag teknis untuk menyimpan form yang dinilai tetapi tidak menyumbang skor kompetensi. Pada UI saat ini nilainya disiapkan sebagai `false` dan tidak ditampilkan sebagai kontrol terpisah. |
| Aturan lanjutan | `scoring[advanced_rules_text]` | JSON aturan lanjutan. Field ini tersedia di payload, tetapi pada UI saat ini berupa field teknis tersembunyi. |

Kompetensi yang tersedia:

| Nilai | Label |
|---|---|
| `pedagogik` | Pedagogik |
| `kepribadian` | Kepribadian |
| `sosial` | Sosial |
| `profesional` | Profesional |

## 4. Field umum pertanyaan

Setiap pertanyaan dibuat dengan tombol **Tambah Field**.

| Field | Nama data | Wajib | Definisi dan kegunaan |
|---|---|:---:|---|
| Label Field | `fields[*][label]` | Ya | Teks pertanyaan atau nama data yang dilihat peserta. Label harus mengandung huruf/angka dan harus unik dalam satu form. |
| Tipe Pertanyaan | `fields[*][tipe_field]` | Ya | Menentukan elemen input, format jawaban, opsi, dan metode scoring yang dapat dipakai. |
| Urutan | `fields[*][urutan]` | Tidak | Urutan field di dalam form. Nilai minimal 1 dan default mengikuti posisi field. |
| Auto-fill dari Data Peserta | `fields[*][autofill_source]` | Tidak | Mengisi jawaban awal dari data peserta yang sudah tersedia. Hanya didukung oleh tipe tertentu. |
| Deskripsi Pertanyaan | `fields[*][deskripsi]` | Tidak | Penjelasan konteks atau maksud pertanyaan. Juga dapat menjadi sumber bantuan scoring otomatis. |
| Placeholder | `fields[*][placeholder]` | Tidak | Teks contoh/petunjuk singkat di dalam input. |
| Bantuan / Petunjuk Tambahan | `fields[*][bantuan]` | Tidak | Petunjuk khusus untuk membantu peserta menjawab field. Dapat menjadi sumber scoring assistant. |
| Wajib diisi | `fields[*][is_required]` | Tidak | Menandai jawaban sebagai wajib. Nilai tersimpan juga di `validasi.required`. Pada UI builder saat ini field utama memakai default `false` dan kontrolnya bersifat teknis tersembunyi. |
| Aktif | `fields[*][is_active]` | Tidak | Menentukan apakah field ditampilkan dan dihitung dalam jumlah field aktif. Field baru default aktif. Pada UI builder saat ini nilainya dikelola sebagai data teknis tersembunyi. |
| ID | `fields[*][id]` | Tidak | Hanya digunakan saat edit untuk mempertahankan field database yang sudah ada. Field baru tidak memiliki ID sebelum disimpan. |

Nama field (`nama_field`) tidak diisi manual pada field utama. Sistem membuatnya
otomatis dari label menggunakan format `snake_case`. Prefix nomor soal di awal
label seperti `1. Nama` dibuang. Contoh:

```text
Label:     Nama Lengkap Peserta
nama_field: nama_lengkap_peserta
```

Nama field harus unik di dalam satu form. Nama field ini menjadi key jawaban
peserta, key auto-fill, dan referensi `parent_field` pada field dependency.

### 4.1 Data teknis yang ikut membentuk field

Beberapa nama data berikut tidak tampil sebagai input terpisah, tetapi penting
untuk memahami hasil penyimpanan dan integrasi:

| Data | Sifat | Kegunaan |
|---|---|---|
| `nama_field` | Dibuat otomatis | Key jawaban peserta dan identitas field dalam dependency. |
| `opsi_field` | Dibuat otomatis | Opsi final dalam JSON setelah opsi manual, lookup, Likert, radio, file, atau repeater dinormalisasi. |
| `nilai_default` | Teknis | Kolom database untuk nilai awal. Form Builder saat ini menyimpan `null` pada saat sinkronisasi. |
| `validasi` | Dibuat otomatis | JSON validasi minimal yang memuat `required`, `tipe_field`, dan `allow_other_input`. |
| `scoring_config` | Dibuat otomatis | JSON aturan scoring field. |
| `raw_opsi_field_json` | Payload teknis | Menjaga konfigurasi JSON khusus seperti batas file saat field diedit. |
| `forms_payload` | Input tersembunyi | JSON seluruh form yang dikirim browser; backend mengubahnya menjadi input `forms` sebelum validasi. |

Dengan demikian, `opsi_field_text`, `opsi_score_text`,
`dependency_config_text`, `repeater_config_text`, dan `raw_opsi_field_json`
adalah representasi input builder. Nama tersebut bukan kolom database utama;
hasil akhirnya disimpan ke `opsi_field`, `dependency_config`, atau
`scoring_config` sesuai konteksnya.

## 5. Tipe field yang tersedia

| Nilai `tipe_field` | Label UI | Bentuk jawaban | Kegunaan |
|---|---|---|---|
| `text` | Teks | Satu baris teks | Nama, kode, jawaban singkat, atau data bebas. |
| `textarea` | Area Teks | Teks panjang | Uraian, refleksi, analisis, catatan, dan jawaban naratif. |
| `number` | Angka | Angka | Nilai capaian, jumlah, kuantitas, atau skor yang dapat dibandingkan dengan target. |
| `email` | Email | Alamat email | Input email dengan bentuk input email. |
| `date` | Tanggal | Tanggal | Input tanggal dengan format tanggal browser. |
| `select` | Daftar Pilihan | Satu opsi | Pilihan tunggal dari opsi manual, master database, atau mapping dependency. |
| `radio` | Pilihan Ganda | Satu opsi | Pilihan tunggal dengan kode, isi jawaban, skor, dan level kompetensi per opsi. |
| `likert` | Skala Likert | Satu nilai 1–5 | Pernyataan sikap/persepsi dengan opsi Likert tetap. |
| `checkbox` | Kotak Centang | Satu atau beberapa opsi | Memungkinkan peserta memilih beberapa opsi sekaligus. |
| `file` | Unggah File | File atau link | Mengumpulkan bukti dokumen langsung atau URL bukti. |
| `repeater` | Tabel Berulang | Array baris dan kolom | Mengumpulkan banyak data berstruktur, misalnya riwayat pelatihan atau pengalaman. |

### 5.1 Field `select` dan `checkbox`

Keduanya memakai field opsi berikut:

| Field | Nama data | Definisi |
|---|---|---|
| Opsi manual | `opsi_field_text` | Daftar opsi yang dipisahkan koma atau baris baru. Wajib untuk `checkbox`, dan wajib untuk `select` jika tidak memakai lookup/dependency. |
| Skor opsi | `opsi_score_text` | Skor opsional per opsi. Format yang direkomendasikan satu baris per opsi: `Label = Skor`, misalnya `Ya = 5`. |
| Lookup opsi database | `lookup_source` | Mengambil opsi dari master data aplikasi. Hanya tersedia untuk `select`. |
| Tambahkan opsi Lainnya | `allow_other_input` | Hanya untuk `select`. Menambahkan opsi `Lainnya` dengan input teks manual. Nilai teknisnya adalah `__other_option__`. |
| Dependency | `dependency_config_text` | Membuat opsi `select` bergantung pada nilai field sebelumnya. Tidak boleh digabung dengan lookup database. |

Contoh input opsi manual:

```text
Sangat Baik
Baik
Cukup
Perlu Perbaikan
```

Setelah disimpan, opsi menjadi array seperti:

```json
[
  {"label": "Sangat Baik", "value": "Sangat Baik", "score": 5},
  {"label": "Baik", "value": "Baik", "score": 4}
]
```

`checkbox` dapat menggunakan metode scoring `choice_option_average`,
`choice_option_sum`, `choice_option_max`, atau hanya memeriksa keberadaan
jawaban dengan `presence`.

### 5.2 Field `radio` / Pilihan Ganda

Pilihan ganda wajib memiliki minimal dua opsi. Setiap opsi memiliki field:

| Field | Nama data | Wajib | Kegunaan |
|---|---|:---:|---|
| Kode | `radio_options[*][value]` | Ya | Nilai yang disimpan ketika opsi dipilih. Harus unik dalam satu field. Biasanya `A`, `B`, `C`, dan seterusnya. |
| Isi Jawaban | `radio_options[*][label]` | Ya | Teks jawaban yang dilihat peserta. |
| Skor | `radio_options[*][score]` | Tidak | Skor eksplisit opsi pada rentang 0–5. Jika dikosongkan, engine dapat menggunakan level kompetensi sebagai skor default. |
| Level Kompetensi | `radio_options[*][level_kompetensi]` | Ya | Pemetaan opsi ke level 1–5. |

Level kompetensi yang tersedia:

| Nilai | Label |
|---:|---|
| 1 | Level 1: Paham |
| 2 | Level 2: Dasar |
| 3 | Level 3: Menengah |
| 4 | Level 4: Mumpuni |
| 5 | Level 5: Ahli |

### 5.3 Field `likert` / Skala Likert

Opsi Likert dibuat otomatis dan tidak perlu diinput manual:

| Label | Value | Score |
|---|---:|---:|
| Sangat Setuju | `5` | 5 |
| Setuju | `4` | 4 |
| Cukup Setuju | `3` | 3 |
| Tidak Setuju | `2` | 2 |
| Sangat Tidak Setuju | `1` | 1 |

Pada scoring, field Likert menggunakan method `likert_scale`. Jika
**Pernyataan negatif** diaktifkan, skor dikoreksi dengan rumus `6 - X`.

### 5.4 Field `file` / Unggah File

Field file mempunyai mode input:

| Field | Nama data | Nilai | Kegunaan |
|---|---|---|---|
| Mode Input Bukti | `file_input_mode` | `file` | Peserta mengunggah file ke sistem. |
| Mode Input Bukti | `file_input_mode` | `link` | Peserta mengirim URL/link bukti. |

Konfigurasi tersimpan pada `opsi_field` dalam bentuk:

```json
{
  "input_mode": "file",
  "accept": ["pdf", "jpg", "png"],
  "max_size_kb": 5120,
  "max_files": 1
}
```

`accept`, `max_size_kb`, dan `max_files` adalah konfigurasi teknis. Mode
input tersedia langsung di UI; batas file dapat diatur melalui payload/config
programatik atau dipertahankan saat edit.

### 5.5 Field `repeater` / Tabel Berulang

Tabel berulang menyimpan konfigurasi berikut pada `repeater_config_text`, lalu
menjadi object JSON di `opsi_field`:

| Field | Definisi |
|---|---|
| `min_rows` | Minimal jumlah baris yang diharapkan terisi. |
| `max_rows` | Batas maksimal baris. Nilai `0` berarti tanpa batas. |
| `columns` | Daftar kolom tabel. Minimal satu kolom. |

Setiap item `columns` memiliki:

| Field kolom | Wajib | Kegunaan |
|---|:---:|---|
| `label` | Ya | Judul kolom yang dilihat peserta. |
| `nama_field` | Ya | Key penyimpanan nilai kolom dalam setiap baris. Harus unik dalam tabel. |
| `tipe_field` | Ya | `text`, `textarea`, `number`, `email`, `date`, `url`, atau `select`. |
| `placeholder` | Tidak | Petunjuk di input kolom. |
| `opsi_field` | Kondisional | Daftar opsi jika tipe kolom adalah `select`. |
| `is_required` | Tidak | Menentukan kolom wajib di setiap baris. |

Contoh konfigurasi tabel:

```json
{
  "min_rows": 1,
  "max_rows": 10,
  "columns": [
    {
      "label": "Nama Pelatihan",
      "nama_field": "nama_pelatihan",
      "tipe_field": "text",
      "placeholder": "Contoh: Pelatihan Pembelajaran Berdiferensiasi",
      "opsi_field": [],
      "is_required": true
    },
    {
      "label": "Tahun",
      "nama_field": "tahun",
      "tipe_field": "number",
      "placeholder": "2026",
      "opsi_field": [],
      "is_required": true
    }
  ]
}
```

### 5.6 Dependency antar-field

Dependency hanya tersedia untuk field `select`. Field child harus berada
setelah field parent dalam form.

Konfigurasi yang dihasilkan berbentuk:

```json
{
  "enabled": true,
  "parent_field": "provinsi",
  "empty_behavior": "disabled",
  "options_by_parent": {
    "Sulawesi Selatan": [
      {"label": "Makassar", "value": "Makassar"},
      {"label": "Gowa", "value": "Gowa"}
    ],
    "Jawa Barat": [
      {"label": "Bandung", "value": "Bandung"}
    ]
  }
}
```

| Field | Nilai | Kegunaan |
|---|---|---|
| `enabled` | `true` / `false` | Mengaktifkan mapping opsi child. |
| `parent_field` | Nama field sebelumnya | Field yang nilainya menjadi penentu opsi child. |
| `empty_behavior` | `disabled` / `empty` | Saat parent kosong, child dinonaktifkan atau ditampilkan tanpa opsi. |
| `options_by_parent` | Object mapping | Daftar opsi child untuk setiap nilai parent. |

Ketentuan dependency:

- parent harus ada di form yang sama;
- parent harus berada sebelum child;
- child tidak boleh bergantung pada dirinya sendiri;
- setiap group parent minimal memiliki satu opsi child;
- value opsi dalam satu group tidak boleh duplikat; dan
- dependency tidak boleh sekaligus menggunakan `lookup_source`.

## 6. Auto-fill dari data peserta

Auto-fill hanya tersedia untuk `text`, `textarea`, `number`, `email`, `date`,
`select`, `radio`, dan `checkbox`. Sumber yang dapat dipilih:

| Nilai sumber | Label |
|---|---|
| `nama_lengkap` | Nama Lengkap |
| `no_ktp` | NIK / No. KTP |
| `nip` | NIP |
| `nuptk` | NUPTK |
| `nip_nuptk` | NIP / NUPTK |
| `golongan` | Golongan |
| `jabatan` | Jabatan |
| `status_kepegawaian` | Status Kepegawaian |
| `eksternal_jabatan` | Ketenagaan |
| `jenis_jabatan` | Jenis Jabatan |
| `kategori_jabatan` | Kategori Jabatan |
| `tugas_jabatan` | Tugas Jabatan |
| `latar_jabatan` | Latar Jabatan |
| `gender` | Jenis Kelamin |
| `tempat_lahir` | Tempat Lahir |
| `tgl_lahir` | Tanggal Lahir |
| `agama` | Agama |
| `pendidikan` | Pendidikan Terakhir |
| `email` | Email |
| `no_hp` | No. HP |
| `no_wa` | No. WhatsApp |
| `satuan_pendidikan` | Satuan Pendidikan |
| `npsn_sekolah` | NPSN Sekolah |
| `kabupaten` | Kabupaten / Kota |
| `alamat_satuan` | Alamat Satuan Pendidikan |
| `alamat_rumah` | Alamat Rumah |
| `npwp` | NPWP |
| `no_rek` | No. Rekening |
| `jenis_bank` | Jenis Bank |

Sistem dapat menyarankan sumber berdasarkan label field, tetapi nilai yang
tersimpan tetap dinormalisasi ke daftar sumber di atas.

## 7. Lookup opsi dari master database

Lookup hanya tersedia untuk field `select`. Opsi diambil dari kolom `name`
pada master data yang dipilih sehingga administrator tidak perlu menyalin
opsi secara manual.

| Nilai sumber | Master data |
|---|---|
| `master_golongan` | Gabungan master golongan PNS dan PPPK |
| `master_golongan_pns` | Master golongan PNS |
| `master_golongan_pppk` | Master golongan PPPK |
| `master_status_kepegawaian` | Master status kepegawaian |
| `master_pendidikan` | Master pendidikan |
| `master_kabupaten` | Master kabupaten/kota |
| `master_satuan_pendidikan` | Master satuan pendidikan |
| `master_jabatan_umum` | Master jabatan umum |
| `master_jabatan_pendidik` | Master jabatan tenaga pendidik |
| `master_jabatan_kependidikan` | Master jabatan tenaga kependidikan |
| `master_jabatan_stakeholder` | Master jabatan stakeholder |
| `master_jenis_jabatan` | Master jenis jabatan |
| `master_tugas_jabatan` | Master tugas jabatan |
| `master_latar_jabatan` | Master latar jabatan |

Saat disimpan, opsi hasil lookup disalin ke `opsi_field` sebagai pasangan
`label` dan `value`. Jika master yang dipilih tidak memiliki data, validasi
akan menolak penyimpanan.

## 8. Pengaturan skor otomatis per field

Pengaturan ini berada pada `fields[*][scoring]` dan disimpan sebagai
`assessment_form_fields.scoring_config`.

### 8.1 Field scoring umum

| Field | Nama data | Definisi |
|---|---|---|
| Aktif | `scoring[enabled]` | Mengaktifkan scoring otomatis untuk field. |
| Cara Sistem Menilai | `scoring[method]` | Metode perhitungan yang sesuai dengan tipe field. |
| Bobot Nilai Pertanyaan | `scoring[weight]` | Bobot relatif pertanyaan. Jika kosong, pertanyaan menggunakan bobot normal/default. |
| Konteks Penilaian | `scoring[profile]` | Profil scoring yang digunakan engine. |
| Kode Rubrik / Indikator | `scoring[rubric_code]` | Referensi kode rubrik atau indikator tambahan. |
| Skala Minimum/Maksimum | `scoring[scale_min]`, `scoring[scale_max]` | Rentang skor; default umum 1–5 dan wajib 1–5 untuk Likert. |
| Aturan Lanjutan | `scoring[advanced_rules_text]` | JSON aturan tambahan untuk kebutuhan lanjutan. |

### 8.2 Metode scoring berdasarkan tipe field

| Tipe field | Method yang tersedia | Default |
|---|---|---|
| `likert` | `likert_scale`, `presence` | `likert_scale` |
| `radio`, `select` | `choice_option_score`, `presence` | `choice_option_score` |
| `checkbox` | `choice_option_average`, `choice_option_sum`, `choice_option_max`, `presence` | `choice_option_average` |
| `number` | `numeric_threshold`, `numeric_range`, `presence` | `numeric_threshold` |
| `text`, `textarea` | `semantic_similarity`, `keyword_coverage`, `presence` | `semantic_similarity` |
| `repeater` | `repeater_completeness`, `presence` | `repeater_completeness` |
| `email`, `date`, `file` | `presence` | `presence` |

### 8.3 Parameter scoring khusus

| Parameter | Nama data | Digunakan untuk | Definisi |
|---|---|---|---|
| Pernyataan negatif | `scoring[is_negative_statement]` | `likert` | Membalik skor dengan rumus `6 - X`. |
| Nilai saat jawaban ada | `scoring[score_if_answered]` | `presence` | Nilai ketika jawaban/bukti tersedia. |
| Pedoman penilaian | `scoring[reference_answer]` | `text`, `textarea`, `repeater` | Ciri jawaban atau bukti yang diharapkan. |
| Kata kunci penting | `scoring[keyword_groups_text]` | `keyword_coverage` | Kata kunci yang dipisahkan dengan koma. |
| Padanan kata | `scoring[synonym_map_text]` | Analisis teks | Format per baris, misalnya `asesmen: penilaian, evaluasi`. |
| Minimal kata | `scoring[min_words]` | Analisis teks | Saran panjang jawaban minimum. |
| Ambang keyakinan | `scoring[confidence_threshold]` | Analisis teks | Nilai 0–1 untuk tingkat keyakinan hasil analisis. |
| Review manual di bawah ambang | `scoring[manual_review_below_confidence]` | Analisis teks | Field payload yang divalidasi untuk kebutuhan lanjutan. Pada penyimpanan melalui Form Builder saat ini nilainya dinormalisasi menjadi `false`, sehingga belum mengaktifkan review manual secara mandiri. |
| Arah penilaian | `scoring[numeric_direction]` | `number` | `greater_is_better`, `lower_is_better`, atau `range`. |
| Batas angka | `scoring[min_threshold]`, `target_threshold`, `max_threshold` | `number` | Batas minimum, target/ideal, dan maksimum input. |
| Nilai hasil | `scoring[min_score]`, `target_score`, `max_score` | `number` | Skor yang dipetakan pada batas angka. |

Tombol **Isi bantuan otomatis** dapat menyusun kata kunci, padanan kata,
minimal kata, dan aturan lanjutan dari deskripsi pertanyaan, bantuan, atau
pedoman penilaian. Hasil tersebut tetap dapat diperiksa dan diubah sebelum
disimpan.

## 9. Struktur data setelah disimpan

### 9.1 Relasi database

Data disimpan pada tiga tabel utama:

```text
assessments.id
└── assessment_forms.assessment_id
    └── assessment_form_fields.assessment_form_id
```

Penghapusan parent menghapus child terkait (`cascadeOnDelete`).

### 9.2 Struktur object assessment

Secara konseptual, object yang tersimpan/diteruskan ke API memiliki bentuk:

```json
{
  "id": 101,
  "kode_assessment": "ASM-LIKERT-001",
  "judul": "Pemetaan Kompetensi Guru 2026",
  "slug": "pemetaan-kompetensi-guru-2026",
  "deskripsi": "Assessment pemetaan kompetensi guru.",
  "petunjuk": "Jawab setiap pernyataan sesuai kondisi yang sebenarnya.",
  "instrument_type": "skala_likert",
  "kategori": "assessment",
  "target_ketenagaan": "tenaga_pendidik",
  "scoring_config": {
    "profile": "skala_likert",
    "weight": 1,
    "verification_gap_threshold": 1.5,
    "empty_response_threshold_percent": 10
  },
  "status": "publish",
  "is_active": true,
  "forms": []
}
```

Pada endpoint assessment yang dipublikasikan, hanya assessment dengan
`status = publish` dan `is_active = true` yang diambil. Endpoint juga hanya
memuat form dan field yang `is_active = true`.

### 9.3 Contoh struktur satu form beserta field

Contoh berikut memperlihatkan beberapa tipe field dalam satu form. ID hanya
contoh; ID sebenarnya dibuat database.

```json
{
  "id": 201,
  "assessment_id": 101,
  "judul_form": "Profil dan Refleksi Peserta",
  "kode_form": "FORM-PROFIL-01",
  "deskripsi": "Data awal dan refleksi singkat peserta.",
  "kompetensi": "pedagogik",
  "indikator_kode": "1.1",
  "indikator_label": "Memahami karakteristik peserta didik",
  "is_scoreable": true,
  "scoring_config": {
    "profile": "skala_likert",
    "weight": 20,
    "exclude_from_competency": false
  },
  "urutan": 1,
  "is_active": true,
  "fields": [
    {
      "id": 301,
      "assessment_form_id": 201,
      "label": "Nama Lengkap",
      "deskripsi": "Nama sesuai identitas.",
      "nama_field": "nama_lengkap",
      "tipe_field": "text",
      "placeholder": "Masukkan nama lengkap",
      "bantuan": null,
      "opsi_field": null,
      "nilai_default": null,
      "autofill_source": "nama_lengkap",
      "lookup_source": null,
      "dependency_config": null,
      "validasi": {
        "required": true,
        "tipe_field": "text",
        "allow_other_input": false
      },
      "scoring_config": {
        "enabled": false
      },
      "urutan": 1,
      "is_required": true,
      "is_active": true
    },
    {
      "id": 302,
      "assessment_form_id": 201,
      "label": "Kabupaten / Kota",
      "deskripsi": null,
      "nama_field": "kabupaten_kota",
      "tipe_field": "select",
      "placeholder": "Pilih kabupaten/kota",
      "bantuan": null,
      "opsi_field": [
        {"label": "Makassar", "value": "Makassar"},
        {"label": "Gowa", "value": "Gowa"}
      ],
      "nilai_default": null,
      "autofill_source": "kabupaten",
      "lookup_source": "master_kabupaten",
      "dependency_config": null,
      "validasi": {
        "required": true,
        "tipe_field": "select",
        "allow_other_input": false
      },
      "scoring_config": {
        "enabled": false
      },
      "urutan": 2,
      "is_required": true,
      "is_active": true
    },
    {
      "id": 303,
      "assessment_form_id": 201,
      "label": "Saya merancang pembelajaran sesuai kebutuhan peserta didik",
      "deskripsi": "Pilih tingkat persetujuan Anda.",
      "nama_field": "saya_merancang_pembelajaran_sesuai_kebutuhan_peserta_didik",
      "tipe_field": "likert",
      "placeholder": null,
      "bantuan": null,
      "opsi_field": [
        {"label": "Sangat Setuju", "value": "5", "score": 5},
        {"label": "Setuju", "value": "4", "score": 4},
        {"label": "Cukup Setuju", "value": "3", "score": 3},
        {"label": "Tidak Setuju", "value": "2", "score": 2},
        {"label": "Sangat Tidak Setuju", "value": "1", "score": 1}
      ],
      "nilai_default": null,
      "autofill_source": null,
      "lookup_source": null,
      "dependency_config": null,
      "validasi": {
        "required": true,
        "tipe_field": "likert",
        "allow_other_input": false
      },
      "scoring_config": {
        "enabled": true,
        "method": "likert_scale",
        "scale_min": 1,
        "scale_max": 5,
        "is_negative_statement": false
      },
      "urutan": 3,
      "is_required": true,
      "is_active": true
    }
  ]
}
```

### 9.4 Dampak status aktif terhadap tampilan

```text
Assessment publish + aktif
└── Form aktif
    └── Field aktif
        └── Ditampilkan kepada peserta dan masuk struktur jawaban
```

Kondisi penting:

- assessment nonaktif tidak tampil pada endpoint publik/portal;
- form nonaktif tidak tampil meskipun assessment aktif;
- field nonaktif tidak tampil meskipun form aktif;
- form `is_scoreable = false` masih dapat tampil dan mengumpulkan data, tetapi
  tidak menyumbang skor kompetensi; dan
- field `is_required` mengatur kewajiban jawaban, sedangkan
  `scoring.enabled` mengatur apakah jawaban diproses oleh scoring otomatis.

## 10. Payload form builder saat submit

Halaman menggunakan input tersembunyi `forms_payload` berisi JSON. Sebelum
validasi, backend mendekode JSON tersebut menjadi input `forms`. Bentuk ringkas
payload-nya adalah:

```json
{
  "forms": [
    {
      "judul_form": "Kompetensi Pedagogik",
      "kode_form": "FORM-PED-01",
      "kompetensi": "pedagogik",
      "indikator_kode": "1.1",
      "indikator_label": "Perencanaan pembelajaran",
      "is_scoreable": true,
      "is_active": true,
      "fields": [
        {
          "label": "Saya menyusun tujuan pembelajaran yang terukur",
          "tipe_field": "likert",
          "is_required": true,
          "is_active": true,
          "scoring": {
            "enabled": true,
            "method": "likert_scale",
            "is_negative_statement": false
          }
        }
      ]
    }
  ]
}
```

Backend kemudian melakukan normalisasi berikut:

1. membuat atau mempertahankan kode assessment;
2. membuat slug dari judul;
3. membuat kode form jika kosong;
4. membuat `nama_field` dari label;
5. mengubah teks opsi menjadi `opsi_field` JSON;
6. mengubah konfigurasi dependency/repeater menjadi JSON;
7. menggabungkan `is_required` ke `validasi.required`;
8. menyimpan konfigurasi scoring; dan
9. menghapus form/field lama yang tidak lagi dikirim ketika proses edit.

## 11. Checklist sebelum menyimpan

- Judul assessment dan status sudah benar.
- Target ketenagaan sudah sesuai penerima penugasan.
- Jenis instrumen dipilih bila assessment akan memakai aturan instrumen.
- Minimal ada satu form dan satu field pada setiap form.
- Judul form, label field, dan urutannya jelas.
- Kompetensi diisi untuk setiap form yang masuk penilaian.
- Field `select` dan `checkbox` sudah memiliki opsi atau sumber yang valid.
- Pilihan ganda memiliki minimal dua opsi, kode unik, dan level kompetensi.
- Dependency memakai parent yang berada sebelum child.
- Tabel berulang memiliki minimal satu kolom, nama kolom unik, dan batas baris valid.
- Form dan field yang ingin ditampilkan sudah aktif.
- Scoring otomatis diaktifkan dan dikonfigurasi hanya pada field yang memang perlu dinilai.
