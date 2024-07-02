@extends('layouts.main')
@section('container')
    <div class="container-fluid mt-2">
        <div class="row my-2 d-flex">
            <form action="{{ route('pharmacies.dashboard.orders') }}" method="GET" class="col-12 col-md-12">
                <div class="input-group flex-column flex-md-row justify-content-center">
                    {{-- Search --}}
                    <div class="col-12 col-md-2 mb-2">
                        <label for="search" class="form-label">Pencarian</label>
                        <input type="text" id="search" name="search" class="form-control"
                            value="{{ request('search') }}" placeholder="Cari No.RM/Nama Pasien">
                    </div>
                    {{-- Location --}}
                    <div class="col-12 col-md-6 mb-2">
                        <div class="row">
                            <div class="custom-inline-form">
                                {{-- Location --}}
                                <div class="col-12 col-md-3">
                                    <label for="location" class="form-label">Lokasi</label>
                                    <select class="form-select" id="location" name="location"
                                        aria-label="Default select example">
                                        @foreach ($locations as $key => $value)
                                            <option value="{{ $key }}" {{ $locationId == $key ? 'selected' : '' }}>
                                                {{ $value }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                {{-- Customer Type --}}
                                <div class="col-12 col-md-3">
                                    <label for="customer_type" class="form-label">Penjamin</label>
                                    <select class="form-select" id="customer_type" name="customer_type"
                                        aria-label="Default select example">
                                        @foreach ($customerTypes as $key => $value)
                                            <option value="{{ $key }}"
                                                {{ $customerTypeId == $key ? 'selected' : '' }}>
                                                {{ $value }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                {{-- Status Order --}}
                                <div class="col-12 col-md-3">
                                    <label for="status_order" class="form-label">Status Order</label>
                                    <select class="form-select" id="status_order" name="status_order"
                                        aria-label="Default select example">
                                        @foreach ($statusOrders as $key => $value)
                                            <option value="{{ $key }}"
                                                {{ $statusOrderId == $key ? 'selected' : '' }}>
                                                {{ $value }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                {{-- Jenis Order --}}
                                <div class="col-12 col-md-3">
                                    <label for="jenis_order" class="form-label">Jenis Order</label>
                                    <select class="form-select" id="jenis_order" name="jenis_order"
                                        aria-label="Default select example">
                                        @foreach ($jenisOrders as $key => $value)
                                            <option value="{{ $key }}"
                                                {{ $jenisOrderId == $key ? 'selected' : '' }}>
                                                {{ $value }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- Date --}}
                    <div class="col-12 col-md-2 mb-2 after">
                        <label for="date" class="form-label">Tanggal</label>
                        <input type="text" class="form-control flatpickr-date" id="date" name="date"
                            placeholder="dd-mm-yyyy" value="{{ $date }}">
                    </div>
                    <div class="col-12 col-md-2 mb-2">
                        <div class="row">
                            {{-- Sort By --}}
                            <label for="sort_by" class="form-label">OrderBy</label>
                            <div class="custom-inline-form">
                                <select class="form-select" id="sort_by" name="sort_by"
                                    aria-label="Default select example">
                                    @foreach ($sorts as $key => $value)
                                        <option value="{{ $key }}" {{ $sortId == $key ? 'selected' : '' }}>
                                            {{ $value }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn btn-success"><i class="bi bi-search"></i></button>
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
                        {{-- <th scope="col" class="align-middle">Lokasi</th> --}}
                        <th scope="col" class="align-middle">Poli</th>
                        <th scope="col" class="align-middle">User Input</th>
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
                            {{-- @if ($pharmacy->GCCustomerType == 'X004^500')
                                <td>BPJS - Kemenkes</td>
                            @elseif(
                                $pharmacy->GCCustomerType == 'X004^999' ||
                                    $pharmacy->GCCustomerType == 'X004^251' ||
                                    $pharmacy->GCCustomerType == 'X004^300')
                                <td>Personal</td>
                            @elseif($pharmacy->GCCustomerType == 'X004^100' || $pharmacy->GCCustomerType == 'X004^200')
                                <td>Asuransi</td>
                            @else
                                <td>{{ $pharmacy->Penjamin }}</td>
                            @endif --}}
                            <td>@checkCustomerType($pharmacy->GCCustomerType, $pharmacy->Penjamin)</td>
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
                                {{ $pharmacy->SendOrderDateTime ? \Carbon\Carbon::parse($pharmacy->SendOrderDateTime)->format('j F Y H:i') : '-' }}
                            </td>
                            <td>{{ $pharmacy->ProposedDateTime ? \Carbon\Carbon::parse($pharmacy->ProposedDateTime)->format('j F Y H:i') : '-' }}
                            </td>
                            <td>{{ $pharmacy->ClosedDateFarmasi ? \Carbon\Carbon::parse($pharmacy->ProposedDateTime)->format('j F Y') : '-' }}
                                {{ $pharmacy->ClosedTimeFarmasi ? \Carbon\Carbon::parse($pharmacy->ClosedTimeFarmasi)->format('H:i') : '' }}
                            </td>
                            {{-- <td>{{ $pharmacy->Dispensary }}</td> --}}
                            <td>{{ $pharmacy->Poli }}</td>
                            <td>{{ $pharmacy->Orderer }}</td>
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
        //Get data dari DashboardController
        let timeOrder = @json($timeRespons);
        let dataFromPHP = @json($pharmacies);

        //Mendapatkan selisih waktu dengan format tanggal dan waktu terpisah
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

        //Mengkonversi tanggal dan waktu yang terpisah menjadi satu
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

        //Mengconvert time ke string dengan format hh:mm:ss
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

        //Iterasi data dari controller
        dataFromPHP.data.forEach((data, index) => {
            let i = index + 1;

            //Fungsi update progress bar
            function updateProgressBar() {
                let standarTime = 0;
                if (data.JenisResep === 'RACIKAN') {
                    standarTime = timeOrder.racikan; // standard time untuk resep jenis racikan 60 menit
                } else {
                    standarTime = timeOrder.nonRacikan; // standart time untuk resep jenis non racikan 30 menit
                }

                let diffTime = 0; // Selisih waktu untuk progress bar
                let diffTimeRunning = 0; // Selisih waktu untuk time running
                let percentageElapsedTime = 0; // Persentase waktu yang sudah berjalan

                let startTime = null; // Waktu mulai order
                let currentTime = null; // Waktu live saat ini
                let elapsedTime = 0; // Waktu yang sudah berjalan

                currentTime = new Date(); //Ambil waktu saat ini

                //cek apakah data SendOrderDateTime tidak kosong
                if (data.SendOrderDateTime !== null) {
                    const sendOrderDateTimeObject = convertDateTime(data.SendOrderDateTime, '');
                    //set start time dengan sendOrderDateTime
                    startTime = sendOrderDateTimeObject;

                    // Jika date time closed order sudah terisi, maka hitung selisih waktu
                    if (data.ClosedDateFarmasi !== null && data.ClosedTimeFarmasi !== '') {
                        diffTime = setAndGetDiffDateTime(data.ClosedDateFarmasi, data.ClosedTimeFarmasi,
                            sendOrderDateTimeObject);
                        diffTimeRunning = setAndGetDiffDateTime(data.ClosedDateFarmasi, data.ClosedTimeFarmasi,
                            sendOrderDateTimeObject);
                        // Jika selisih waktu kurang dari standar waktu, maka set selisih waktu dengan standar waktu
                        if (diffTime < standarTime) {
                            diffTime = standarTime;
                        }
                    }
                    //Jika close date time masih kosong, tetapi proposed date time tidak kosong maka hitung selisih waktu
                    else if (data.ProposedDateTime !== null) {
                        proposedDateTimeObject = convertDateTime(data.ProposedDateTime, '');
                        diffTime = proposedDateTimeObject - sendOrderDateTimeObject;
                    }
                }
                //Jika sendOrderDateTime kosong, tetapi proposedDateTime tidak kosong
                else if (data.ProposedDateTime !== null) {
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

                // Jika semua waktu masih kosong, maka set waktu berjalan = 00:00:00
                if (data.SendOrderDateTime === null &&
                    data.ClosedDateFarmasi === null && data.ClosedTimeFarmasi === '' &&
                    data.ProposedDateTime === null) {
                    document.getElementById(`time-running${i}`).innerText = '00:00:00';
                }
                // Tetapi jika hanya date time closed order yang masih kosong, maka hitung waktu berjalan
                else if (data.ClosedDateFarmasi === null && data.ClosedTimeFarmasi === '') {
                    elapsedTime = currentTime - startTime;
                    let timeString = getTimeString(currentTime, startTime, 0);
                    document.getElementById(`time-running${i}`).innerText = timeString;
                }
                // Jika date time closed order sudah terisi, maka hitung waktu berjalan dari waktu start ke closed order
                else {
                    elapsedTime = diffTime;
                    let timeString = getTimeString(0, 0, diffTimeRunning);
                    document.getElementById(`time-running${i}`).innerText = timeString;
                }

                // Ambil elemen dengan ID "progress-bar" ke-i
                const progressBar = document.getElementById(`progress-bar${i}`);

                // Ambil elemen dengan ID "progress-bar" ke-i
                const textBar = document.getElementById(`text-bar${i}`);

                //Jika setiap waktu masing-masing data masih kosong, maka set progress bar = 0%
                if (data.SendOrderDateTime === null &&
                    data.ClosedDateFarmasi === null && data.ClosedTimeFarmasi === '' &&
                    data.ProposedDateTime === null) {
                    textBar.innerText = '0%';
                    progressBar.style.display = "none";
                }
                // Jika sudah ada waktu terisi, maka hitung persentase waktu yang sudah berjalan
                else {
                    // Hitung persentase waktu yang sudah berjalan
                    percentageElapsedTime = (elapsedTime / standarTime) * 100;
                    //Jika persentase waktu yang sudah berjalan masih <= 100 maka tampilkan style progress bar sesuai persentase
                    if (percentageElapsedTime <= 100) {
                        progressBar.style.width = percentageElapsedTime + '%';
                    }
                    // Jika persentase waktu yang sudah berjalan lebih dari 100, maka set style progress bar = 100%
                    else {
                        progressBar.style.width = '100%';
                    }
                    progressBar.innerText = percentageElapsedTime.toFixed(2) + '%';
                    textBar.style.display = "none";
                }

                // Periksa jika nilai persentase selisih waktu lebih dari 100
                if (percentageElapsedTime > 100) {
                    // Jika ya, ubah warna bar menjadi merah
                    progressBar.className = "progress-bar bg-danger";
                    // Periksa jika nilai persentase selisih waktu = 100
                } else if (percentageElapsedTime === 100) {
                    // Jika ya, ubah warna bar menjadi hijau
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
        // Get data date range dari controller
        let date_range_str = @json($date);

        //Convert dan pecah menjadi 2 bagian yaitu start_date dan end_date
        if (date_range_str !== null) {
            //Cek apakah di dalam string mengandung 'to'
            if (date_range_str.includes('to')) {
                const [start_date_str, end_date_str] = date_range_str.split(' to ');

                // Mengonversi string menjadi objek Date menggunakan Moment.js
                start_date = moment(start_date_str, "DD-MM-YYYY").toDate();
                end_date = moment(end_date_str, "DD-MM-YYYY").toDate();
            }
            //Jika tidak mengandung 'to' maka date_range_str hanya mengandung current date,
            //Lalu convert menjadi objek Date
            else {
                // Mengonversi string menjadi objek Date menggunakan Moment.js
                start_date = moment(date_range_str, "DD-MM-YYYY").toDate();
                end_date = moment(date_range_str, "DD-MM-YYYY").toDate();
            }
        } else {
            start_date = new Date();
            end_date = new Date();
        }

        // Inisialisasi Flatpickr Menjadi Range Date
        flatpickr('.flatpickr-date', {
            mode: "range",
            dateFormat: "d-m-Y",
            defaultDate: [start_date, end_date],
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"
        integrity="sha512-VEd+nq25CkR676O+pLBnDW09R7VQX9Mdiij052gVCp5yVH3jGtH70Ho/UUv4mJDsEdTvqRCFZg0NKGiojGnUCw=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    @if (session()->has('passwordSuccess'))
        <script>
            // toastr.options = {
            //     "closeButton": true,
            //     "debug": false,
            //     "newestOnTop": true,
            //     "progressBar": true,
            //     "positionClass": "toast-top-right",
            //     "preventDuplicates": false,
            //     "onclick": null,
            //     "showDuration": "300",
            //     "hideDuration": "1000",
            //     "timeOut": "1000", // Notifikasi akan tampil selama 1 detik
            //     "extendedTimeOut": "1000", // Waktu tambahan sebelum notifikasi benar-benar hilang
            //     "showEasing": "swing",
            //     "hideEasing": "linear",
            //     "showMethod": "fadeIn",
            //     "hideMethod": "fadeOut"
            // };

            toastr.success("{{ session('passwordSuccess') }}", 'Success!', {
                timeOut: 2000,
                extendedTimeOut: 1000,
                progressBar: true,
            })
        </script>
    @endif
    <script>
        // Fungsi untuk reload halaman setiap 30 detik
        function reloadPage() {
            location.reload();
        }

        // Jalankan fungsi reload setiap 30 detik
        setInterval(reloadPage, 60000);
    </script>
@endsection
