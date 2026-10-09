# Sistematika Penilaian Monitoring / Observasi / Eviden

## Export dan import struktur assessment

Struktur assessment dapat dipindahkan melalui menu **Export JSON** dan
**Validasi & Import** pada halaman Assessment. Formatnya mempertahankan pola
API assessment yang sudah digunakan project (`meta` dan `data`) dengan schema
`database-assessment-v1`.

```json
{
  "meta": {
    "schema": "database-assessment-v1",
    "version": 1,
    "export_type": "assessment-package",
    "streaming": true
  },
  "data": {
    "kode_assessment": "ASM-MOE-2026",
    "judul": "Monitoring Observasi Eviden",
    "instrument_type": "monitoring_observasi_eviden",
    "target_ketenagaan": "tenaga_pendidik",
    "target_jabatan": ["__all__"],
    "scoring_config": {
      "profile": "monitoring_observasi_eviden",
      "weight": 0.2,
      "verification_gap_threshold": 1.5
    },
    "status": "draft",
    "is_active": true,
    "forms": [
      {
        "kode_form": "FORM-01",
        "judul_form": "Bukti Pelaksanaan",
        "kompetensi": "profesional",
        "is_scoreable": true,
        "scoring_config": {"weight": 1},
        "urutan": 1,
        "is_active": true,
        "fields": [
          {
            "label": "Keterangan bukti",
            "nama_field": "keterangan_bukti",
            "tipe_field": "textarea",
            "validasi": {"required": true},
            "scoring_config": {
              "enabled": true,
              "method": "keyword_coverage",
              "weight": 1
            },
            "dependency_config": null,
            "opsi_field": null,
            "urutan": 1,
            "is_required": true,
            "is_active": true
          }
        ]
      }
    ]
  },
  "included": {
    "assignment_configs": [
      {
        "kode_penugasan": "TUGAS-MOE-2026",
        "judul_penugasan": "Penugasan Monitoring",
        "security_config": {
          "enabled": true,
          "lock_mode": "strict",
          "max_serious_violations": 3
        },
        "assessments": [
          {"kode_assessment": "ASM-MOE-2026", "urutan": 1, "stage_config": {}}
        ],
        "sessions": []
      }
    ]
  }
}
```

`data` memuat seluruh konfigurasi assessment, form, pertanyaan, opsi,
validasi, autofill, lookup, dependency, serta scoring pada level assessment,
form, dan field. `included.assignment_configs` memuat konfigurasi penugasan,
stage, sesi, dan security guard. ID database tidak dipakai sebagai referensi
import; kode assessment/form/penugasan menjadi identitas portable.

Sebelum job dibuat, file dibaca streaming dan divalidasi penuh: schema, tipe
field, properti yang dikenali, field wajib, nama field ganda, scoring, stage,
dan security config. Setelah valid, file disimpan sementara dan job pada queue
default dipanggil. Job membaca ulang file secara streaming,
melakukan upsert berdasarkan `kode_assessment`, mengganti struktur form/soal,
memulihkan konfigurasi penugasan tanpa target peserta, lalu menyegarkan
kombinasi assessment.

Data runtime berikut sengaja tidak dipindahkan: target peserta, attempt,
jawaban, file jawaban, dan event pelanggaran keamanan. Jalankan worker untuk
memproses import:

```bash
php artisan queue:work
```

## Tujuan

Monitoring, Observasi, dan Eviden digunakan untuk menilai pelaksanaan kegiatan, perilaku yang diamati, capaian angka, kelengkapan bukti, serta kualitas catatan lapangan. Rubriknya disesuaikan dengan bentuk bukti pada setiap form.

Bobot default instrumen ini dalam gabungan penilaian adalah 20%.

## Bentuk penilaian

| Bentuk bukti | Cara penilaian |
|---|---|
| Kehadiran jawaban/dokumen | Nilai diberikan jika bukti tersedia |
| Pilihan tunggal | Menggunakan skor pilihan yang dipilih |
| Pilihan jamak | Menggunakan rata-rata, jumlah, nilai tertinggi, atau terendah sesuai rubrik |
| Skala Likert | Nilai 1–5; pernyataan negatif dibalik dengan rumus `6 - X` |
| Capaian angka | Dibandingkan dengan target atau rentang ideal |
| Catatan naratif | Dinilai dari kesesuaian isi, kata kunci, struktur, dan kecukupan uraian |
| Tabel bukti | Dinilai dari jumlah data, kelengkapan kolom, kekayaan uraian, dan kata kunci |

## Penilaian berdasarkan keberadaan bukti

Metode ini digunakan bila yang dinilai hanya ada atau tidaknya jawaban/dokumen.

- Bukti tersedia: memperoleh nilai yang telah ditetapkan; jika tidak ditetapkan, menggunakan nilai maksimum.
- Bukti tidak tersedia: tidak memperoleh skor pada pengumpulan normal atau memperoleh skor 0 ketika waktu habis.

Metode ini tidak menilai keaslian maupun kualitas isi dokumen.

## Penilaian pilihan jawaban

