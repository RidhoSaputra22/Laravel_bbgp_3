<!-- Contoh detail.blade.php -->
<?php
setlocale(LC_TIME, 'id_ID.UTF-8');

$tgl_surat = strftime('%d %B %Y', strtotime($kuitansi->tgl_surat_tugas));
?>
<p><strong>Nomor dan Tanggal Surat Tugas:</strong> <br> {{ $kuitansi->no_surat_tugas }} - {{ $tgl_surat }}</p>
<p><strong>Nama Lengkap:</strong> {{ $kuitansi->peserta->nama ?? '' }}</p>
<p><strong>NIP:</strong> {{ $kuitansi->peserta->pegawai->nip ?? '' }}</p>
<div class="row">
    <div class="col-md-6">
        <p><strong>Nomor Bukti:</strong> {{ $kuitansi->no_bukti }}</p>
        <p><strong>Nomor MAK:</strong> {{ $kuitansi->no_MAK }}</p>
        <p><strong>Jenis Angkutan:</strong> {{ $kuitansi->jenis_angkutan }}</p>
        

        <!-- Tambahkan detail lainnya sesuai kebutuhan -->
    </div>
    <div class="col-md-6">
        <p><strong>Lokasi Asal:</strong> {{ $kuitansi->kabupaten->name }}</p>
        <p><strong>Lokasi Tujuan:</strong> {{ $kuitansi->lokasi_tujuan }}</p>
        <!-- Tambahan detail lainnya -->
    </div>
</div>

<hr>
<h6>Tiket</h6>
<div class="row">
    <div class="col-md-6">
        <p><strong>Biaya Pergi:</strong> Rp.   {{ number_format($kuitansi->biaya_pergi ?? 0, 0, ',', '.') }} </p>
        <p><strong>Total Biaya:  Rp.  {{ number_format($kuitansi->total_pp ?? 0, 0, ',', '.') }}  </strong></p>
        
    </div>
    <div class="col-md-6">
        <p><strong>Biaya Pulang:</strong>  Rp.  {{ number_format($kuitansi->biaya_pulang ?? 0, 0, ',', '.') }} </p>
        <p><strong>Pajak Bandara:</strong>  Rp.  {{ number_format($kuitansi->pajak_bandara ?? 0, 0, ',', '.') }} </p>

    </div>

</div>

<hr>
<h6>Trasnport Pajak Darat / Laut / Taxi</h6>
<div class="row">
    <div class="col-md-6">
        <p><strong>Biaya Asal:</strong>  Rp. {{ number_format($kuitansi->biaya_asal ?? 0, 0, ',', '.') }} </p>
        <p><strong>Bea Jarak:</strong>  Rp. {{ number_format($kuitansi->bea_jarak ?? 0, 0, ',', '.') }} </p>
    </div>
    <div class="col-md-6">
        <p><strong>Tujuan:</strong>  Rp. {{ number_format($kuitansi->biaya_tujuan ?? 0, 0, ',', '.') }} </p>
        <p><strong>Total Transport:  Rp. {{ number_format($kuitansi->total_transport ?? 0, 0, ',', '.') }} </strong> </p>
        
    </div>

</div>
<hr>
<h6>Biaya Harian - {{ $kuitansi->jumlah_hari }} hari {{ $kuitansi->jumlah_malam  }} Malam</h6>
<div class="row">
    <div class="col-md-5">
        <p></p>
        <p><strong>Uang Harian:</strong>  Rp. {{ number_format($kuitansi->uang_harian ?? 0, 0, ',', '.') }} </p>
        <p><strong>Total Uang Harian:  Rp. {{ number_format($kuitansi->total_harian ?? 0, 0, ',', '.') }} </strong> </p>
    </div>
    <div class="col-md-7">
        <p><strong>Penginapan  :</strong>  Rp. {{ number_format($kuitansi->biaya_penginapan ?? 0, 0, ',', '.') }} </p>
        <p><strong>Total Bill {{ $kuitansi->bill_malam == 210000 ? '30%' : $kuitansi->bill_malam }} :  Rp. {{ number_format($kuitansi->bill_malam ?? 0, 0, ',', '.') }} </strong></p>
        <p><strong>Total Penginapan  :  Rp. {{ number_format($kuitansi->total_penginapan ?? 0, 0, ',', '.') }} </strong></p>
        
    </div>

</div>
<div class="row">
    <div class="col-md">
        <h5><strong>Total Diterima  :</strong>  Rp. {{ number_format($kuitansi->total_terima ?? 0, 0, ',', '.') }} </h5>
        
    </div>

</div>