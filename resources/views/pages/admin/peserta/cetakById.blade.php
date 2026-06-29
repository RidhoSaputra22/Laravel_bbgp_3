<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <style>
      body {
         font-family: Arial, sans-serif;
         font-size: 11px;
      }

      .kop-surat {
         width: 100%;
         margin-bottom: 20px;
      }

      .kop-surat img {
         width: 100%;
         height: auto;
      }

      table {
         width: 100%;
         border-collapse: collapse;
      }

      th,
      td {
         padding: 8px;
         text-align: left;
      }

      th {
         background-color: #f2f2f2;
      }

      .page-break {
         page-break-after: always;
      }

      .container {
         width: 100%;
         margin: 0 auto;
         padding: 20px;
         box-sizing: border-box;
      }

      h1 {
         text-align: center;
         margin-bottom: 20px;
         font-size: 18px;
         text-transform: uppercase;
      }

      .biodata-table {
         width: 100%;
         border-collapse: collapse;
         margin-bottom: 20px;
         font-family: Arial, sans-serif;
         font-size: 14px;
         line-height: 1.6;
         margin: 0;
         padding: 0;
      }

      .biodata-table td {
         padding: 5px;
         vertical-align: top;
      }

      .signature {
         text-align: right;
         margin-top: 50px;
      }

      /* Style untuk halaman Pakta Integritas */
      .pakta-container {
         padding: 20px 40px;
         font-size: 12px;
         line-height: 1.8;
      }

      .pakta-header {
         width: 100%;
         margin-bottom: 20px;
      }

      .pakta-header img {
         width: 100%;
         height: auto;
      }

      .pakta-title {
         font-weight: bold;
         font-size: 14px;
         margin: 10px 0;
         text-align: center;
      }

      .pakta-content {
         text-align: justify;
         margin: 20px 0;
      }

      .pakta-list {
         margin-top: -20px;
         margin-left: 20px;
      }

      .pakta-list ol {
         margin: 10px 0;
         padding-left: 20px;
      }

      .pakta-list li {
         /* margin: 10px 0; */
         text-align: justify;
      }

      .pakta-identity {
         margin: 20px 0;
      }

      .pakta-identity table {
         border: none;
      }

      .pakta-identity td {
         padding: 3px 5px;
         border: none;
      }

      .pakta-footer {
         margin-top: 40px;
         text-align: right;
         padding-right: 50px;
      }

      .materai-box {
         border: 0px solid #000;
         /* width: 100px;
            height: 100px; */
         display: inline-block;
         text-align: center;
         line-height: 50px;
         margin-right: 100px !important;
         margin: 20px 0;
      }

      /* Style untuk Surat Pernyataan Sehat */
      .surat-sehat {
         margin-top: 60px;
         padding-top: 40px;
      }
   </style>
</head>

