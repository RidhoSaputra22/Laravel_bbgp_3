# Sistematika Penilaian Validasi Ahli

## Tujuan

Validasi Ahli digunakan untuk menilai kelayakan Instrumen Pemetaan Kompetensi Guru berdasarkan aspek desain instrumen, konten/substansi, dan kebahasaan. Penilaian dilakukan oleh validator melalui modul Validator Assessment yang terpisah dari penilaian peserta.

## Skala penilaian

Setiap indikator diberi skor langsung pada skala Likert 1–5:

| Skor | Kategori |
|---:|---|
| 1 | Sangat Tidak Sesuai |
| 2 | Tidak Sesuai |
| 3 | Cukup Sesuai |
| 4 | Sesuai |
| 5 | Sangat Sesuai |

Nilai jawaban digunakan langsung sebagai skor butir. Tidak ada pembalikan skor, koreksi pernyataan negatif, atau penilaian benar-salah.

## Struktur penilaian

| Aspek | Kode form | Jumlah butir | Skor minimum | Skor maksimum |
|---|---|---:|---:|---:|
| Desain instrumen | `FORM-VALIDASI-DESAIN` | 10 | 10 | 50 |
| Konten/substansi | `FORM-VALIDASI-KONTEN` | 15 | 15 | 75 |
| Kebahasaan | `FORM-VALIDASI-BAHASA` | 10 | 10 | 50 |
| **Total** |  | **35** | **35** | **175** |

### Aspek desain instrumen

Menilai kerapian, format, sistematika, petunjuk, urutan indikator, layout, konsistensi skala, identitas, penomoran, dan kemudahan pelaksanaan asesmen.

### Aspek konten/substansi

Menilai kesesuaian dengan tujuan pemetaan kompetensi guru, keterwakilan indikator, kemampuan mengukur kompetensi, relevansi dengan tugas profesional, ketidak-tumpangtindihan, fokus butir, bebas bias, kedalaman materi, serta dukungan terhadap pengambilan keputusan pengembangan kompetensi.

### Aspek kebahasaan

Menilai kaidah Bahasa Indonesia, keterpahaman kalimat, ketepatan pilihan kata, kejelasan makna, keefektifan kalimat, konsistensi istilah, kesesuaian bahasa dengan responden, ejaan, tanda baca, dan sifat komunikatif redaksi.

## Tahapan perhitungan

1. Setiap jawaban indikator dikonversi langsung menjadi skor 1–5.
2. Skor setiap aspek dihitung dengan menjumlahkan seluruh skor butir pada aspek tersebut.
3. Skor total dihitung dengan menjumlahkan skor ketiga aspek.
4. Persentase kelayakan dihitung terhadap skor maksimum 175.

```text
Skor aspek = jumlah skor seluruh butir pada aspek

Skor total = skor desain + skor konten/substansi + skor kebahasaan

Persentase kelayakan = (skor total / 175) × 100%
```

Setiap butir memiliki bobot yang sama. Form identitas validator serta form saran dan rekomendasi tidak masuk ke perhitungan skor.

## Interpretasi persentase

| Persentase | Kategori kelayakan |
|---:|---|
| `86–100%` | Sangat Valid |
| `71–85%` | Valid |
| `56–70%` | Cukup Valid |
| `41–55%` | Kurang Valid |
| `0–40%` | Tidak Valid |

Kategori hasil digunakan bersama rekomendasi dan catatan validator. Kategori tidak menggantikan pertimbangan substantif terhadap saran perbaikan pada setiap aspek.

## Komponen nonskor

Validator juga mengisi:

- identitas validator, jabatan/profesi, instansi, bidang keahlian, dan tanggal validasi;
- saran dan masukan untuk aspek desain, konten/substansi, dan kebahasaan;
- rekomendasi akhir;
- catatan akhir validator; dan
- data pengesahan, termasuk nama, tanda tangan, dan tanggal pengesahan.

Komponen tersebut disimpan sebagai data pendukung dan tidak menambah skor numerik.

## Rekomendasi akhir

Validator memilih salah satu rekomendasi berikut:

- Dapat digunakan tanpa revisi;
- Dapat digunakan dengan revisi kecil;
- Dapat digunakan dengan revisi besar; atau
- Belum layak digunakan.

Rekomendasi akhir wajib dipilih ketika validasi dikirim. Hasil yang sudah dikirim dikunci dan menyimpan skor total, skor maksimum, persentase, rekomendasi, serta catatan akhir.

## Jawaban kosong

- Seluruh indikator penilaian wajib diisi sebelum validasi dikirim.
- Jawaban nonnumerik atau jawaban di luar rentang valid tidak menghasilkan skor.
- Skor setiap butir dibatasi pada nilai minimum 0 dan maksimum sesuai `max_score`, yaitu 5 untuk indikator validasi.

## Engine penilaian

Validator Assessment menggunakan engine internal berbasis aturan dengan metode **direct scale**. Jalur ini menggunakan `ValidatorAssignmentService`, bukan `AssessmentScoringService` yang digunakan untuk assessment peserta.

Perhitungan aktual pada saat submit adalah penjumlahan skor numerik setiap respons dan konversi ke persentase berdasarkan skor maksimum seluruh field yang dinilai.

## Hasil akhir

Hasil validasi menampilkan skor tiap indikator, skor per aspek, skor total dari maksimum 175, persentase kelayakan, rekomendasi akhir, serta saran dan catatan validator.
