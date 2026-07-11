<?php

use App\Http\Controllers\Assessment\AuthController as AssessmentPortalAuthController;
use App\Http\Controllers\Assessment\PortalController as AssessmentPortalController;
use App\Http\Controllers\PenyewaanRuanganController;
use App\Http\Controllers\RtlController;
use App\Http\Controllers\SekolahController as AdminSekolahController;
use App\Http\Controllers\User\SekolahController as UserSekolahController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

//  User
Route::group(
    ['prefix' => '', 'namespace' => 'App\Http\Controllers\User'],
    function () {
        Route::redirect('/', '/');
        // Dashboard

        Route::get('/', 'UserController@index')->name('user.index');
        Route::get('/kontak', 'UserController@kontak')->name('user.kontak');
        Route::get('/eksternal', 'UserController@guru')->name('user.guru');
        Route::get('/dataguru', 'UserController@dataguru')->name('user.guru.data');
        Route::get('/cari', 'UserController@cari')->name('user.cari.guru');

        Route::get('/detail/{jenis}/{id}', 'UserController@detail')->name('user.detail.post');

        // analisis kebutuhan pelatihan
        Route::get('/analisis-kebutuhan-pelatihan', 'UserController@analisisPelatihan')->name('user.analisisPelatihan');
        Route::get('/analisis-kebutuhan-slb', 'UserController@analisisSLB')->name('user.analisisSLB');
        Route::get('/monitoring-evaluasi-kegiatan', 'UserController@monitoring')->name('user.monitoring');
        Route::get('/pengaduan', 'UserController@pengaduan')->name('user.pengaduan');
        Route::get('/penyewaan-ruangan', [PenyewaanRuanganController::class, 'landing'])->name('penyewaan.landing');
        Route::get('/buletin-diksi', 'UserController@buletin')->name('user.buletin-diksi');
        Route::get('/lab-virtual', 'UserController@labVirtual')->name('user.lab-virtual');

        Route::get('/pegawai', 'UserController@pegawai')->name('user.pegawai');
        Route::get('/pegawai/form', 'UserController@form_pegawai')->name('user.form_pegawai');
        Route::post('/pegawai/daftar', 'UserController@daftar_pegawai')->name('user.daftar_pegawai');
        Route::get('/pegawai/all', 'UserController@getPenugasanAll')->name('user.pegawai.all');
        Route::get('/pegawai/detail', 'UserController@getPenugasanDetail')->name('user.pegawai.detail');
        Route::get('/pegawai/detailLoka', 'UserController@getPenugasanDetailLoka')->name('user.pegawai.detail.loka');
        Route::get('/pegawai/detailEksternal', 'UserController@getPenugasanDetailEksternal')->name('user.pegawai.detail.eksternal');

        Route::get('/statistik', 'UserController@statistik')->name('user.statistik');
        Route::get('/api/statistics/month/{month}', 'UserController@getMonthStatistics')->name('user.statistik.month');
        Route::get('/api/statistics/activities/{month}', 'UserController@getActivitiesByMonth')->name('user.statistik.activities.month');
        Route::get('/api/statistics/activity/{activityId}/{participantType}', 'UserController@getActivityStatistics')->name('user.statistik.activity');

        // Menu Sekolah
        Route::get('/data-sekolah', 'SekolahController@index')->name('user.data-sekolah');
        Route::post('/data-sekolah', 'SekolahController@store')->name('user.store.data-sekolah');
        Route::get('/data-sekolah/{id}', 'SekolahController@show')->name('show.data-sekolah');
        Route::get('/data-sekolah/{id}/edit', 'SekolahController@edit')->name('edit.data-sekolah');
        Route::put('/data-sekolah/{id}', 'SekolahController@update')->name('update.data-sekolah');

        Route::get('/eksternal/form/{jenis}', 'UserController@form_guru')->name('user.form_guru');
        Route::post('/eksternal/daftar', 'UserController@daftar_guru')->name('user.daftar_guru');

        Route::get('/kegiatan', 'KegiatanController@index')->name('user.kegiatan');
        Route::get('/kegiatan/cari', 'KegiatanController@cari')->name('user.cari');

        Route::get('/kegiatan/registrasi', 'KegiatanController@regist')->name('user.kegiatan_regist');
        Route::post('/kegiatan/store', 'KegiatanController@store')->name('user.kegiatan_store');

        // response json
        Route::get('/kegiatan/getStatus', 'KegiatanController@getStatus')->name('user.kegiatan.getStatus');
        Route::get('/kegiatan/cariPeserta', 'KegiatanController@cariPeserta')->name('user.kegiatan.cariPeserta');
        Route::get('/kegiatan/peserta', 'KegiatanController@getPesertaByKegiatan')->name('user.kegiatan.peserta');
        Route::get('/peserta/detail', 'KegiatanController@getPesertaDetail')->name('user.peserta.detail');

        // trace pesrta dari kegiatan sebelum nya
        Route::get('/peserta/cekData', 'KegiatanController@cekDataPeserta')->name('user.peserta.cekData');

        Route::get('/print/absensi-peserta', 'KegiatanController@printAbsensiPeserta')->name('print.absensi.peserta');
        Route::get('/print/registrasi-peserta', 'KegiatanController@printRegistrasiPeserta')->name('print.registrasi.peserta');
        Route::get('/print/absensi-panitia', 'KegiatanController@printAbsensiPanitia')->name('print.absensi.panitia');
        Route::get('/print/absensi-narasumber', 'KegiatanController@printAbsensiNarasumber')->name('print.absensi.narasumber');

        Route::get('/print/absensi-tp', 'KegiatanController@printAbsensiTp')->name('print.absensi.tp');
        Route::get('/print/absensi-tkp', 'KegiatanController@printAbsensiTkp')->name('print.absensi.tkp');
        Route::get('/print/absensi-stk', 'KegiatanController@printAbsensiStk')->name('print.absensi.stk');
        Route::get('/print/absensi-pgw', 'KegiatanController@printAbsensiPgw')->name('print.absensi.pgw');

        // RTL User
        Route::prefix('rtl')->group(function () {
            Route::get('/', [RtlController::class, 'index'])->name('user.rtl.index');
            Route::post('/store', [RtlController::class, 'store'])->name('user.rtl.store');
            Route::post('/reupload/{id}', [RtlController::class, 'reupload'])->name('user.rtl.reupload');
            Route::get('/{id}', [RtlController::class, 'show'])->name('user.rtl.show');
        });
    }
);

