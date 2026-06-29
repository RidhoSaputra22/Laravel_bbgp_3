@extends('layouts.landing.app')
@section('content')
    <div id="banner-area" class="banner-area"
        style="background-image:url({{ asset('landing/images/banner/bbgtk.jpg') }})">
        <div class="banner-text">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="banner-heading">
                            <h1 class="banner-title">Statistik Data</h1>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb justify-content-center">
                                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Statistik Data</li>
                                </ol>
                            </nav>
                        </div>
                    </div><!-- Col end -->
                </div><!-- Row end -->
            </div><!-- Container end -->
        </div><!-- Banner text end -->
    </div><!-- Banner area end -->

    <section id="main-container" class="main-container">
        <div class="container">

            <div class="row text-center">
                <div class="col-12">
                    <h2 class="section-title">Statistik Guru Penggerak dan Non Guru Penggerak</h2>
                    <h3 class="section-sub-title"></h3>
                </div>
            </div>
            <!--/ Title row end -->

            <div class="row justify-content-center">

                <div class="col-md-12">
                    <!-- Dropdown for Selecting Statistic Type -->
                    <div class="form-group">
                        <label for="statisticType">Pilih Statistik</label>
                        <select class="form-control" id="statisticType">
                            <option value="external">Lihat Statistik Eksternal</option>
                            <option value="activity">Lihat Statistik Kegiatan</option>
                        </select>
                    </div>

                    <!-- Filter for Activity Statistics -->
                    <div id="activityFilters" style="display: none;">
                        <!-- Month Filter -->
                        <div class="form-group">
                            <label for="monthSelect">Pilih Bulan</label>
                            <select class="form-control" id="monthSelect">
                                <option value="">-- Pilih Bulan --</option>
                                <option value="1">Januari</option>
                                <option value="2">Februari</option>
                                <option value="3">Maret</option>
                                <option value="4">April</option>
                                <option value="5">Mei</option>
                                <option value="6">Juni</option>
                                <option value="7">Juli</option>
                                <option value="8">Agustus</option>
                                <option value="9">September</option>
                                <option value="10">Oktober</option>
                                <option value="11">November</option>
                                <option value="12">Desember</option>
                            </select>
                        </div>

                        <!-- Activity Selection -->
                        <div class="form-group">
                            <label for="activitySelect">Pilih Kegiatan</label>
                            <select class="form-control" id="activitySelect" disabled>
                                <option value="">-- Pilih Kegiatan --</option>
                                <!-- Options will be populated dynamically -->
                            </select>
                        </div>

                        <!-- Participant Type Filter -->
                        <div class="form-group">
                            <label for="participantType">Lihat Statistik</label>
                            <select class="form-control" id="participantType">
                                <option value="peserta">Peserta</option>
                                <option value="panitia">Panitia</option>
                                <option value="narasumber">Narasumber</option>
                            </select>
                        </div>
                    </div>

                    <!-- Chart Canvas -->
                    <canvas id="chartCanvas" width="500" height="400"></canvas>
                </div>
            </div><!-- 1st row end -->
        </div><!-- Container end -->
    </section><!-- Main container end -->

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const ctx = document.getElementById('chartCanvas').getContext('2d');
                let chart;

                function renderChart(type, data) {
                    if (chart) chart.destroy();

                    chart = new Chart(ctx, {
                        type: type,
                        data: data,
                        options: {
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            },
                            plugins: {
                                legend: {
                                    position: 'top'
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            let label = context.dataset.label || '';
                                            if (label) {
                                                label += ': ';
                                            }
                                            if (context.parsed.y !== null) {
                                                label += context.parsed.y;
                                            }
                                            return label;
                                        }
                                    }
                                }
                            }
                        }
                    });
                }

                function getBarChartData(data) {
                    return {
                        labels: Object.keys(data),
                        datasets: [{
                            label: 'Jumlah',
                            data: Object.values(data),
                            backgroundColor: [
                                'rgba(75, 192, 192, 0.6)',
                                'rgba(153, 102, 255, 0.6)',
                                'rgba(255, 159, 64, 0.6)'
                            ],
                            borderColor: [
                                'rgba(75, 192, 192, 1)',
                                'rgba(153, 102, 255, 1)',
                                'rgba(255, 159, 64, 1)'
                            ],
                            borderWidth: 1
                        }]
                    };
                }

                // Event listener for statistic type change
                document.getElementById('statisticType').addEventListener('change', function() {
                    const value = this.value;
                    if (value === 'external') {
                        document.getElementById('activityFilters').style.display = 'none';
                        renderChart('bar', getBarChartData(@json($datas)));
                    } else {
                        document.getElementById('activityFilters').style.display = 'block';
                        renderChart('bar', getBarChartData({})); // Clear the chart
                    }
                });

                // Event listener for month selection change
                document.getElementById('monthSelect').addEventListener('change', function() {
                    const month = this.value;
                    // Fetch and render data for the selected month
                    if (month !== '') {
                        fetch(`/api/statistics/month/${month}`)
                            .then(response => response.json())
                            .then(data => {
                                console.log('Month Statistics:', data); // Check the response here
                                renderChart('bar', getBarChartData({
                                    'Jumlah Kegiatan': data.jumlah_kegiatan
                                }));

                                // Fetch activities for the selected month
                                return fetch(`/api/statistics/activities/${month}`);
                            })
                            .then(response => response.json())
                            .then(activities => {
                                console.log('Activities:', activities); // Check the activities list
                                const activitySelect = document.getElementById('activitySelect');
                                activitySelect.innerHTML = '<option value="">-- Pilih Kegiatan --</option>';
                                activities.forEach(activity => {
                                    activitySelect.innerHTML +=
                                        `<option value="${activity.id}">${activity.nama_kegiatan}</option>`;
                                });
                                activitySelect.disabled = false;
                            })
                            .catch(error => console.error('Error fetching data:', error));
                    }
                });

                // Event listener for activity selection change
                document.getElementById('activitySelect').addEventListener('change', function() {
                    const activityId = this.value;
                    const participantType = document.getElementById('participantType').value;
                    // Fetch and render data for the selected activity and participant type
                    if (activityId !== '') {
                        fetch(`/api/statistics/activity/${activityId}/${participantType}`)
                            .then(response => response.json())
                            .then(data => {
                                renderChart('bar', getBarChartData({
                                    [participantType]: data.jumlah
                                }));
                            })
                            .catch(error => console.error('Error fetching data:', error));
                    }
                });

                // Event listener for participant type change
                document.getElementById('participantType').addEventListener('change', function() {
                    const activityId = document.getElementById('activitySelect').value;
                    const participantType = this.value;
                    // Fetch and render data for the selected activity and participant type
                    if (activityId !== '') {
                        fetch(`/api/statistics/activity/${activityId}/${participantType}`)
                            .then(response => response.json())
                            .then(data => {
                                renderChart('bar', getBarChartData({
                                    [participantType]: data.jumlah
                                }));
                            })
                            .catch(error => console.error('Error fetching data:', error));
                    }
                });

                // Initial render for external statistics
                renderChart('bar', getBarChartData(@json($datas)));
            });
        </script>
    @endpush
@endsection
