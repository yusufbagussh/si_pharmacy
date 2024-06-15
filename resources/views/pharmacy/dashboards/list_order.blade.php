@extends('layouts.main')
@section('container')
    <div class="container-fluid mt-2">
        <div class="row my-2 d-flex justify-content-center">
            <form action="{{ route('pharmacies.dashboard.orders') }}" method="GET" class="col-12 col-md-12">
                <div class="input-group flex-column flex-md-row">
                    {{-- <input type="text" id="searchInput" class="form-control" placeholder="Cari Pasien, No Rekam Medis"> --}}
                    {{-- Search --}}
                    <div class="col-12 col-md-2 mb-3">
                        <label for="search" class="form-label">Pencarian</label>
                        <input type="text" id="search" name="search" class="form-control"
                            value="{{ request('search') }}" placeholder="Cari No.RM/Nama Pasien">
                    </div>
                    {{-- Location --}}
                    <div class="col-12 col-md-2 mb-3">
                        <label for="location" class="form-label">Lokasi</label>
                        <select class="form-select" id="location" name="location" aria-label="Default select example">
                            @foreach ($locations as $key => $value)
                                <option value="{{ $key }}" {{ $locationId == $key ? 'selected' : '' }}>
                                    {{ $value }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    {{-- Customer Type --}}
                    <div class="col-12 col-md-2 mb-3">
                        <label for="customer_type" class="form-label">Penjamin</label>
                        <select class="form-select" id="customer_type" name="customer_type"
                            aria-label="Default select example">
                            @foreach ($customerTypes as $key => $value)
                                <option value="{{ $key }}" {{ $customerTypeId == $key ? 'selected' : '' }}>
                                    {{ $value }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    {{-- Status Order --}}
                    <div class="col-12 col-md-1 mb-3">
                        <label for="status_order" class="form-label">Status Order</label>
                        <select class="form-select" id="status_order" name="status_order"
                            aria-label="Default select example">
                            @foreach ($statusOrders as $key => $value)
                                <option value="{{ $key }}" {{ $statusOrderId == $key ? 'selected' : '' }}>
                                    {{ $value }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    {{-- Jenis Order --}}
                    <div class="col-12 col-md-1 mb-3">
                        <label for="jenis_order" class="form-label">Jenis Order</label>
                        <select class="form-select" id="jenis_order" name="jenis_order" aria-label="Default select example">
                            @foreach ($jenisOrders as $key => $value)
                                <option value="{{ $key }}" {{ $jenisOrderId == $key ? 'selected' : '' }}>
                                    {{ $value }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    {{-- Date --}}
                    <div class="col-12 col-md-2 mb-3">
                        <label for="date" class="form-label">Tanggal</label>
                        <input type="text" class="form-control flatpickr-date" id="date" name="date"
                            placeholder="dd-mm-yyyy" value="{{ $date }}">
                    </div>
                    <div class="col-md-2">
                        <div class="row">
                            {{-- Sort By --}}
                            <div class="col-12 col-md-9 mb-3">
                                <label for="sort_by" class="form-label">OrderBy</label>
                                <select class="form-select" id="sort_by" name="sort_by"
                                    aria-label="Default select example">
                                    @foreach ($sorts as $key => $value)
                                        <option value="{{ $key }}" {{ $sortId == $key ? 'selected' : '' }}>
                                            {{ $value }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            {{-- Submit Button --}}
                            <div class="col-12 col-md-3 mb-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-success w-100"><i class="bi bi-search"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-bordered text-center table-custom-border">
                <thead>
                    <tr>
                        <th scope="col" class="align-middle">No</th>
                        {{-- <th scope="col" class="align-middle">Nomer Registrasi</th> --}}
                        <th scope="col" class="align-middle">No.RM</th>
                        <th scope="col" class="align-middle">Nama Pasien</th>
                        <th scope="col" class="align-middle">Penjamin</th>
                        <th scope="col" class="align-middle">Status Transaksi</th>
                        <th scope="col" class="align-middle">Status Order</th>
                        <th scope="col" class="align-middle">Progress</th>
                        <th scope="col" class="align-middle">Jenis Resep</th>
                        <th scope="col" class="align-middle">Order Dikirim</th>
                        <th scope="col" class="align-middle">Order Diproses</th>
                        <th scope="col" class="align-middle">Order Selesai</th>
                        <th scope="col" class="align-middle">Lokasi</th>
                        <th scope="col" class="align-middle">Poli</th>
                        <th scope="col" class="align-middle">Orderer</th>
                        {{-- <th scope="col" class="align-middle">Avg Time(minutes)</th> --}}
                    </tr>
                </thead>
                <tbody class="table-group-divider">
                    @forelse ($pharmacies as $key => $pharmacy)
                        <tr>
                            @php
                                $numRow = ($page - 1) * $perPage + $key;
                            @endphp
                            <td class="key">{{ ++$numRow }}</td>
                            <td class="no-rekan-medis">{{ $pharmacy->MedicalNo }}</td>
                            <td class="patient-name">{{ $pharmacy->PatientName }}</td>
                            <td>{{ $pharmacy->Penjamin }}</td>
                            <td class="status-transaksi">{{ $pharmacy->StatusTransaksi }}</td>
                            <td>{{ $pharmacy->StatusOrder }}</td>
                            <td class="text-bar">
                                <div id="progress-bar{{ ++$key }}" class="progress-bar bg-primary"
                                    role="progressbar" style="width: 0%;color: white" aria-valuenow="0"
                                    aria-valuemin="0" aria-valuemax="100"></div>
                                <div id="text-bar{{ $key }}" style="font-size: 15px"></div>
                                <div id="time-running{{ $key }}"></div>
                            </td>
                            <td class="jenis-resep">{{ $pharmacy->JenisResep }}</td>
                            <td>
                                {{ $pharmacy->SendOrderDateTime ? \Carbon\Carbon::parse($pharmacy->SendOrderDateTime)->format('d-m-Y H:i:s') : '-' }}
                            </td>
                            <td>{{ $pharmacy->ProposedDateTime ? \Carbon\Carbon::parse($pharmacy->ProposedDateTime)->format('d-m-Y H:i') : '-' }}
                            </td>
                            <td>{{ $pharmacy->ClosedDateFarmasi }}
                                {{ $pharmacy->ClosedTimeFarmasi ? \Carbon\Carbon::parse($pharmacy->ClosedTimeFarmasi)->format('H:i') : '-' }}
                            </td>
                            <td>{{ $pharmacy->Dispensary }}</td>
                            <td>{{ $pharmacy->Poli }}</td>
                            {{-- <td>{{ $pharmacy->RegistrationNo }}</td> --}}
                            <td>{{ $pharmacy->Orderer }}</td>
                            {{-- <td>10</td> --}}
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
    <div class="d-flex justify-content-center mb-2">
        <form action="{{ route('pharmacies.dashboard.orders') }}" method="GET">
            <!-- Include all existing query parameters as hidden fields -->
            <div class="input-group flex-column flex-md-row">
                @foreach (request()->query() as $key => $value)
                    @if ($key != 'perPage')
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endif
                @endforeach
                <select class="form-select mr-2" id="per_page" name="per_page" aria-label="Default select example"
                    onchange="this.form.submit()">
                    @foreach ($pages as $key => $value)
                        <option value="{{ $key }}" {{ $perPage == $key ? 'selected' : '' }}>
                            {{ $value }}</option>
                    @endforeach
                </select>
            </div>
        </form>
        <div>
            {{ $pharmacies->appends(request()->query())->links() }}
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        let timeOrder = {!! json_encode($timeRespons) !!};
        let dataFromPHP = {!! json_encode($pharmacies) !!};

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

        dataFromPHP.data.forEach((data, index) => {
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
                    document.getElementById(`time-running${i}`).innerText = '00:00:00';
                }
                // Jika date time closed order masih kosong, maka hitung waktu berjalan
                else if (data.ClosedDateFarmasi === null && data.ClosedTimeFarmasi === '') {
                    elapsedTime = currentTime - startTime;
                    // Jika date time closed order sudah terisi, maka hitung waktu berjalan dari waktu start ke closed order
                    // Tampilkan waktu yang sedang berjalan
                    let timeString = getTimeString(currentTime, startTime, 0);
                    document.getElementById(`time-running${i}`).innerText = timeString;
                } else {
                    elapsedTime = diffTime;
                    let timeString = getTimeString(0, 0, diffTimeRunning);
                    document.getElementById(`time-running${i}`).innerText = timeString;
                }

                // Ambil elemen dengan ID "progress-bar" ke-i
                const progressBar = document.getElementById(`progress-bar${i}`);

                // Ambil elemen dengan ID "progress-bar" ke-i
                const textBar = document.getElementById(`text-bar${i}`);

                //Jika setiap waktu masing-masing data masih kosong, maka progress bar = 0%
                if (data.SendOrderDateTime === null &&
                    data.ClosedDateFarmasi === null && data.ClosedTimeFarmasi === '' &&
                    data.ProposedDateTime === null) {
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
        flatpickr('.flatpickr-date', {
            mode: "range",
            dateFormat: "d-m-Y",
            defaultDate: [start_date, end_date], // Mengatur defaultDate ke hari ini dan besok
            maxDate: moment().endOf('day').toDate(),
            onChange: function(selectedDates, dateStr, instance) {
                const start = selectedDates[0];
                const end = selectedDates[1];
                const maxRange = moment(start).add(31, 'days').toDate();

                if (end > maxRange) {
                    instance.setDate([start, maxRange], true);
                }
            }
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