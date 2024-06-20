<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Add New Products - SantriKoding.com</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body style="background: lightgray">

    <div class="container mt-5 mb-5">
        <div class="row">
            <div class="col-md-12">
                <div class="card border-0 shadow-sm rounded">
                    <div class="card-body">
                        <form action="{{ route('receipts.store') }}" method="POST" enctype="multipart/form-data">

                            @csrf

                            <div class="form-group mb-3">
                                <label class="font-weight-bold">Kode Order / Nomor Instalasi</label>
                                <input type="text" class="form-control @error('code_order') is-invalid @enderror"
                                    name="code_order" value="{{ old('code_order') }}" placeholder="Masukkan Kode Order">

                                <!-- error message untuk code_order -->
                                @error('code_order')
                                    <div class="alert alert-danger mt-2">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label class="font-weight-bold">Nama Pasien</label>
                                <input type="text" class="form-control @error('patient_name') is-invalid @enderror"
                                    name="patient_name" value="{{ old('patient_name') }}"
                                    placeholder="Masukkan Nama Pasien">

                                <!-- error message untuk patient_name -->
                                @error('patient_name')
                                    <div class="alert alert-danger mt-2">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label for="category" class="form-label">Nama Petugas</label>
                                <select class="form-select" name="user_id" aria-label="Default select example">
                                    @foreach ($users as $user)
                                        @if (old('user_id') == $user->id)
                                            <option value="{{ $user->id }}" selected>{{ $user->name }}</option>
                                        @else
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                                @error('user_id')
                                    <div class="alert alert-danger mt-2">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label for="category" class="form-label">Dokter</label>
                                <select class="form-select" name="docter_id" aria-label="Default select example">
                                    @foreach ($docters as $docter)
                                        @if (old('docter_id') == $docter->id)
                                            <option value="{{ $docter->id }}" selected>{{ $docter->name }}</option>
                                        @else
                                            <option value="{{ $docter->id }}">{{ $docter->name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                                @error('docter_id')
                                    <div class="alert alert-danger mt-2">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label for="category" class="form-label">Jenis Poli</label>
                                <select class="form-select" name="poly_id" aria-label="Default select example">
                                    @foreach ($polies as $poly)
                                        @if (old('poly_id') == $poly->id)
                                            <option value="{{ $poly->id }}" selected>{{ $poly->name }}</option>
                                        @else
                                            <option value="{{ $poly->id }}">{{ $poly->name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                                @error('poly_id')
                                    <div class="alert alert-danger mt-2">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label class="font-weight-bold">Deskripsi Resep</label>
                                <textarea class="form-control @error('description_receipt') is-invalid @enderror" name="description_receipt"
                                    rows="5" placeholder="Masukkan Description Product">{{ old('description_receipt') }}</textarea>

                                <!-- error message untuk description_receipt -->
                                @error('description_receipt')
                                    <div class="alert alert-danger mt-2">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="font-weight-bold">Nomor Antrian</label>
                                        <input type="number" class="form-control @error('queue') is-invalid @enderror"
                                            name="queue" value="{{ old('queue') }}"
                                            placeholder="Masukkan Harga Product">

                                        <!-- error message untuk queue -->
                                        @error('queue')
                                            <div class="alert alert-danger mt-2">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="font-weight-bold">Status</label>
                                        <select class="form-control" name="status">
                                            <option value="send">send</option>
                                            <option value="accept">accept</option>
                                            <option value="process">process</option>
                                            <option value="checking">checking</option>
                                            <option value="already">already</option>
                                            <option value="delivered">delivered</option>
                                        </select>
                                        @error('status')
                                            <div class="alert alert-danger mt-2">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-md btn-primary me-3">SAVE</button>
                            <button type="reset" class="btn btn-md btn-warning">RESET</button>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    {{-- <script src="https://cdn.ckeditor.com/4.13.1/standard/ckeditor.js"></script> --}}
    {{-- <script>
        CKEDITOR.replace('description');
    </script> --}}
</body>

</html>
