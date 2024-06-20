<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Data Products - SantriKoding.com</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    @vite('resources/css/app.css')
</head>

<body style="background: lightgray">
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-12">
                <div>
                    <h3 class="text-center my-4">Manajemen Resep Obat</h3>
                    <hr>
                </div>
                <div class="card border-0 shadow-sm rounded">
                    <div class="card-body">
                        <a href="{{ route('receipts.create') }}" class="btn btn-md btn-success mb-3">ADD PRODUCT</a>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th scope="col">ID ORDER</th>
                                    <th scope="col">QUEUE</th>
                                    <th scope="col">PATIENT NAME</th>
                                    <th scope="col">DESCRIPTION RECEIPT</th>
                                    <th scope="col">USER</th>
                                    <th scope="col">DOCTER</th>
                                    <th scope="col">STATUS</th>
                                    <th scope="col">POLY</th>
                                    <th scope="col">STATUS</th>
                                    <th scope="col" style="width: 20%">ACTIONS</th>
                                </tr>
                            </thead>
                            <tbody id="receipts-table-body">
                                @forelse ($receipts as $receipt)
                                    <tr>
                                        <td>{{ $receipt->code_order }}</td>
                                        <td>{{ $receipt->queue }}</td>
                                        <td>{{ $receipt->patient_name }}</td>
                                        <td>{{ $receipt->description_receipt }}</td>
                                        <td>{{ $receipt->user->name }}</td>
                                        <td>{{ $receipt->docter->name }}</td>
                                        <td>{{ $receipt->status }}</td>
                                        <td>{{ $receipt->poly->name }}</td>
                                        <td>{{ $receipt->status }}</td>
                                        <td class="text-center">
                                            <form onsubmit="return confirm('Apakah Anda Yakin ?');"
                                                action="{{ route('receipts.destroy', $receipt->id) }}" method="POST">
                                                <a href="{{ route('receipts.show', $receipt->id) }}"
                                                    class="btn btn-sm btn-dark">SHOW</a>
                                                <a href="{{ route('receipts.edit', $receipt->id) }}"
                                                    class="btn btn-sm btn-primary">EDIT</a>
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">HAPUS</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <div class="alert alert-danger">
                                        Data Products belum Tersedia.
                                    </div>
                                @endforelse
                            </tbody>
                        </table>
                        {{ $receipts->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        //message with sweetalert
        @if (session('success'))
            Swal.fire({
                icon: "success",
                title: "BERHASIL",
                text: "{{ session('success') }}",
                showConfirmButton: false,
                timer: 2000
            });
        @elseif (session('error'))
            Swal.fire({
                icon: "error",
                title: "GAGAL!",
                text: "{{ session('error') }}",
                showConfirmButton: false,
                timer: 2000
            });
        @endif
    </script>
    <script type="text/javascript">
        window.laravel_echo_hostname = "{{ env('LARAVEL_ECHO_HOSTNAME') }}";
    </script>
    <script src="{{ env('LARAVEL_ECHO_HOSTNAME') }}/socket.io/socket.io.js" type="text/javascript"></script>
    @vite('resources/js/app.js')
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof window.Echo !== 'undefined') {
                console.log('window.Echo is defined');
                window.Echo.channel('dashboard-channel').listen('.DashboardEvent', (data) => {
                    console.log(data.receipts);
                    console.log(data.polies)
                    receipt = data.receipts;
                    // Buat variabel untuk menyimpan string HTML dari baris baru
                    let newRowHtml = `
                    <tr>
                        <td>${receipt.code_order}</td>
                        <td>${receipt.queue}</td>
                        <td>${receipt.patient_name}</td>
                        <td>${receipt.description_receipt}</td>
                        <td>${receipt.user.name}</td>
                        <td>${receipt.docter.name}</td>
                        <td>${receipt.status}</td>
                        <td>${receipt.poly.name}</td>
                        <td>${receipt.status}</td>
                        <td class="text-center">
                            <form onsubmit="return confirm('Apakah Anda Yakin ?');"
                                action="{{ route('receipts.destroy', $receipt->id) }}" method="POST">
                                <a href="{{ route('receipts.show', $receipt->id) }}"
                                    class="btn btn-sm btn-dark">SHOW</a>
                                <a href="{{ route('receipts.edit', $receipt->id) }}"
                                    class="btn btn-sm btn-primary">EDIT</a>
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">HAPUS</button>
                            </form>
                        </td>
                    </tr>
                `;
                    // 'beforeend', 'afterbegin'
                    // Masukkan baris baru ke dalam tabel
                    document.getElementById('receipts-table-body').insertAdjacentHTML('afterbegin',
                        newRowHtml);
                });
            } else {
                console.error('window.Echo is not defined. Please check that Echo is properly imported in app.js');
            }
        });
    </script>


</body>

</html>
