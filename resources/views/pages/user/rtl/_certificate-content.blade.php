<div class="certificate-page">
    <img class="certificate-background" src="{{ $backgroundSrc }}" alt="">
    <div class="certificate-top-logos" aria-label="Logo program pendidikan">
        <img class="certificate-top-logo-quality" src="{{ $topLogoLeftSrc }}" alt="Pendidikan Bermutu">
        <img class="certificate-top-logo-ramah" src="{{ $topLogoRightSrc }}" alt="Kemendikdasmen Ramah">
    </div>
    <div class="certificate-content">
        <img class="certificate-logo" src="{{ $logoSrc }}" alt="Lambang Kemdikdasmen">
        <div class="certificate-institution">
            KEMENTERIAN PENDIDIKAN DASAR DAN MENENGAH<br>
            BALAI BESAR GURU DAN TENAGA KEPENDIDIKAN PROVINSI SULAWESI SELATAN
        </div>

        <div class="certificate-title">SERTIFIKAT</div>
        <div class="certificate-number">Nomor : <span data-certificate-preview="number">{{ $number ?? '' }}</span></div>

        <div class="certificate-recipient">Diberikan kepada :</div>
        <div class="certificate-name" data-certificate-preview="participantName">{{ $participantName ?? '' }}</div>
        <div class="certificate-institution-name">
            INSTANSI/SEKOLAH : <span data-certificate-preview="institution">{{ $institution ?? '' }}</span>
        </div>
        <div class="certificate-description">
            telah berpartisipasi aktif sebagai
            <strong data-certificate-preview="participantRole">{{ $participantRole ?? '' }}</strong> pada kegiatan<br>
            <span class="certificate-activity" data-certificate-preview="activityName">{{ $activityName ?? '' }}</span><br>
            yang diselenggarakan oleh Balai Besar Guru dan Tenaga Kependidikan Provinsi Sulawesi Selatan,<br>
            pada tanggal <span data-certificate-preview="activityDate">{{ $activityDate ?? '' }}</span>, di
            <span data-certificate-preview="activityPlace">{{ $activityPlace ?? '' }}</span>.
        </div>

        <div class="certificate-signature">
            Makassar, <span data-certificate-preview="issueDate">{{ $issueDate ?? '' }}</span><br>
            Kepala Balai,
            <div class="certificate-qr-wrap" data-certificate-qr-wrap
                style="{{ !empty($qrImageSrc ?? '') ? '' : 'display:none;' }}">
                <img class="certificate-qr" data-certificate-qr src="{{ $qrImageSrc ?? '' }}" alt="QR Code Sertifikat">
            </div>
            <div class="certificate-signature-space" data-certificate-qr-spacer
                style="{{ !empty($qrImageSrc ?? '') ? 'display:none;' : '' }}"></div>
            <div class="certificate-signature-name">
                <u data-certificate-preview="signerName">{{ $signerName ?? '' }}</u>
            </div>
            NIP. <span data-certificate-preview="signerNip">{{ $signerNip ?? '' }}</span>
        </div>
    </div>
</div>
