<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        @page { size: A4 landscape; margin: 0; }
        * { box-sizing: border-box; }
        html, body { width: 297mm; height: 210mm; margin: 0; }
        body { color: #111; font-family: DejaVu Sans, sans-serif; }
        .page { position: relative; width: 297mm; height: 210mm; overflow: hidden; text-align: center; }
        .background { position: absolute; inset: 0; width: 297mm; height: 210mm; }
        .content { position: absolute; top: 9mm; left: 22mm; right: 22mm; bottom: 10mm; }
        .logo { width: 19mm; height: 19mm; margin: 0 auto 1mm; }
        .institution { font-size: 14pt; font-weight: 700; line-height: 1.12; }
        .certificate-title { margin-top: 7mm; font-family: DejaVu Sans, sans-serif; font-size: 25pt; font-weight: 700; letter-spacing: .4mm; }
        .number { margin-top: 1mm; font-size: 11pt; }
        .recipient { margin: 7mm 0 2mm; font-size: 12pt; }
        .name { display: inline-block; border-bottom: 1px solid #111; font-size: 17pt; font-weight: 700; }
        .institution-name { margin-top: 3mm; font-size: 12pt; }
        .description { margin: 5mm auto 0; max-width: 235mm; font-size: 11.5pt; line-height: 1.35; }
        .activity { font-size: 12.5pt; font-weight: 700; }
        .signature { position: absolute; left: 0; right: 0; bottom: 18mm; font-size: 11pt; line-height: 1.15; }
        .signature-space { height: 17mm; }
        .signature-name { font-weight: 700; font-size: 12pt; }
    </style>
</head>
<body>
    <div class="page">
        <img class="background" src="data:image/png;base64,{{ $background }}" alt="">
        <div class="content">
            <img class="logo" src="data:image/png;base64,{{ $logo }}" alt="Lambang Kemdikdasmen">
            <div class="institution">
                KEMENTERIAN PENDIDIKAN DASAR DAN MENENGAH<br>
                BALAI BESAR GURU DAN TENAGA KEPENDIDIKAN PROVINSI SULAWESI SELATAN
            </div>

            <div class="certificate-title">SERTIFIKAT</div>
            <div class="number">Nomor : {{ $number }}</div>

            <div class="recipient">Diberikan kepada :</div>
            <div class="name">{{ $participantName }}</div>
            <div class="institution-name">INSTANSI/SEKOLAH : {{ $institution }}</div>
            <div class="description">
                telah berpartisipasi aktif sebagai <strong>{{ $participantRole }}</strong> dan dinyatakan memenuhi ketentuan
                Rencana Tindak Lanjut (RTL) pada kegiatan<br>
                <span class="activity">{{ $activityName }}</span><br>
                yang diselenggarakan oleh Balai Besar Guru dan Tenaga Kependidikan Provinsi Sulawesi Selatan,<br>
                pada tanggal {{ $activityDate }}, di {{ $activityPlace }}.
            </div>

            <div class="signature">
                Makassar, {{ $issueDate }}<br>
                Kepala Balai,
                <div class="signature-space"></div>
                <div class="signature-name"><u>Dr. Arman Agung, M.Pd.</u></div>
                NIP. 196910112001121002
            </div>
        </div>
    </div>
</body>
</html>