Route::prefix('assessment')
    ->name('assessment.portal.')
    ->group(function () {
        Route::get('/', [AssessmentPortalController::class, 'landing'])->name('index');
        Route::get('/result/{id}', [AssessmentPortalController::class, 'result'])->name('result');
        Route::get('/result/{id}/download', [AssessmentPortalController::class, 'downloadResultPdf'])->name('result.download');
        Route::get('/auth', [AssessmentPortalAuthController::class, 'showLoginForm'])->name('auth');
        Route::post('/auth', [AssessmentPortalAuthController::class, 'login'])->name('login');

        Route::middleware('assessment.portal')->group(function () {
            Route::get('/dashboard', [AssessmentPortalController::class, 'dashboard'])->name('dashboard');
            Route::post('/show/{id}/start', [AssessmentPortalController::class, 'start'])->name('start');
            Route::get('/show/{id}', [AssessmentPortalController::class, 'show'])->name('show');
            Route::post('/show/{id}/autosave', [AssessmentPortalController::class, 'autosave'])->name('autosave');
            Route::post('/show/{id}/security/violation', [AssessmentPortalController::class, 'securityViolation'])->name('security.violation');
            Route::post('/show/{id}/security/disqualify', [AssessmentPortalController::class, 'securityDisqualify'])->name('security.disqualify');
            Route::post('/show/{id}/submit', [AssessmentPortalController::class, 'submit'])->name('submit');
            Route::post('/logout', [AssessmentPortalAuthController::class, 'logout'])->name('logout');
        });
    });