<body>
   <!-- HALAMAN 1: BIODATA PESERTA -->
   <!-- HALAMAN 1: BIODATA PESERTA -->
   <div class="kop-surat">
      <img src="img_template/kop_baru.png" alt="Kop Surat">
   </div>

   <div style="text-align: center; margin-top: 10px;">
      <?php
      setlocale(LC_TIME, 'id_ID.UTF-8');
      $tgl_lahir_val = $peserta->tgl_lahir ?? ($getById->tgl_lahir ?? null);
      $tgl_lahir = $tgl_lahir_val ? strftime('%d %B %Y', strtotime($tgl_lahir_val)) : '-';
      
      $tgl_kegiatan = $peserta->kegiatan->tgl_kegiatan ?? now();
      $tahun_kegiatan = date('Y', strtotime($tgl_kegiatan));
      ?>
      <h2 style="line-height: 1.4; padding: 0 40px;">{{ strtoupper($namaKegiatan) }}</h2>
   </div>

   <img style="position: absolute; top: -25; right: 0; width: 220px"
      src="img_template/biodata/bio-{{
         ($peserta->status_keikutpesertaan ?? 'peserta') == 'panitia' ? 'panitia' :
         (($peserta->status_keikutpesertaan ?? 'peserta') == 'narasumber' ? 'narasumber' : 'peserta')
      }}.png"
      alt="Logo Kanan">

   <div style="margin-top: -20px">
      <div class="container">
         <table cellspacing="0" cellpadding="0" border="0" style="border: none !important;" class="biodata-table">
            <tr>
               <td width="35%">1. Nama Lengkap (dengan gelar)</td>
               <td width="65%">: {{ $peserta->nama ?: ($getById->nama ?: '-') }}</td>
            </tr>
            <tr>
               <td>2. NIP</td>
               <td>: {{ $peserta->nip ?: ($getById->nip ?: '-') }}</td>
            </tr>
            <tr>
               <td>3. Pangkat & Golongan</td>
               <td>: {{ $peserta->golongan ?: ($getById->golongan ?: '-') }}</td>
            </tr>
            <tr>
               <td>4. Jabatan</td>
               <td>: {{ $peserta->jabatan ?: ($getById->jenis_jabatan ?: ($getById->jabatan ?: '-')) }}</td>
            </tr>
            <tr>
               <td>5. Mata Pelajaran yang diampu</td>
               <td>: {{ $peserta->mata_pelajaran ?: ($getById->mata_pelajaran ?: '-') }}</td>
            </tr>
            <tr>
               <td>6. Tempat & Tanggal Lahir</td>
               <td>: {{ $peserta->tempat_lahir ?: ($getById->tempat_lahir ?: '-') }}, {{ $tgl_lahir }}</td>
            </tr>
            <tr>
               <td>7. Jenis Kelamin</td>
               <td>: {{ $peserta->jkl ?: ($getById->gender ?: ($getById->jkl ?: '-')) }}</td>
            </tr>
            <tr>
               <td>8. Status</td>
               <td>: {{ $peserta->status ?: ($getById->status ?: '-') }}</td>
            </tr>
            <tr>
               <td>9. Agama</td>
               <td>: {{ $peserta->agama ?: ($getById->agama ?: '-') }}</td>
            </tr>
            <tr>
               <td>10. Pendidikan Terakhir</td>
               <td>: {{ $peserta->pendidikan ?: ($getById->pendidikan ?: '-') }}</td>
            </tr>
            <tr>
               <td>11. Nama Unit Kerja</td>
               <td>: {{ $peserta->instansi ?: ($getById->instansi ?: ($getById->unit_kerja ?: '-')) }}</td>
            </tr>
            <tr>
               <td>12. Alamat Unit Kerja</td>
               <td>: {{ $peserta->alamat ?: ($getById->alamat ?: '-') }}</td>
            </tr>
            <tr>
               <td style="padding-left: 30px;">Kabupaten/Kota</td>
               <td>: {{ $peserta->kabupaten ?: ($getById->kabupaten ?: '-') }}</td>
            </tr>

            <tr>
               <td>13. Alamat Rumah</td>
               <td>: {{ $peserta->alamat_rumah ?: ($getById->alamat_rumah ?: '-') }}</td>
            </tr>
            <tr>
               <td style="padding-left: 30px;">Kabupaten/Kota</td>
               <td>: {{ $peserta->kabupaten_rumah ?: ($getById->kabupaten_rumah ?: '-') }}</td>
            </tr>
            <tr>
               <td>14. Nomor HP / WA</td>
               <td>: {{ $peserta->no_hp ?: ($getById->no_hp ?: '-') }} / {{ $peserta->no_wa ?: ($getById->no_wa ?: '-') }}</td>
            </tr>
            <tr>
               <td>15. Alamat Email/akun belajar</td>
               <td>: {{ $peserta->email ?: ($getById->email ?: '-') }}</td>
            </tr>
            <tr>
               <td>16. NPWP</td>
               <td>: {{ $peserta->npwp ?: ($getById->npwp ?: '-') }}</td>
            </tr>
         </table>
         <footer>
            <div style="font-size: 14px; margin-top: 15px;">
               <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border: none !important;">
                  <tr>
                     <td width="60%" style="border: none !important;"></td>
                     <td width="40%" style="text-align: left; border: none !important;">
                        <p>Makassar, {{ strftime('%d %B %Y', strtotime($tgl_kegiatan)) }}</p>
                        <p style="font-weight: bold;">Peserta,</p>
                        <br><br><br>
                        <p>{{ $peserta->nama ?? ($getById->nama ?? '-') }}</p>
                        {{-- <p>NIP. {{ $peserta->nip }}</p> --}}
                     </td>
                  </tr>
               </table>
            </div>
         </footer>
      </div>
   </div>

   <!-- PAGE BREAK -->
   <div class="page-break"></div>

   <!-- HALAMAN 2: PAKTA INTEGRITAS DAN SURAT PERNYATAAN SEHAT -->
   <div class="pakta-container">
      <!-- Header dengan Logo -->
      <div class="pakta-header">
         <img src="img_template/kop_baru.png" alt="Kop Surat">
      </div>

      <!-- Judul Pakta Integritas -->
      <div class="pakta-title">
         PAKTA INTEGRITAS
      </div>
      <div class="pakta-title" style="margin-top: -10px !important; line-height: 1.4;">
         {{ strtoupper($namaKegiatan) }}<br>
         BALAI BESAR GURU DAN TENAGA KEPENDIDIKAN (BBGTK)<br>
         PROVINSI SULAWESI SELATAN<br>
         TAHUN {{ $tahun_kegiatan }}
      </div>

      <!-- Identitas Pembuat Pernyataan -->
      <div class="pakta-content" style="margin-top: -10px !important;">
         <p>Saya yang bertanda tangan dibawah ini:</p>
         <div style="margin-top: -10px !important;" class="pakta-identity">
            <table style="border: none !important;">
               <tr>
                  <td width="200" style="border: none !important;">Nama</td>
                  <td style="border: none !important;">: {{ $peserta->nama ?: ($getById->nama ?: '-') }}</td>
               </tr>
               <tr>
                  <td style="border: none !important;">Jabatan</td>
                  <td style="border: none !important;">: {{ $peserta->jabatan ?: ($getById->jenis_jabatan ?: ($getById->jabatan ?: '-')) }}</td>
               </tr>
               <tr>
                  <td style="border: none !important;">Instansi/Unit Kerja</td>
                  <td style="border: none !important;">: {{ $peserta->instansi ?: ($getById->instansi ?: ($getById->unit_kerja ?: '-')) }}</td>
               </tr>
               <tr>
                  <td style="border: none !important;">Kabupaten/Kota</td>
                  <td style="border: none !important;">: {{ $peserta->kabupaten ?: ($getById->kabupaten ?: '-') }}</td>
               </tr>
               <tr>
                  <td style="border: none !important;">Provinsi</td>
                  <td style="border: none !important;">: <strong>SULAWESI SELATAN</strong></td>
               </tr>
            </table>
         </div>

         <p style="margin-top: -10px !important;">Dengan ini menyatakan bahwa saya:</p>

         <div class="pakta-list">
            <ol>
               <li>Wajib menaati kewajiban dan menghindari larangan, menaati ketentuan peraturan Perundang
                  undangan, menunjukan integritas dan keteladanan sikap, perilaku, ucapan dan tindakan kepada
                  setiap orang, baik di dalam maupun di luar kedinasan, berdasarkan Peraturan Pemerintah Nomor 94
                  Tahun 2021 tentang Disiplin Pegawai Negeri Sipil.</li>

               <li>Bersedia mengikuti secara penuh seluruh rangkaian kegiatan, sesuai jadwal yang telah ditentukan,
                  tidak meninggalkan kegiatan kecuali dengan izin tertulis dari Pimpinan/PIC kegiatan.</li>

               <li>Akan menjaga sikap profesional, disiplin, serta berpartisipasi aktif dalam semua sesi pelatihan.
               </li>

               <li>Berkomitmen untuk menyelesaikan tugas-tugas, proyek, serta evaluasi yang diberikan selama
                  kegiatan berlangsung.</li>

               <li>Tidak membawa anggota keluarga, teman, dan/ atau siapapun dan dengan alasan apapun, ke lokasi
                  kegiatan pelatihan.</li>

               <li>Apabila melakukan pelanggaran sebagaimana yang tercantum dari angka 1 s.d 5, bersedia menerima
                  konsekuensi dan sanksi sesuai aturan/ketentuan yang berlaku.</li>
            </ol>
         </div>

         <p>Demikian Pakta Integritas ini saya buat dengan penuh kesadaran dan tanggung jawab tanpa paksaan dari
            pihak manapun.</p>
      </div>

      <!-- Footer dengan Tanda Tangan -->
      <div class="pakta-footer" style="margin-top: -50px !important;">
         <p>.............., ........................ {{ $tahun_kegiatan }}</p>
         <p>Pembuat Pernyataan,</p>
         <div class="materai-box">
            Materai Rp. 10.000
         </div>
         <div>.............................................</div>
         {{-- <p style="margin-top: 10px;">{{ $peserta->nama }}</p> --}}
      </div>

   </div>

   <!-- PAGE BREAK -->
   <div class="page-break"></div>

   <!-- HALAMAN 3: SURAT PERNYATAAN SEHAT -->
   <div class="pakta-container">
      <!-- Header dengan Logo -->
      <div class="pakta-header">
         <img src="img_template/kop_baru.png" alt="Kop Surat">
      </div>

      <div class="pakta-title">
         SURAT PERNYATAAN SEHAT
      </div>

      <div class="pakta-content">
         <p>Yang bertanda tangan di bawah ini:</p>
         <div class="pakta-identity">
            <table style="border: none !important;">
               <tr>
                  <td width="200" style="border: none !important;">Nama</td>
                  <td style="border: none !important;">: {{ $peserta->nama ?: ($getById->nama ?: '-') }}</td>
               </tr>
               <tr>
                  <td style="border: none !important;">Tempat/Tanggal Lahir</td>
                  <td style="border: none !important;">: {{ $peserta->tempat_lahir ?: ($getById->tempat_lahir ?: '-') }}, {{ $tgl_lahir }}</td>
               </tr>
               <tr>
                  <td style="border: none !important;">Instansi/Unit Kerja</td>
                  <td style="border: none !important;">: {{ $peserta->instansi ?: ($getById->instansi ?: ($getById->unit_kerja ?: '-')) }}</td>
               </tr>
               <tr>
                  <td style="border: none !important;">Alamat</td>
                  <td style="border: none !important;">: {{ $peserta->alamat ?: ($getById->alamat ?: '-') }}</td>
               </tr>
            </table>
         </div>

         <p>Dengan ini menyatakan bahwa saya dalam kondisi sehat untuk mengikuti kegiatan <i>{{ $namaKegiatan }}</i> pada waktu dan tempat yang ditetapkan.</p>

         <p>Demikian surat pernyataan sehat ini saya buat dengan sungguh-sungguh dan penuh rasa tanggung jawab.</p>
      </div>

      <!-- Footer Surat Sehat -->
      <footer class="">
         <div style="font-size: 13px; margin-top: 30px;">
            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border: none !important;">
               <tr>
                  <td width="60%" style="border: none !important;"></td>
                  <td width="40%" style="text-align: left; border: none !important;">
                     <p>.............., ........................ {{ $tahun_kegiatan }}</p>
                     <p>Pembuat pernyataan</p>
                     <br><br><br>
                     <p>{{ $peserta->nama ?: ($getById->nama ?: '-') }}</p>
                  </td>
               </tr>
            </table>
         </div>
      </footer>
   </div>

</body>

</html>