Setiap pilihan dapat diberi skor tertentu. Untuk pilihan jamak, rubrik dapat menggunakan:

| Cara agregasi | Perhitungan |
|---|---|
| Rata-rata | Jumlah skor pilihan dibagi jumlah pilihan yang dinilai |
| Jumlah | Seluruh skor pilihan dijumlahkan |
| Maksimum | Mengambil skor tertinggi |
| Minimum | Mengambil skor terendah |

Jika jumlah skor mempunyai batas maksimum, nilai dikonversi secara proporsional ke skala penilaian.

## Penilaian angka

### Semakin tinggi semakin baik

- Nilai pada atau di bawah batas minimum memperoleh skor minimum.
- Nilai antara batas minimum dan target meningkat secara proporsional.
- Nilai antara target dan batas maksimum meningkat hingga skor maksimum.
- Nilai pada atau di atas batas maksimum memperoleh skor maksimum.

### Semakin rendah semakin baik

- Nilai pada atau di bawah batas terbaik memperoleh skor maksimum.
- Nilai meningkat menuju target menyebabkan skor menurun secara proporsional.
- Nilai pada atau di atas batas terburuk memperoleh skor minimum.

### Rentang ideal

- Nilai di dalam rentang ideal memperoleh skor maksimum.
- Nilai di luar rentang mengalami penurunan skor berdasarkan jaraknya dari batas terdekat.
- Nilai yang melewati batas toleransi memperoleh skor minimum.

## Penilaian catatan naratif

Catatan observasi atau uraian eviden dapat dinilai melalui:

| Unsur | Bobot analisis |
|---|---:|
| Kesesuaian dengan acuan | 28% |
| Cakupan kata kunci | 28% |
| Cakupan frasa penting | 16% |
| Struktur analisis, strategi, dan evaluasi | 14% |
| Kecukupan panjang uraian | 8% |
| Istilah pendukung | 6% |

Jika tidak tersedia acuan dan kata kunci, uraian hanya dinilai berdasarkan keberadaannya.

## Penilaian tabel bukti

| Komponen | Bobot |
|---|---:|
| Kecukupan jumlah baris | 35% |
| Kelengkapan kolom wajib | 35% |
| Kekayaan isi teks | 15% |
| Cakupan kata kunci | 15% |

```text
Nilai tabel =
(35% × kecukupan baris)
+ (35% × kelengkapan wajib)
+ (15% × kekayaan isi)
+ (15% × cakupan kata kunci)
```

Hasilnya dikonversi ke skala nilai yang digunakan oleh form.

## Tahapan perhitungan

1. Tentukan bentuk bukti dan rubrik yang sesuai.
2. Nilai setiap komponen menggunakan metode yang ditetapkan.
3. Gabungkan nilai komponen menjadi skor bagian berdasarkan bobot masing-masing.
4. Gabungkan bagian yang berada dalam indikator yang sama.
5. Gabungkan indikator menjadi skor instrumen per kompetensi.
6. Gabungkan skor instrumen ini dengan instrumen lain menggunakan bobot default 20%.
7. Hitung skor keseluruhan dari rata-rata kompetensi yang tersedia.

Jika instrumen lain tidak tersedia, bobot instrumen yang tersedia disesuaikan secara proporsional hingga berjumlah 100%.

## Persyaratan pengelompokan

Agar hasil dapat masuk ke skor kompetensi, setiap bagian penilaian perlu:

- ditandai sebagai bagian yang dinilai;
- dipetakan ke kompetensi Pedagogik, Kepribadian, Sosial, atau Profesional;
- memiliki indikator yang jelas; dan
- memiliki bobot komponen dan bagian yang sesuai dengan kepentingannya.

Bagian yang hanya bersifat informasi dapat dikeluarkan dari skor kompetensi.

## Jawaban kosong dan verifikasi

- Pada pengumpulan normal, komponen kosong tidak dimasukkan ke rata-rata.
- Jika waktu habis, komponen kosong diberi skor 0.
- Bukti dokumen tetap perlu diperiksa keaslian, relevansi, kelengkapan, dan keterlacakannya.
- Selisih hasil antarinstrumen sekurang-kurangnya 1,50 poin menandakan perlunya verifikasi.
- Penandaan verifikasi tidak mengubah skor secara otomatis.

## Interpretasi hasil

| Rentang skor | Level | Makna umum |
|---:|---|---|
| `1,00–1,79` | Paham | Bukti atau pelaksanaan masih pada tahap awal |
| `1,80–2,59` | Dasar | Bukti menunjukkan penerapan dasar |
| `2,60–3,39` | Menengah | Pelaksanaan cukup konsisten dan relevan |
| `3,40–4,19` | Mumpuni | Pelaksanaan matang, terukur, dan adaptif |
| `4,20–5,00` | Ahli | Pelaksanaan berdampak, sistemik, dan berkelanjutan |

Skor di bawah 1 tidak diberi level kompetensi.

## Hasil akhir

Hasil penilaian menampilkan skor setiap bukti, skor bagian dan indikator, skor per kompetensi, persentase `(skor / 5) × 100%`, level kompetensi, serta catatan kebutuhan verifikasi.