//  Admin
Route::group(
    ['prefix' => '', 'namespace' => 'App\Http\Controllers', 'middleware' => 'ValidasiUser'],
    function () {
        Route::redirect('/admin', 'dashboard/');
        // Dashboard
        Route::prefix('dashboard')->group(function () {

            // Root
            Route::get('/', 'AdminController@index')->name('dashboard');
            Route::get('/jadwalKegiatan', 'AdminController@jadwal')->name('dashboard.jadwal');
            Route::get('/jadwalKegiatan/{nik}', 'AdminController@getJadwalByPegawai')->name('dashboard.jadwal.getByPegawai');

            Route::get('/getByKegiatan', 'AdminController@getByKegiatan')->name('dashboard.jadwal.getByKegiatan')->withoutMiddleware(['ValidasiUser']);
            Route::get('/getByKegiatanUser', 'AdminController@getByKegiatanUser')->name('dashboard.jadwal.getByKegiatanUser')->withoutMiddleware(['ValidasiUser']);

            // Profile User yang Login
            Route::get('/profile/{id}', 'AdminController@profile')->name('profile.index');
            Route::put('/profile/update', 'AdminController@profile_update')->name('profile.update');

            Route::get('/fetch-sekolah', 'GuruController@fetchSekolah')->name('fetchSekolah');

            // Guru / Eksternal
            Route::prefix('eksternal')->group(function () {
                Route::get('/', 'GuruController@index')->name('guru.index');
                Route::get('/create', 'GuruController@create')->name('guru.create');
                Route::post('/store', 'GuruController@store')->name('guru.store');
                Route::post('/verifikasi/{id}', 'GuruController@verifikasi')->name('guru.verifikasi');
                Route::get('/edit/{id}', 'GuruController@edit')->name('guru.edit');
                Route::put('/update', 'GuruController@update')->name('guru.update');
                Route::post('/hapus/{id}', 'GuruController@destroy')->name('guru.hapus');

                Route::get('/export', 'GuruController@export')->name('guru.export');
                Route::get('/export/{id}', 'GuruController@exportByUser')->name('guru.export.user');

                // untuk login ekternal by user
                Route::get('/detail', 'GuruController@getDetail')->name('admin.eksternal.detail');
                Route::get('/show/{id}', 'GuruController@show')->name('guru.show');
                Route::get('/editByUser/{id}', 'GuruController@editByUser')->name('guru.edit.user');
                Route::put('/updateByUser', 'GuruController@updateByUser')->name('guru.update.user');
                Route::get('/cari', 'GuruController@cari')->name('guru.cari');

                Route::get('/data-sekolah/{id}', [UserSekolahController::class, 'show'])->name('user.show.data-sekolah');
                Route::get('/data-sekolah/{id}/edit', [UserSekolahController::class, 'edit'])->name('user.edit.data-sekolah');
                Route::put('/data-sekolah/{id}', [UserSekolahController::class, 'update'])->name('user.update.data-sekolah');

                Route::get('/sekolah', [AdminSekolahController::class, 'index'])->name('admin.data-sekolah.index');
                Route::get('/sekolah/export', [AdminSekolahController::class, 'export'])->name('admin.data-sekolah.export');
                Route::get('/sekolah/{id}', [AdminSekolahController::class, 'edit'])->name('admin.data-sekolah.edit');

            });

            // Pegawai
            Route::prefix('pegawai')->group(function () {
                Route::get('/', 'PegawaiController@index')->name('pegawai.index');
                Route::get('/create', 'PegawaiController@create')->name('pegawai.create');
                Route::post('/store', 'PegawaiController@store')->name('pegawai.store');
                Route::post('/verifikasi/{id}', 'PegawaiController@verifikasi')->name('pegawai.verifikasi');
                Route::get('/show', 'PegawaiController@showPegawai')->name('admin.pegawai.detail');
                Route::get('/edit/{id}', 'PegawaiController@edit')->name('pegawai.edit');
                Route::put('/update', 'PegawaiController@update')->name('pegawai.update');
                Route::post('/hapus/{id}', 'PegawaiController@destroy')->name('pegawai.hapus');

                // untuk login pegawai by user
                Route::get('/{id}', 'PegawaiController@show')->name('pegawai.show');
                Route::get('/lokakarya/show', 'PegawaiController@showDetailLokakarya')->name('pegawai.show.lokakarya');
                Route::post('/lokakarya', 'InternalController@storeLokakaryaPegawai')->name('pegawai.lokakarya');

                Route::get('/editUser/{id}', 'PegawaiController@editUser')->name('pegawai.edit.user');
                Route::put('/updateUser', 'PegawaiController@updateUser')->name('pegawai.update.user');
                Route::get('/detailUser', 'PegawaiController@detailUser')->name('pegawai.detail.user');

                Route::get('/penugasan/{id}', 'PegawaiController@editPenugasan')->name('pegawai.editPenugasan');
                Route::get('/pendamping/{id}', 'PegawaiController@editPendamping')->name('pegawai.editPendamping');
            });

            // Berkas
            Route::prefix('berkas')->group(function () {
                Route::get('/', 'BerkasController@index')->name('berkas.index');
                Route::get('/create', 'BerkasController@create')->name('berkas.create');
                Route::post('/store', 'BerkasController@store')->name('berkas.store');
                Route::get('/edit/{id}', 'BerkasController@edit')->name('berkas.edit');
                Route::put('/update', 'BerkasController@update')->name('berkas.update');
                Route::post('/hapus/{id}', 'BerkasController@destroy')->name('berkas.hapus');
                Route::get('/verify/{id}', 'BerkasController@verify')->name('berkas.verify');
            });

            // Kepegawaian
            Route::prefix('kepegawaian')->group(function () {
                Route::get('/', 'KepegawaianController@index')->name('kepegawaian.index');
                Route::get('/create', 'KepegawaianController@create')->name('kepegawaian.create');
                Route::post('/store', 'KepegawaianController@store')->name('kepegawaian.store');
                Route::get('/edit/{id}', 'KepegawaianController@edit')->name('kepegawaian.edit');
                Route::put('/update', 'KepegawaianController@update')->name('kepegawaian.update');
                Route::post('/hapus/{id}', 'KepegawaianController@destroy')->name('kepegawaian.hapus');
            });

            // Kepegawaian
            Route::prefix('penyewaan')->group(function () {
                Route::get('/', 'PenyewaanRuanganController@index')->name('penyewaan.index');
                Route::get('/create', 'PenyewaanRuanganController@create')->name('penyewaan.create');
                Route::post('/store', 'PenyewaanRuanganController@store')->name('penyewaan.store');
                Route::get('/show/{id}', 'PenyewaanRuanganController@show')->name('penyewaan.show');
                Route::get('/edit/{id}', 'PenyewaanRuanganController@edit')->name('penyewaan.edit');
                Route::put('/update/{id}', 'PenyewaanRuanganController@update')->name('penyewaan.update');
                Route::post('/hapus/{id}', 'PenyewaanRuanganController@destroy')->name('penyewaan.hapus');

            });

            // Kepegawaian
            Route::prefix('kependidikan')->group(function () {
                Route::get('/', 'KependidikanController@index')->name('kependidikan.index');
                Route::get('/create', 'KependidikanController@create')->name('kependidikan.create');
                Route::post('/store', 'KependidikanController@store')->name('kependidikan.store');
                Route::get('/edit/{id}', 'KependidikanController@edit')->name('kependidikan.edit');
                Route::put('/update', 'KependidikanController@update')->name('kependidikan.update');
                Route::post('/hapus/{id}', 'KependidikanController@destroy')->name('kependidikan.hapus');
            });

            // Internal
            Route::prefix('internal')->group(function () {
                Route::get('/', 'InternalController@index')->name('internal.index');

                Route::get('/calendar', 'InternalController@calendar')->name('internal.calendar');
                Route::get('/getCalendar', 'InternalController@getCalendarData')->name('internal.getCalendarData');

                // untuk tampil berdasar dari id pegawai
                Route::get('/{id_pegawai}', 'InternalController@show')->name('internal.show');

                Route::get('/tabel/{jenis}', 'InternalController@get_tabel')->name('internal.tabel');
                // Route::get('/tabel/ppnpn', 'InternalController@get_tabel')->name('internal.tabel.ppnpn');
                // Route::get('/tabel/lokakarya', 'InternalController@get_tabel')->name('internal.tabel.lokakarya');
                Route::post('/verifikasi/{id}', 'InternalController@verifikasi')->name('internal.verifikasi');

                // Khusus Loka karya
                Route::get('/indexLokakarya/{nik}', 'InternalController@indexLokakarya')->name('internal.index.lokakarya');
                Route::get('/createLokakarya/{id}', 'InternalController@createLokakarya')->name('internal.create.lokakarya');
                Route::post('/storeLokakarya', 'InternalController@storeLokakarya')->name('internal.store.lokakarya');
                Route::get('/editLokakarya/{id}', 'InternalController@editLokakarya')->name('internal.edit.lokakarya');
                Route::post('/updateLokakarya', 'InternalController@updateLokakarya')->name('internal.update.lokakarya');
                Route::post('/updateLokakaryaJS', 'InternalController@updateLokakaryaJS')->name('internal.update.lokakaryaJS');
                Route::get('/jadwalLokakarya/{id}', 'InternalController@jadwalLokakarya')->name('internal.jadwal.lokakarya');
                Route::post('/cariLokakarya', 'InternalController@cariLokakarya')->name('internal.cari.lokakarya');

                Route::post('/hapusLoka/{id}', 'InternalController@hapusLoka')->name('internal.hapus.loka');

                // Penugasan PEgawai BBGP
                Route::get('/indexPegawai/{nik}', 'InternalController@indexPegawai')->name('internal.index.pegawai');
                Route::get('/createPegawai/{id}', 'InternalController@createPegawai')->name('internal.create.pegawai');
                Route::post('/storePegawai', 'InternalController@storePegawai')->name('internal.store.pegawai');
                Route::get('/editPegawai/{id}', 'InternalController@editPegawai')->name('internal.edit.pegawai');
                Route::post('/updatePegawai', 'InternalController@updatePegawai')->name('internal.update.pegawai.post');
                Route::post('/updatePegawaiAll', 'InternalController@updatePegawaiAll')->name('updateAllEmployees');

                Route::post('/hapusPegawai/{id}', 'InternalController@hapusPenugasan')->name('internal.hapus.penugasan');

                // Penugasan PPNPN
                Route::get('/indexPpnpn/{id}', 'InternalController@indexPpnpn')->name('internal.index.ppnpn');
                Route::get('/createPpnp/{id}', 'InternalController@createPpnpn')->name('internal.create.ppnpn');
                Route::post('/storePpnp', 'InternalController@storePpnpn')->name('internal.store.ppnpn');
                Route::get('/editPpnp/{id}', 'InternalController@editPpnpn')->name('internal.edit.ppnpn');
                Route::post('/updatePpnp', 'InternalController@updatePpnpn')->name('internal.update.ppnpn');

                Route::post('/hapusPpnpn/{id}', 'InternalController@hapusPpnpn')->name('internal.hapus.ppnpn');

                Route::post('/store', 'InternalController@store')->name('internal.store');
                Route::get('/edit/{id}', 'InternalController@edit')->name('internal.edit');
                Route::put('/update', 'InternalController@update')->name('internal.update');
                Route::post('/hapus/{id}', 'InternalController@destroy')->name('internal.hapus');

                Route::put('/updatePegawai', 'InternalController@updatePegawai')->name('internal.update.pegawai');
            });

            // Pendamping
            Route::prefix('pendamping')->group(function () {
                Route::get('/', 'PendampingController@index')->name('pendamping.index');

                Route::get('/tabel', 'PendampingController@tabel')->name('pendamping.tabel');
                Route::get('/create', 'PendampingController@create')->name('pendamping.create');
                Route::post('/store', 'PendampingController@store')->name('pendamping.store');
                Route::get('/edit/{id}', 'PendampingController@edit')->name('pendamping.edit');
                Route::put('/update', 'PendampingController@update')->name('pendamping.update');
                Route::post('/hapus/{id}', 'PendampingController@destroy')->name('pendamping.hapus');

                Route::put('/updatePendamping', 'PendampingController@updatePendamping')->name('pendamping.update.user');
            });

            // Akun
            Route::prefix('akun')->group(function () {
                Route::get('/', 'AkunController@index')->name('akun.index');
                Route::get('/akun/data', 'AkunController@getAkunData')->name('akun.data');

                Route::get('/create', 'AkunController@create')->name('akun.create');
                Route::post('/store', 'AkunController@store')->name('akun.store');
                Route::post('/regis', 'AkunController@regis')->name('akun.regis');
                Route::get('/edit/{id}', 'AkunController@edit')->name('akun.edit');
                Route::put('/update', 'AkunController@update')->name('akun.update');
                Route::post('/hapus/{id}', 'AkunController@destroy')->name('akun.hapus');
            });

            Route::prefix('assessment')->group(function () {
                Route::get('/', 'AssessmentController@index')->name('assessment.index');
                Route::get('/create', 'AssessmentController@create')->name('assessment.create');
                Route::post('/store', 'AssessmentController@store')->name('assessment.store');
                Route::get('/show/{id}', 'AssessmentController@show')->name('assessment.show');
                Route::get('/edit/{id}', 'AssessmentController@edit')->name('assessment.edit');
                Route::put('/update/{id}', 'AssessmentController@update')->name('assessment.update');
                Route::post('/hapus/{id}', 'AssessmentController@destroy')->name('assessment.hapus');

                Route::prefix('kombinasi')->group(function () {
                    Route::get('/', 'AssessmentCombinationController@index')->name('assessment.combination.index');
                    Route::get('/create', 'AssessmentCombinationController@create')->name('assessment.combination.create');
                    Route::post('/store', 'AssessmentCombinationController@store')->name('assessment.combination.store');
                    Route::get('/proses/show/{id}', 'AssessmentCombinationController@generationShow')->name('assessment.combination.generation.show');
                    Route::post('/proses/retry/{id}', 'AssessmentCombinationController@retryGeneration')->name('assessment.combination.generation.retry');
                    Route::get('/show/{id}', 'AssessmentCombinationController@show')->name('assessment.combination.show');
                    Route::post('/hapus/{id}', 'AssessmentCombinationController@destroy')->name('assessment.combination.hapus');
                });

                Route::get('/monitoring', 'AssessmentMonitoringController@index')->name('assessment.monitoring.index');

                Route::prefix('penugasan')->group(function () {
                    Route::get('/', 'AssessmentAssignmentController@index')->name('assessment.assignment.index');
                    Route::get('/create', 'AssessmentAssignmentController@create')->name('assessment.assignment.create');
                    Route::get('/edit/{id}', 'AssessmentAssignmentController@edit')->name('assessment.assignment.edit');
                    Route::get('/guru-options', 'AssessmentAssignmentController@guruOptions')->name('assessment.assignment.guru-options');
                    Route::post('/store', 'AssessmentAssignmentController@store')->name('assessment.assignment.store');
                    Route::put('/update/{id}', 'AssessmentAssignmentController@update')->name('assessment.assignment.update');
                    Route::post('/hapus/{id}', 'AssessmentAssignmentController@destroy')->name('assessment.assignment.hapus');
                    Route::post('/retry/{id}', 'AssessmentAssignmentController@retry')->name('assessment.assignment.retry');
                    Route::get('/show/{id}', 'AssessmentAssignmentController@show')->name('assessment.assignment.show');
                    Route::get('/review/{targetId}', 'AssessmentAttemptReviewController@show')->name('assessment.assignment.review.show');
                    Route::put('/review/{targetId}', 'AssessmentAttemptReviewController@update')->name('assessment.assignment.review.update');
                });
            });

            // Kegiatan
            Route::prefix('kegiatan')->group(function () {
                Route::get('/', 'KegiatanController@index')->name('kegiatan.index');
                Route::get('/create', 'KegiatanController@create')->name('kegiatan.create');
                Route::post('/store', 'KegiatanController@store')->name('kegiatan.store');
                Route::get('/edit/{id}', 'KegiatanController@edit')->name('kegiatan.edit');
                Route::put('/update', 'KegiatanController@update')->name('kegiatan.update');
                Route::post('/hapus/{id}', 'KegiatanController@destroy')->name('kegiatan.hapus');
            });

            // Peserta Kegiatan
            Route::prefix('peserta')->group(function () {
                Route::get('/', 'PesertaKegiatanController@index')->name('peserta.index');
                Route::get('/create', 'PesertaKegiatanController@create')->name('peserta.create');
                Route::post('/store', 'PesertaKegiatanController@store')->name('peserta.store');
                Route::get('/edit/{id}', 'PesertaKegiatanController@edit')->name('peserta.edit');
                Route::put('/update', 'PesertaKegiatanController@update')->name('peserta.update');
                Route::post('/hapus/{id}', 'PesertaKegiatanController@destroy')->name('peserta.hapus');
                Route::get('/cetak/{id}', 'PesertaKegiatanController@cetak')->name('peserta.cetak');
                Route::get('/cetakByUser/{id}', 'PesertaKegiatanController@cetakByUser')->name('peserta.cetakByUser')->withoutMiddleware(['ValidasiUser']);
                Route::get('/export/{id_kegiatan}', 'PesertaKegiatanController@export')->name('peserta.export');
            });

            // Honor
            Route::prefix('honor')->group(function () {
                Route::get('/', 'HonorController@index')->name('honor.index');
                Route::get('/create', 'HonorController@create')->name('honor.create');
                Route::post('/store', 'HonorController@store')->name('honor.store');
                Route::get('/edit/{id}', 'HonorController@edit')->name('honor.edit');
                Route::put('/update', 'HonorController@update')->name('honor.update');
                Route::post('/hapus/{id}', 'HonorController@destroy')->name('honor.hapus');
                Route::get('/cetak/{jabatan}', 'HonorController@cetak')->name('honor.cetak');

                // Route::get('/cetakExcelPanitia/{kegiatan}', 'HonorController@honorPanitia')->name('honor.cetakExcelPanitia');
                // Route::get('/cetakExcelNarasumber/{kegiatan}', 'HonorController@honorNarasumber')->name('honor.cetakExcelNarasumber');

                Route::get('/cetakExcelPanitia/{id_kegiatan}/{jabatan}', 'HonorController@cetakExcelPanitia')->name('honor.cetakExcelPanitia');
                Route::get('/cetakExcelNarasumber/{id_kegiatan}/{jabatan}', 'HonorController@cetakExcelNarasumber')->name('honor.cetakExcelNarasumber');
                Route::get('/cetakExcelPeserta/{id_kegiatan}/{jabatan}', 'HonorController@cetakExcelPeserta')->name('honor.cetakExcelPeserta');
                Route::get('/storeNomor', 'HonorController@storeNomor')->name('honor.storeNomor');

                Route::get('honor/cetakExcelFiltered/{kegiatan}/{jabatan}', 'HonorController@cetakExcelFiltered')->name('honor.cetakExcelFiltered');

                Route::get('/getPeserta', 'HonorController@getPeserta')->name('honor.getPeserta');

                Route::get('/penomoran', 'HonorController@Penomoran')->name('honor.penomoran');
            });

            //kuitansi
            Route::prefix('kuitansi')->group(function () {
                Route::get('/', 'KuitansiController@index')->name('kuitansi.index');
                Route::get('/create', 'KuitansiController@create')->name('kuitansi.create');
                Route::post('/store', 'KuitansiController@store')->name('kuitansi.store');
                Route::get('/edit/{id}', 'KuitansiController@edit')->name('kuitansi.edit');
                Route::put('/update', 'KuitansiController@update')->name('kuitansi.update');
                Route::post('/hapus/{id}', 'KuitansiController@destroy')->name('kuitansi.hapus');
                Route::get('/show/{id}', 'KuitansiController@show')->name('kuitansi.show');
                Route::get('/getPeserta', 'KuitansiController@getPeserta')->name('kuitansi.getPeserta');

                Route::get('/cetakAll', 'KuitansiController@cetakAll')->name('kuitansi.cetakAll');
                Route::get('/cetakRillAll', 'KuitansiController@cetakRillAll')->name('kuitansi.cetakRillAll');
                Route::get('/cetakPJmutlakAll', 'KuitansiController@cetakPJmutlakAll')->name('kuitansi.cetakPJmutlakAll');
                Route::get('/cetakAmplopAll', 'KuitansiController@cetakAmplopAll')->name('kuitansi.cetakAmplopAll');

                Route::get('/cetak/{id}', 'KuitansiController@cetak')->name('kuitansi.cetak');
                Route::get('/cetakRill/{id}', 'KuitansiController@cetakRill')->name('kuitansi.cetakRill');
                Route::get('/cetakPJmutlak/{id}', 'KuitansiController@cetakPJmutlak')->name('kuitansi.cetakPJmutlak');
                Route::get('/cetakAmplop/{id}', 'KuitansiController@cetakAmplop')->name('kuitansi.cetakAmplop');
                Route::get('/cetakPermintaan', 'KuitansiController@cetakPermintaan')->name('kuitansi.cetakPermintaan');
                Route::get('/cetakLampiran', 'KuitansiController@cetakLampiran')->name('kuitansi.cetakLampiran');
                Route::get('/cetakExcel/{id_kegiatan}', 'KuitansiController@cetakExcel')->name('kuitansi.cetakexcel');

                Route::get('/storeNomor', 'KuitansiController@storeNomor')->name('kuitansi.storeNomor');
                Route::get('/penomoran', 'KuitansiController@Penomoran')->name('kuitansi.penomoran');
            });

            //kuitansiLoka
            Route::prefix('kuitansiLoka')->group(function () {
                Route::get('/', 'KuitansiLokaController@index')->name('kuitansiLoka.index');
                Route::get('/create', 'KuitansiLokaController@create')->name('kuitansiLoka.create');
                Route::post('/store', 'KuitansiLokaController@store')->name('kuitansiLoka.store');
                Route::get('/edit/{id}', 'KuitansiLokaController@edit')->name('kuitansiLoka.edit');
                Route::put('/update/{id}', 'KuitansiLokaController@update')->name('kuitansiLoka.update');
                Route::post('/hapus/{id}', 'KuitansiLokaController@destroy')->name('kuitansiLoka.hapus');
                Route::get('/show/{id}', 'KuitansiLokaController@show')->name('kuitansiLoka.show');
                Route::get('/getPeserta', 'KuitansiLokaController@getPeserta')->name('kuitansiLoka.getPeserta');

                Route::get('/cetakAll', 'KuitansiLokaController@cetakAll')->name('kuitansiLoka.cetakAll');
                Route::get('/cetakRillAll', 'KuitansiLokaController@cetakRillAll')->name('kuitansiLoka.cetakRillAll');
                Route::get('/cetakPJmutlakAll', 'KuitansiLokaController@cetakPJmutlakAll')->name('kuitansiLoka.cetakPJmutlakAll');
                Route::get('/cetakAmplopAll', 'KuitansiLokaController@cetakAmplopAll')->name('kuitansiLoka.cetakAmplopAll');

                Route::get('/cetak/{id}', 'KuitansiLokaController@cetak')->name('kuitansiLoka.cetak');
                Route::get('/cetakRill/{id}', 'KuitansiLokaController@cetakRill')->name('kuitansiLoka.cetakRill');
                Route::get('/cetakPJmutlak/{id}', 'KuitansiLokaController@cetakPJmutlak')->name('kuitansiLoka.cetakPJmutlak');
                Route::get('/cetakAmplop/{id}', 'KuitansiLokaController@cetakAmplop')->name('kuitansiLoka.cetakAmplop');
                Route::get('/cetakPermintaan', 'KuitansiLokaController@cetakPermintaan')->name('kuitansiLoka.cetakPermintaan');
                Route::get('/cetakLampiran', 'KuitansiLokaController@cetakLampiran')->name('kuitansiLoka.cetakLampiran');
                Route::get('/cetakExcel/{id_kegiatan}', 'KuitansiLokaController@cetakExcel')->name('kuitansiLoka.cetakexcel');

                Route::get('/storeNomor', 'KuitansiLokaController@storeNomor')->name('kuitansiLoka.storeNomor');
            });

            // Master Jabatan Pegawai BBGP
            Route::prefix('kependudukan')->group(function () {
                Route::get('/', 'KependudukanController@index')->name('kependudukan.index');
                Route::get('/create', 'KependudukanController@create')->name('kependudukan.create');
                Route::post('/store', 'KependudukanController@store')->name('kependudukan.store');
                Route::get('/edit/{id}', 'KependudukanController@edit')->name('kependudukan.edit');
                Route::put('/update', 'KependudukanController@update')->name('kependudukan.update');
                Route::post('/hapus/{id}', 'KependudukanController@hapus')->name('kependudukan.hapus');
                Route::get('/cetak/{id}', 'KependudukanController@cetak')->name('kependudukan.cetak');
            });

            // Route::prefix('kependudukan')->group(function () {
            //     Route::get('/', 'KependudukanController@index')->name('kependudukan.index');
            //     Route::get('/create', 'KependudukanController@create')->name('kependudukan.create');
            //     Route::post('/store', 'KependudukanController@store')->name('kependudukan.store');
            //     Route::get('/edit/{id}', 'KependudukanController@edit')->name('kependudukan.edit');
            //     Route::put('/update', 'KependudukanController@update')->name('kependudukan.update');
            //     Route::post('/hapus/{id}', 'KependudukanController@hapus')->name('kependudukan.hapus');
            //     Route::get('/cetak/{id}', 'KependudukanController@cetak')->name('kependudukan.cetak');
            // });

            // Route::prefix('kependudukan')->group(function () {
            //     Route::get('/', 'KependudukanController@index')->name('kependudukan.index');
            //     Route::get('/create', 'KependudukanController@create')->name('kependudukan.create');
            //     Route::post('/store', 'KependudukanController@store')->name('kependudukan.store');
            //     Route::get('/edit/{id}', 'KependudukanController@edit')->name('kependudukan.edit');
            //     Route::put('/update', 'KependudukanController@update')->name('kependudukan.update');
            //     Route::post('/hapus/{id}', 'KependudukanController@hapus')->name('kependudukan.hapus');
            //     Route::get('/cetak/{id}', 'KependudukanController@cetak')->name('kependudukan.cetak');
            // });

            // Agenda
            Route::prefix('agenda')->group(function () {
                Route::get('/', 'AgendaController@index')->name('agenda.index');
                Route::get('/create', 'AgendaController@create')->name('agenda.create');
                Route::post('/store', 'AgendaController@store')->name('agenda.store');
                Route::get('/edit/{id}', 'AgendaController@edit')->name('agenda.edit');
                Route::put('/update', 'AgendaController@update')->name('agenda.update');
                Route::post('/hapus/{id}', 'AgendaController@destroy')->name('agenda.hapus');
            });

            // Berita
            Route::prefix('berita')->group(function () {
                Route::get('/', 'BeritaController@index')->name('berita.index');
                Route::get('/create', 'BeritaController@create')->name('berita.create');
                Route::post('/store', 'BeritaController@store')->name('berita.store');
                Route::get('/edit/{id}', 'BeritaController@edit')->name('berita.edit');
                Route::put('/update', 'BeritaController@update')->name('berita.update');
                Route::post('/hapus/{id}', 'BeritaController@destroy')->name('berita.hapus');
            });

            // Artikels
            Route::prefix('artikel')->group(function () {
                Route::get('/', 'ArtikelController@index')->name('artikel.index');
                Route::get('/create', 'ArtikelController@create')->name('artikel.create');
                Route::post('/store', 'ArtikelController@store')->name('artikel.store');
                Route::get('/edit/{id}', 'ArtikelController@edit')->name('artikel.edit');
                Route::put('/update', 'ArtikelController@update')->name('artikel.update');
                Route::post('/hapus/{id}', 'ArtikelController@destroy')->name('artikel.hapus');
            });

            // RTL Admin
            Route::prefix('rtl')->group(function () {
                Route::get('/', [RtlController::class, 'index'])->name('rtl.index');
                Route::get('/{id}', [RtlController::class, 'show'])->name('rtl.show');
                Route::post('/update/{id}', [RtlController::class, 'update'])->name('rtl.update');
            });
        });
    }
);

