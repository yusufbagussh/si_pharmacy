@extends('layouts.main')
@section('container')
    <div class="container mt-4">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('update-password') }}" method="POST" enctype="multipart/form-data">
                    @if (session()->has('passwordError'))
                        <div class="col-md-8 alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('passwordError') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    @csrf
                    <div class="mb-3 row">
                        <label for="inputPassword" class="col-md-2 col-form-label">Password Lama</label>
                        <div class="col-md-6">
                            <input name="old_password" type="password"
                                class="form-control @error('old_password') is-invalid @enderror" id="old_password">
                            @error('old_password')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label for="inputPassword" class="col-md-2 col-form-label">Password Baru</label>
                        <div class="col-md-6">
                            <input name="new_password" type="password"
                                class="form-control @error('new_password') is-invalid @enderror" id="new_password">
                            @error('new_password')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label for="inputPassword" class="col-md-2 col-form-label">Konfirmasi Password Baru</label>
                        <div class="col-md-6">
                            <input name="confirm_password" type="password"
                                class="form-control @error('confirm_password') is-invalid @enderror" id="confirm_password">
                            @error('confirm_password')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            </div>
        </div>
    </div>
@endsection
