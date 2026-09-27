@extends('layouts.app', ['title' => 'Dashboard Pegawai'])

@section('content')
    @push('styles')
        <link rel="stylesheet" href="{{ asset('library/fullcalendar/dist/fullcalendar.min.css') }}">
    @endpush

    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Dashboard Pegawai</h1>
            </div>

            <div class="section-body">


                <div class="card">
                    <div class="card-header">
                        <h4>Kalender Kegiatan dan Penugasan Saya</h4>
                    </div>
                    <div class="card-body">
                        <div id="pegawai-calendar"></div>
                        @if ($calendarEvents->isEmpty())
                            <p class="text-muted text-center mt-3 mb-0">Belum ada kegiatan atau penugasan.</p>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div class="modal fade" id="pegawaiEventModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pegawaiEventTitle"></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p><strong>Jenis:</strong> <span id="pegawaiEventType"></span></p>
                    <p><strong>Waktu:</strong> <span id="pegawaiEventTime"></span></p>
                    <p><strong>Tempat:</strong> <span id="pegawaiEventPlace"></span></p>
                    <p><strong>Deskripsi:</strong> <span id="pegawaiEventDescription"></span></p>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('library/fullcalendar/dist/fullcalendar.min.js') }}"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/locale/id.min.js"></script>
        <script>
            $(function() {
                $('#pegawai-calendar').fullCalendar({
                    locale: 'id',
                    height: 'auto',
                    header: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'month,agendaWeek,agendaDay,listWeek'
                    },
                    events: @json($calendarEvents),
                    eventClick: function(event) {
                        $('#pegawaiEventTitle').text(event.title || 'Kegiatan');
                        $('#pegawaiEventType').text(event.jenis || '-');
                        $('#pegawaiEventTime').text(event.jam || '-');
                        $('#pegawaiEventPlace').text(event.tempat || '-');
                        $('#pegawaiEventDescription').text(event.description || '-');
                        $('#pegawaiEventModal').modal('show');
                    }
                });
            });
        </script>
    @endpush
@endsection