// handle route sekolah
Route::get('/get-sekolahs', [AdminSekolahController::class, 'getSekolahs'])->name('getSekolahs');

// Auth
Route::group(['prefix' => 'auth', 'namespace' => 'App\Http\Controllers'], function () {
    Route::get('/', 'AuthController@login')->name('login');
    Route::get('/login_admin', 'AuthController@login_admin')->name('login.admin');
    // Route::get('/reset', 'AuthController@reset')->name('reset');
    // Route::get('/reset_password', 'AuthController@reset_password')->name('reset.password');
    Route::post('/login', 'AuthController@login_action')->name('login_action');
    Route::post('/login/admin', 'AuthController@login_action_admin')->name('login_action_admin');
    Route::get('/logout', function () {
        Session::flush();

        return redirect()->route(
            'user.index'
        )->with('message', 'sukses logout');
    })->name('logout');
});
// Diagnostic & Repair Route
Route::get('/repair-link', function () {
    $target = base_path('storage/app/public');
    $link = public_path('upload'); // Ganti dari storage ke upload

    // Hapus jika sudah ada (bisa jadi broken link atau folder asli)
    if (file_exists($link)) {
        if (is_link($link)) {
            unlink($link);
        } else {
            // Jika itu folder asli, kita coba rename dulu untuk backup.
            rename($link, $link.'_old_'.time());
        }
    }

    try {
        // Buat Symbolic Link bernama 'upload'
        app()->make('files')->link($target, $link);

        return "Symbolic link 'upload' created successfully!<br>Target: $target <br>Link: $link <br><br>Sekarang file bisa diakses via <b>domain.com/upload/...</b>";
    } catch (\Exception $e) {
        return 'Failed to create link: '.$e->getMessage();
    }
});
