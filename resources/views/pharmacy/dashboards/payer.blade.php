@extends('layouts.main')
@section('container')
    <div class="container-fluid">
        <div class="row my-3 d-flex col-12 col-md-12">
            <form action="{{ route('pharmacies.dashboard.payers') }}" method="GET">
                <div class="input-group flex-column flex-md-row justify-content-center">
                    <div class="col-12 col-md-2">
                        <select class="form-select mb-2 mb-md-0" id="location" name="location"
                            aria-label="Default select example">
                            @foreach ($locations as $key => $value)
                                <option value="{{ $key }}" {{ $locationId == $key ? 'selected' : '' }}>
                                    {{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <input type="text" class="form-control flatpickr-date mb-2 mb-md-0" id="date" name="date"
                            placeholder="dd-mm-yyyy" value="{{ $date }}">
                    </div>
                    <div class="col-12 col-md-1">
                        <button type="submit" class="btn btn-success"><i class="bi bi-search"></i></button>
                    </div>
                </div>
            </form>
        </div>

        <div class="row g-3 justify-content-center mb-1">
            @forelse ($classes as $class)
                <div class="col-4">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h1 class="card-title text-uppercase fw-bold text-center">
                                {{ $class->CustomerType }}
                            </h1>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <td colspan="2" class="text-center text-dark fw-bold">
                                                <h6>Total Order</h6>
                                            </td>
                                            <td colspan="2" class="text-center text-dark fw-bold">
                                                <h5><strong>{{ $class->TotalOrder }}</strong></h5>
                                            </td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="text-dark fw-bold">
                                                <h6>Selesai Dikerjakan</h6>
                                            </td>
                                            <td class="text-dark fw-bold">
                                                <h5><strong>{{ $class->TotalOrderClosed }}</strong></h5>
                                            </td>
                                            <td class="text-dark fw-bold">
                                                <h6>Belum Selesai Dikerjakan</h6>
                                            </td>
                                            <td class="text-dark fw-bold">
                                                <h5><strong>{{ $class->TotalOrderUnClosed }}</strong></h5>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-dark fw-bold">
                                                <h6>Selesai Tepat Waktu</h6>
                                            </td>
                                            <td class="text-center text-dark fw-bold">
                                                <h5><strong>{{ $class->TotalOrderOnTime }}</strong></h5>
                                            </td>
                                            <td class="text-dark fw-bold">
                                                <h6>Tidak Selesai Tepat Waktu</h6>
                                            </td>
                                            <td class="text-center text-dark fw-bold">
                                                <h5><strong>{{ $class->TotalOrderLateTime }}</strong></h5>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" class="text-center text-dark fw-bold">
                                                <h6>Time Racikan</h6>
                                            </td>
                                            @if ($class->AverageDurationRacikan)
                                                @if ($class->AverageDurationRacikan > 3600)
                                                    <td colspan="2" class="text-center fw-bold bg-danger">
                                                        <h5 class="text-white"><strong>
                                                                @convertSeconds($class->AverageDurationRacikan)
                                                            </strong>
                                                        </h5>
                                                    </td>
                                                @else
                                                    <td colspan="2" class="text-center text-dark fw-bold">
                                                        <h5><strong>
                                                                @convertSeconds($class->AverageDurationRacikan)
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
                                                <h6>Time Non-Racikan</h6>
                                            </td>
                                            @if ($class->AverageDurationNonRacikan)
                                                @if ($class->AverageDurationNonRacikan > 1800)
                                                    <td colspan="2" class="text-center fw-bold bg-danger">
                                                        <h5 class="text-white"><strong>
                                                                @convertSeconds($class->AverageDurationNonRacikan)
                                                            </strong>
                                                        </h5>
                                                    </td>
                                                @else
                                                    <td colspan="2" class="text-center text-dark fw-bold">
                                                        <h5><strong>
                                                                @convertSeconds($class->AverageDurationNonRacikan)
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
        </div>

        <div class="row g-3 justify-content-center mb-1">
            <div class="col-6">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered text-center table-custom-border">
                        <thead>
                            <tr>
                                <th colspan="7" scope="col" class="align-middle">5 OLDEST ORDER NON-RACIKAN</th>
                            </tr>
                            <tr>
                                <th scope="col" class="align-middle">No</th>
                                <th scope="col" class="align-middle">No.RM</th>
                                <th scope="col" class="align-middle">Nama Pasien</th>
                                <th scope="col" class="align-middle">Penjamin</th>
                                <th scope="col" class="align-middle">Progress</th>
                                <th scope="col" class="align-middle">Time</th>
                            </tr>
                        </thead>
                        <tbody class="table-group-divider">
                            @forelse ($dataNonRacikans as $key => $nonRacikan)
                                <tr>
                                    <td class="key">{{ ++$key }}</td>
                                    <td class="no-rekan-medis">{{ $nonRacikan->MedicalNo }}</td>
                                    <td class="patient-name">{{ $nonRacikan->PatientName }}</td>
                                    <td>@checkCustomerType($nonRacikan->GCCustomerType, $nonRacikan->Penjamin)</td>
                                    <td class="text-bar">
                                        <div id="progress-bar-nonracikan{{ $key }}"
                                            class="progress-bar bg-primary" role="progressbar"
                                            style="width: 0%;color: white" aria-valuenow="0" aria-valuemin="0"
                                            aria-valuemax="100"></div>
                                        <div id="text-bar-nonracikan{{ $key }}" style="font-size: 15px"></div>
                                    </td>
                                    <td id="time-running-nonracikan{{ $key }}"></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="14" class="text-center">
                                        <div class="alert alert-danger">
                                            Data order hari ini belum Tersedia.
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-6">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered text-center table-custom-border">
                        <thead>
                            <tr>
                                <th colspan="7" scope="col" class="align-middle">5 OLDEST ORDER RACIKAN</th>
                            </tr>
                            <tr>
                                <th scope="col" class="align-middle">No</th>
                                <th scope="col" class="align-middle">No.RM</th>
                                <th scope="col" class="align-middle">Nama Pasien</th>
                                <th scope="col" class="align-middle">Penjamin</th>
                                <th scope="col" class="align-middle">Progress</th>
                                <th scope="col" class="align-middle">Time</th>
                            </tr>
                        </thead>
                        <tbody class="table-group-divider">
                            @forelse ($dataRacikans as $key => $racikan)
                                <tr>
                                    <td class="key">{{ ++$key }}</td>
                                    <td class="no-rekan-medis">{{ $racikan->MedicalNo }}</td>
                                    <td class="patient-name">{{ $racikan->PatientName }}</td>
                                    <td>@checkCustomerType($racikan->GCCustomerType, $racikan->Penjamin)</td>
                                    <td class="text-bar">
                                        <div id="progress-bar-racikan{{ $key }}" class="progress-bar bg-primary"
                                            role="progressbar" style="width: 0%;color: white" aria-valuenow="0"
                                            aria-valuemin="0" aria-valuemax="100"></div>
                                        <div id="text-bar-racikan{{ $key }}" style="font-size: 15px"></div>
                                    </td>
                                    <td id="time-running-racikan{{ $key }}"></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="14" class="text-center">
                                        <div class="alert alert-danger">
                                            Data order hari ini belum Tersedia.
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script>
        //Mengkonversi data date dari php ke javascript
        const dateRangeStr = @json($date);
        // Parse the date-time strings using Moment.js
        const startDate = moment(dateRangeStr.split(" to ")[0], "DD-MM-YYYY HH:mm").toDate();
        const endDate = moment(dateRangeStr.split(" to ")[1], "DD-MM-YYYY HH:mm").toDate();


        // Fungsi untuk memeriksa dan membatasi rentang tanggal
        function limitRange(selectedDates, dateStr, instance) {
            if (selectedDates.length === 2) {
                const start = selectedDates[0];
                const end = selectedDates[1];
                const maxRange = moment(start).add(31, 'days').toDate();

                if (end > maxRange) {
                    instance.setDate([start, maxRange], true);
                }
            }
        }

        // Initialize Flatpickr with the default date-time range
        flatpickr(".flatpickr-date", {
            mode: "range",
            enableTime: true,
            dateFormat: "d-m-Y H:i", // Format tampilan Flatpickr
            defaultDate: [startDate, endDate],
            time_24hr: true,
            maxDate: moment().endOf('day').toDate(),
            onChange: limitRange,
            defaultHour: 0,
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

                    // Set maksimal range yang dapat diinput adalah 31 hari
                    const start = selectedDates[0];
                    const end = selectedDates[1];
                    const maxRange = moment(start).add(31, 'days').toDate();

                    if (end > maxRange) {
                        instance.setDate([start, maxRange], true);
                    }
                }
            },
        });
    </script>
    <script>
        let timeOrder = @json($timeRespons);
        let dataRacikanFromPHP = @json($dataRacikans);
        let dataNonRacikanFromPHP = @json($dataNonRacikans);

        function setAndGetDiffDateTime(date, time, earlyTime) {
            // Memecah string jam dan menit dari data time
            const [hours, minutes] = time.split(':').map(Number);

            // Membuat objek Date baru berdasarkan tanggal yang ada
            const combinedDateTime = new Date(date);

            // Mengatur jam dan menit
            combinedDateTime.setHours(hours);
            combinedDateTime.setMinutes(minutes);

            // Mencari selisih waktu yang sedah berjalan
            diffTime = combinedDateTime - earlyTime;

            return diffTime;
        }

        function convertDateTime(date, time) {
            let hours = 0,
                minutes = 0,
                combinedDateTime;

            // Membuat objek Date baru berdasarkan tanggal yang ada
            combinedDateTime = new Date(date);

            // cek apakah time tidak kosong
            if (time !== '') {
                // Jika tidak kosong, memecah string jam dan menit dari data time
                [hours, minutes] = time.split(':').map(Number);

                // Mengatur jam dan menit
                combinedDateTime.setHours(hours);
                combinedDateTime.setMinutes(minutes);
            }


            return combinedDateTime;
        }

        function getTimeString(currentTime, startTime, diffTimeRunning) {
            let timeRunning,
                hours,
                minutes,
                seconds,
                timeString;
            if (diffTimeRunning === 0) {
                timeRunning = Math.floor((currentTime - startTime) / 1000); // Konversi ke detik
            } else {
                timeRunning = Math.floor((diffTimeRunning) / 1000); // Konversi ke detik
            }
            hours = Math.floor(timeRunning / 3600);
            minutes = Math.floor((timeRunning % 3600) / 60);
            seconds = timeRunning % 60;
            timeString =
                `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            return timeString;
        }

        dataRacikanFromPHP.forEach((data, index) => {
            let i = index + 1;

            function updateProgressBar() {
                let standarTime = 0;
                if (data.JenisResep === 'RACIKAN') {
                    standarTime = timeOrder.racikan; // standard time untuk resep jenis racikan 60 menit
                } else {
                    standarTime = timeOrder.nonRacikan; // standart time untuk resep jenis non racikan 30 menit
                }

                let diffTime = 0; // Selisih waktu
                let diffTimeRunning = 0; // Selisih waktu
                let percentageElapsedTime = 0; // Persentase waktu yang sudah berjalan

                let startTime = null; // Waktu mulai order
                let currentTime = null; // Waktu live saat ini
                let elapsedTime = 0; // Waktu yang sudah berjalan

                currentTime = new Date();

                if (data.SendOrderDateTime !== null) {
                    const sendOrderDateTimeObject = convertDateTime(data.SendOrderDateTime, '');
                    startTime = sendOrderDateTimeObject;

                    if (data.ClosedDateFarmasi !== null && data.ClosedTimeFarmasi !== '') {
                        diffTime = setAndGetDiffDateTime(data.ClosedDateFarmasi, data.ClosedTimeFarmasi,
                            sendOrderDateTimeObject);
                        diffTimeRunning = setAndGetDiffDateTime(data.ClosedDateFarmasi, data.ClosedTimeFarmasi,
                            sendOrderDateTimeObject);
                        if (diffTime < standarTime) {
                            diffTime = standarTime;
                        }
                    } else if (data.ProposedDateTime !== null) {
                        proposedDateTimeObject = convertDateTime(data.ProposedDateTime, '');
                        diffTime = proposedDateTimeObject - sendOrderDateTimeObject;
                    }

                } else if (data.ProposedDateTime !== null) {
                    const proposedDateTimeObject = convertDateTime(data.ProposedDateTime, '');
                    startTime = proposedDateTimeObject
                    if (data.ClosedDateFarmasi !== null && data.ClosedTimeFarmasi !== '') {
                        diffTime = setAndGetDiffDateTime(data.ClosedDateFarmasi, data.ClosedTimeFarmasi,
                            proposedDateTimeObject);
                        diffTimeRunning = setAndGetDiffDateTime(data.ClosedDateFarmasi, data.ClosedTimeFarmasi,
                            proposedDateTimeObject);
                        if (diffTime < standarTime) {
                            diffTime = standarTime;
                        }
                    }

                }

                if (data.SendOrderDateTime === null &&
                    data.ClosedDateFarmasi === null && data.ClosedTimeFarmasi === '' &&
                    data.ProposedDateTime === null) {
                    document.getElementById(`time-running-racikan${i}`).innerText = '00:00:00';
                }
                // Jika date time closed order masih kosong, maka hitung waktu berjalan
                else if (data.ClosedDateFarmasi === null && data.ClosedTimeFarmasi === '') {
                    elapsedTime = currentTime - startTime;
                    // Jika date time closed order sudah terisi, maka hitung waktu berjalan dari waktu start ke closed order
                    // Tampilkan waktu yang sedang berjalan
                    let timeString = getTimeString(currentTime, startTime, 0);
                    document.getElementById(`time-running-racikan${i}`).innerText = timeString;
                } else {
                    elapsedTime = diffTime;
                    let timeString = getTimeString(0, 0, diffTimeRunning);
                    document.getElementById(`time-running-racikan${i}`).innerText = timeString;
                }

                // Ambil elemen dengan ID "progress-bar" ke-i
                const progressBar = document.getElementById(`progress-bar-racikan${i}`);

                // Ambil elemen dengan ID "progress-bar" ke-i
                const textBar = document.getElementById(`text-bar-racikan${i}`);

                //Jika setiap waktu masing-masing data masih kosong, maka progress bar = 0%
                if (data.SendOrderDateTime === null &&
                    data.ClosedDateFarmasi === null && data.ClosedTimeFarmasi === '' &&
                    data.ProposedDateTime === null) {
                    // progressBar.style.width = '0%';
                    // progressBar.innerText = '0%';
                    textBar.innerText = '0%';
                    progressBar.style.display = "none";
                    //Jika sudah ada waktu terisi, maka hitung persentase waktu yang sudah berjalan
                } else {
                    percentageElapsedTime = (elapsedTime / standarTime) * 100;
                    if (percentageElapsedTime <= 100) {
                        progressBar.style.width = percentageElapsedTime + '%';
                    } else {
                        progressBar.style.width = '100%';
                    }
                    progressBar.innerText = percentageElapsedTime.toFixed(2) + '%';
                    // textBar.innerText = percentageElapsedTime.toFixed(2) + '%';
                    textBar.style.display = "none";
                }

                // Periksa jika nilai variabel lebih dari 100
                if (percentageElapsedTime > 100) {
                    // Jika ya, ubah kelasnya menjadi "progress-bar bg-danger"
                    progressBar.className = "progress-bar bg-danger";
                } else if (percentageElapsedTime === 100) {
                    progressBar.className = "progress-bar bg-success";
                }

                // Jika date time closed order masih kosong, maka update function progressBar setiap 1 detik
                if (data.ClosedDateFarmasi === null && data.ClosedTimeFarmasi === '') {
                    setTimeout(updateProgressBar, 1000);
                }
            }

            updateProgressBar();
        });

        dataNonRacikanFromPHP.forEach((data, index) => {
            let i = index + 1;

            function updateProgressBar() {
                let standarTime = 0;
                if (data.JenisResep === 'RACIKAN') {
                    standarTime = timeOrder.racikan; // standard time untuk resep jenis racikan 60 menit
                } else {
                    standarTime = timeOrder.nonRacikan; // standart time untuk resep jenis non racikan 30 menit
                }

                let diffTime = 0; // Selisih waktu
                let diffTimeRunning = 0; // Selisih waktu
                let percentageElapsedTime = 0; // Persentase waktu yang sudah berjalan

                let startTime = null; // Waktu mulai order
                let currentTime = null; // Waktu live saat ini
                let elapsedTime = 0; // Waktu yang sudah berjalan

                currentTime = new Date();

                if (data.SendOrderDateTime !== null) {
                    const sendOrderDateTimeObject = convertDateTime(data.SendOrderDateTime, '');
                    startTime = sendOrderDateTimeObject;

                    if (data.ClosedDateFarmasi !== null && data.ClosedTimeFarmasi !== '') {
                        diffTime = setAndGetDiffDateTime(data.ClosedDateFarmasi, data.ClosedTimeFarmasi,
                            sendOrderDateTimeObject);
                        diffTimeRunning = setAndGetDiffDateTime(data.ClosedDateFarmasi, data.ClosedTimeFarmasi,
                            sendOrderDateTimeObject);
                        if (diffTime < standarTime) {
                            diffTime = standarTime;
                        }
                    } else if (data.ProposedDateTime !== null) {
                        proposedDateTimeObject = convertDateTime(data.ProposedDateTime, '');
                        diffTime = proposedDateTimeObject - sendOrderDateTimeObject;
                    }

                } else if (data.ProposedDateTime !== null) {
                    const proposedDateTimeObject = convertDateTime(data.ProposedDateTime, '');
                    startTime = proposedDateTimeObject
                    if (data.ClosedDateFarmasi !== null && data.ClosedTimeFarmasi !== '') {
                        diffTime = setAndGetDiffDateTime(data.ClosedDateFarmasi, data.ClosedTimeFarmasi,
                            proposedDateTimeObject);
                        diffTimeRunning = setAndGetDiffDateTime(data.ClosedDateFarmasi, data.ClosedTimeFarmasi,
                            proposedDateTimeObject);
                        if (diffTime < standarTime) {
                            diffTime = standarTime;
                        }
                    }

                }

                if (data.SendOrderDateTime === null &&
                    data.ClosedDateFarmasi === null && data.ClosedTimeFarmasi === '' &&
                    data.ProposedDateTime === null) {
                    document.getElementById(`time-running-nonracikan${i}`).innerText = '00:00:00';
                }
                // Jika date time closed order masih kosong, maka hitung waktu berjalan
                else if (data.ClosedDateFarmasi === null && data.ClosedTimeFarmasi === '') {
                    elapsedTime = currentTime - startTime;
                    // Jika date time closed order sudah terisi, maka hitung waktu berjalan dari waktu start ke closed order
                    // Tampilkan waktu yang sedang berjalan
                    let timeString = getTimeString(currentTime, startTime, 0);
                    document.getElementById(`time-running-nonracikan${i}`).innerText = timeString;
                } else {
                    elapsedTime = diffTime;
                    let timeString = getTimeString(0, 0, diffTimeRunning);
                    document.getElementById(`time-running-nonracikan${i}`).innerText = timeString;
                }

                // Ambil elemen dengan ID "progress-bar" ke-i
                const progressBar = document.getElementById(`progress-bar-nonracikan${i}`);

                // Ambil elemen dengan ID "progress-bar" ke-i
                const textBar = document.getElementById(`text-bar-nonracikan${i}`);

                //Jika setiap waktu masing-masing data masih kosong, maka progress bar = 0%
                if (data.SendOrderDateTime === null &&
                    data.ClosedDateFarmasi === null && data.ClosedTimeFarmasi === '' &&
                    data.ProposedDateTime === null) {
                    // progressBar.style.width = '0%';
                    // progressBar.innerText = '0%';
                    textBar.innerText = '0%';
                    progressBar.style.display = "none";
                    //Jika sudah ada waktu terisi, maka hitung persentase waktu yang sudah berjalan
                } else {
                    percentageElapsedTime = (elapsedTime / standarTime) * 100;
                    if (percentageElapsedTime <= 100) {
                        progressBar.style.width = percentageElapsedTime + '%';
                    } else {
                        progressBar.style.width = '100%';
                    }
                    progressBar.innerText = percentageElapsedTime.toFixed(2) + '%';
                    // textBar.innerText = percentageElapsedTime.toFixed(2) + '%';
                    textBar.style.display = "none";
                }

                // Periksa jika nilai variabel lebih dari 100
                if (percentageElapsedTime > 100) {
                    // Jika ya, ubah kelasnya menjadi "progress-bar bg-danger"
                    progressBar.className = "progress-bar bg-danger";
                } else if (percentageElapsedTime === 100) {
                    progressBar.className = "progress-bar bg-success";
                }

                // Jika date time closed order masih kosong, maka update function progressBar setiap 1 detik
                if (data.ClosedDateFarmasi === null && data.ClosedTimeFarmasi === '') {
                    setTimeout(updateProgressBar, 1000);
                }
            }

            updateProgressBar();
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
