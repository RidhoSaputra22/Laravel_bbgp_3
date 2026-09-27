<?php

namespace App\Http\Controllers;

use App\Models\AssessmentAssignmentTarget;
use App\Models\Guru;
use App\Models\PesertaKegiatan;
use App\Models\Rtl;

class GuruDashboardController extends Controller
{
    public function index()
    {
        $guru = Guru::findOrFail((int) session('guru_id'));

        $activities = PesertaKegiatan::with('kegiatan')
            ->where('no_ktp', $guru->no_ktp)
            ->whereHas('kegiatan')
            ->get()
            ->sortByDesc(fn (PesertaKegiatan $participant) => $participant->kegiatan?->tgl_kegiatan)
            ->values();

        $rtls = Rtl::with('kegiatan')
            ->where('no_ktp', $guru->no_ktp)
            ->latest()
            ->get();

        $assessments = AssessmentAssignmentTarget::with(['assignment', 'attempt'])
            ->where('guru_id', $guru->id)
            ->whereHas('assignment', fn ($query) => $query->active())
            ->latest('assigned_at')
            ->latest('id')
            ->get();

        return view('pages.user.dashboard', [
            'menu' => 'dashboard',
            'guru' => $guru,
            'activities' => $activities,
            'rtls' => $rtls,
            'assessments' => $assessments,
            'pendingRtlCount' => $rtls->whereIn('status', ['pending', 'rejected'])->count(),
        ]);
    }
}
