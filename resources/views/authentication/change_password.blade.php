@extends('layouts.main')
@section('container')
    <form action="{{ route('update-password') }}" method="POST">
        @csrf
        <div class="container-fluid mt-2">
            <div class="mb-3 row">
                <label for="inputPassword" class="col-sm-2 col-form-label">Password Lama</label>
                <div class="col-sm-6">
                    <input name="old_password" type="password" class="form-control" id="old_password">
                </div>
            </div>
            <div class="mb-3 row">
                <label for="inputPassword" class="col-sm-2 col-form-label">Password Baru</label>
                <div class="col-sm-6">
                    <input name="new_password" type="password" class="form-control" id="new_password">
                </div>
            </div>
            <div class="mb-3 row">
                <label for="inputPassword" class="col-sm-2 col-form-label">Konfirmasi Password Baru</label>
                <div class="col-sm-6">
                    <input name="confirm_password" type="password" class="form-control" id="confirm_password">
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        </div>
    </form>
@endsection
