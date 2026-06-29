@extends('layouts.user.app', ['title' => 'Kontak'])

@section('content')
    @push('styles')
    @endpush

    <div class="main-content ">
        <section class="section">
            <div class="section-header">
                <h1 class="text-primary"><u> Kontak BBGTK Sulawesi Selatan</u> </h1>

            </div>

            <div class="section-body">
                {{-- <h2 class="section-title">This is Example Page</h2>
                <p class="section-lead">This page is just an example for you to create your own page.</p> --}}
                <div class="card">

                    <div class="card-body">
                        <div class="p-2">

                            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3973.6360074660843!2d119.444923!3d-5.162121999999999!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2dbee30920b03ac9%3A0xa738c1f4d75fc525!2sBBGP%20Sulawesi%20Selatan!5e0!3m2!1sid!2sid!4v1719594902720!5m2!1sid!2sid" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                        </div>
                        
                    </div>
                    {{-- <div class="card-footer bg-whitesmoke">
                        This is card footer
                    </div> --}}
                </div>
            </div>
        </section>
    </div>

    @push('scripts')
    <script src="{{ asset('library/gmaps/gmaps.min.js') }}"></script>
        <script src="{{ asset('js/page/gmaps-simple.js') }}"></script>
    @endpush
@endsection
