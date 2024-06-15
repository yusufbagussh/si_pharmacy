@extends('layouts.main')
@section('container')
    <div class="container">
        <div class="my-3 d-flex justify-content-center">
            <form action="{{ route('pharmacies.dashboard.locations') }}" method="GET" class="col-12 col-md-6 col-lg-4">
                <div class="input-group">
                    <input type="text" class="form-control flatpickr-date" id="date" name="date"
                        placeholder="dd-mm-yyyy" value="{{ $date }}">
                    <button type="submit" class="btn btn-success"><i class="bi bi-search"></i></button>
                </div>
            </form>
        </div>
        <div class="row g-3 justify-content-center mb-3">
            @forelse ($locations as $location)
                <div class="col-12 col-md-6">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h1 class="card-title text-uppercase fw-bold text-center">
                                {{ $location->LocationName }}
                            </h1>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <td colspan="2" class="text-center text-dark fw-bold">
                                                <h5>Total Order</h5>
                                            </td>
                                            <td colspan="2" class="text-center text-dark fw-bold">
                                                <h5><strong>{{ $location->TotalOrder }}</strong></h5>
                                            </td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="text-dark fw-bold">
                                                <h5>Selesai Dikerjakan</h5>
                                            </td>
                                            <td class="text-dark fw-bold">
                                                <h5><strong>{{ $location->TotalOrderClosed }}</strong></h5>
                                            </td>
                                            <td class="text-dark fw-bold">
                                                <h5>Belum Selesai Dikerjakan</h5>
                                            </td>
                                            <td class="text-dark fw-bold">
                                                <h5><strong>{{ $location->TotalOrderUnClosed }}</strong></h5>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-dark fw-bold">
                                                <h5>Selesai Tepat Waktu</h5>
                                            </td>
                                            <td class="text-center text-dark fw-bold">
                                                <h5><strong>{{ $location->TotalOrderOnTime }}</strong></h5>
                                            </td>
                                            <td class="text-dark fw-bold">
                                                <h5>Tidak Selesai Tepat Waktu</h5>
                                            </td>
                                            <td class="text-center text-dark fw-bold">
                                                <h5><strong>{{ $location->TotalOrderLateTime }}</strong></h5>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" class="text-center text-dark fw-bold">
                                                <h5>Time Racikan</h5>
                                            </td>
                                            @if ($location->AverageDurationRacikan)
                                                @if ($location->AverageDurationRacikan > 3600)
                                                    <td colspan="2" class="text-center fw-bold bg-danger">
                                                        <h5 class="text-white"><strong>
                                                                @convertSeconds($location->AverageDurationRacikan)
                                                            </strong>
                                                        </h5>
                                                    </td>
                                                @else
                                                    <td colspan="2" class="text-center text-dark fw-bold">
                                                        <h5><strong>
                                                                @convertSeconds($location->AverageDurationRacikan)
                                                            </strong>
                                                        </h5>
                                                    </td>
                                                @endif
                                            @else
                                                <td colspan="2" class="text-center text-dark fw-bold">
                                                    <h5><strong>
                                                            00:00:00
                                                        </strong>
                                                    </h5>
                                                </td>
                                            @endif
                                        </tr>
                                        <tr>
                                            <td colspan="2" class="text-center text-dark fw-bold">
                                                <h5>Time Non-Racikan</h5>
                                            </td>
                                            @if ($location->AverageDurationNonRacikan)
                                                @if ($location->AverageDurationNonRacikan > 1800)
                                                    <td colspan="2" class="text-center fw-bold bg-danger">
                                                        <h5 class="text-white"><strong>
                                                                @convertSeconds($location->AverageDurationNonRacikan)
                                                            </strong>
                                                        </h5>
                                                    </td>
                                                @else
                                                    <td colspan="2" class="text-center text-dark fw-bold">
                                                        <h5><strong>
                                                                @convertSeconds($location->AverageDurationNonRacikan)
                                                            </strong>
                                                        </h5>
                                                    </td>
                                                @endif
                                            @else
                                                <td colspan="2" class="text-center text-dark fw-bold">
                                                    <h5><strong>
                                                            00:00:00
                                                        </strong>
                                                    </h5>
                                                </td>
                                            @endif
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="alert alert-danger">
                    Data order hari ini belum Tersedia.
                </div>
            @endforelse

            {{-- <div class="col-md-6">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title text-uppercase fw-bold text-center" style="font-size: 30px;">Farmasi Rawat
                            Jalan
                        </h5>
                        <table class="table table-bordered table-striped">
                            <tr>
                                <td colspan="2" class="text-center">Total Resep Dikerjakan</td>
                                <td colspan="2" class="text-center">{{ $data[0]->TotalOrder }}</td>
                            </tr>
                            <tr>
                                <td>Resep Selesai Dikerjakan</td>
                                <td>{{ $data[0]->TotalOrderClosed }}</td>
                                <td>Resep Belum Selesai Dikerjakan</td>
                                <td>{{ $data[0]->TotalOrderUnClosed }}</td>
                            </tr>
                            <tr>
                                <td>Resep Selesai Tepat Waktu</td>
                                <td>0</td>
                                <td>Resep Tidak Selesai Tepat Waktu</td>
                                <td>0</td>
                            </tr>
                        </table>
                        <p class="text-end"><a href="https://www.linkanda.com"
                                style="text-decoration: underline; color: white;">View
                                Detail</a></p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title text-uppercase fw-bold text-center" style="font-size: 30px;">Farmasi Rawat
                            Inap
                        </h5>
                        <table class="table table-bordered table-striped">
                            <tr>
                                <td colspan="2" class="text-center">Total Resep Dikerjakan:</td>
                                <td colspan="2" class="text-center">{{ $data[0]->TotalOrder }}</td>
                            </tr>
                            <tr>
                                <td>Resep Selesai Dikerjakan:</td>
                                <td>{{ $data[0]->TotalOrderClosed }}</td>
                                <td>Resep Belum Selesai Dikerjakan:</td>
                                <td>{{ $data[0]->TotalOrderUnClosed }}</td>
                            </tr>
                            <tr>
                                <td>Resep Selesai Tepat Waktu:</td>
                                <td>0</td>
                                <td>Resep Tidak Selesai Tepat Waktu:</td>
                                <td>0</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title text-uppercase fw-bold text-center" style="font-size: 30px;">Farmasi IGD</h5>
                        <table class="table table-bordered table-striped">
                            <tr>
                                <td colspan="2" class="text-center">Total Resep Dikerjakan:</td>
                                <td colspan="2" class="text-center">{{ $data[0]->TotalOrder }}</td>
                            </tr>
                            <tr>
                                <td>Resep Selesai Dikerjakan:</td>
                                <td>{{ $data[0]->TotalOrderClosed }}</td>
                                <td>Resep Belum Selesai Dikerjakan:</td>
                                <td>{{ $data[0]->TotalOrderUnClosed }}</td>
                            </tr>
                            <tr>
                                <td>Resep Selesai Tepat Waktu:</td>
                                <td>0</td>
                                <td>Resep Tidak Selesai Tepat Waktu:</td>
                                <td>0</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div> --}}
        </div>
    </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script>
        let date_range_str = @json($date);
        if (date_range_str !== null) {
            if (date_range_str.includes('to')) {
                const [start_date_str, end_date_str] = date_range_str.split(' to ');

                // Mengonversi string menjadi objek Date menggunakan Moment.js
                start_date = moment(start_date_str, "DD-MM-YYYY").toDate();
                end_date = moment(end_date_str, "DD-MM-YYYY").toDate();
            } else {
                // Mengonversi string menjadi objek Date menggunakan Moment.js
                start_date = moment(date_range_str, "DD-MM-YYYY").toDate();
                end_date = moment(date_range_str, "DD-MM-YYYY").toDate();
            }
        } else {
            start_date = new Date();
            end_date = new Date();
        }

        // Inisialisasi Flatpickr
        // flatpickr('.flatpickr-date', {
        //     mode: "range",
        //     dateFormat: "d-m-Y",
        //     defaultDate: [start_date, end_date] // Mengatur defaultDate ke hari ini dan besok
        // });

        // flatpickr('.flatpickr-date', {
        //     enableTime: true,
        //     mode: "range",
        //     dateFormat: "Y-m-d h:iK",
        //     minDate: new Date().fp_incr(-31),
        //     maxDate: "today",
        //     defaultDate: [startDate, endDate], // Mengatur defaultDate ke hari ini dan besok
        // });

        // String input
        const inputString = date_range_str;
        // Parse the date-time strings using Moment.js
        const startDate = moment(inputString.split(" to ")[0], "DD-MM-YYYY HH:mm").toDate();
        const endDate = moment(inputString.split(" to ")[1], "DD-MM-YYYY HH:mm").toDate();

        // Fungsi untuk memeriksa dan membatasi rentang tanggal
        function limitRange(selectedDates, dateStr, instance) {
            if (selectedDates.length === 2) {
                const start = selectedDates[0];
                const end = selectedDates[1];
                const maxRange = moment(start).add(31, 'days').toDate();

                if (end > maxRange) {
                    instance.setDate([start, maxRange], true);
                }

                // Atur waktu pada start date ke 00:00
                start.setHours(0, 0, 0, 0);

                // Atur waktu pada end date ke 23:59
                end.setHours(23, 59, 59, 999);
            }
        }

        // Initialize Flatpickr with the default date-time range
        flatpickr(".flatpickr-date", {
            mode: "range",
            enableTime: true,
            dateFormat: "d-m-Y H:i",
            defaultDate: [startDate, endDate],
            onChange: function(selectedDates, dateStr, instance) {
                if (selectedDates.length === 1) {
                    // Set the time of the start date to 00:00
                    selectedDates[0].setHours(0, 0, 0, 0);
                    instance.setDate(selectedDates, false);
                } else if (selectedDates.length === 2) {
                    // Set the time of the end date to 23:59 if start time is 00:00
                    if (selectedDates[0].getHours() === 0 && selectedDates[0].getMinutes() === 0) {
                        selectedDates[1].setHours(23, 59, 0, 0);
                        instance.setDate(selectedDates, false);
                    }

                    const start = selectedDates[0];
                    const end = selectedDates[1];
                    const maxRange = moment(start).add(31, 'days').toDate();

                    if (end > maxRange) {
                        instance.setDate([start, maxRange], true);
                    }
                }
            },
            time_24hr: true,
            maxDate: moment().endOf('day').toDate(),
            defaultHour: 0
        });
    </script>
    <script>
        // Fungsi untuk reload halaman setiap 30 detik
        function reloadPage() {
            location.reload();
        }
        // Jalankan fungsi reload setiap 30 detik
        setInterval(reloadPage, 60000);
    </script>
@endsection
