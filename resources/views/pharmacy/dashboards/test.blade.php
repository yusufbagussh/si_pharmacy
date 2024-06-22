@extends('layouts.main')
@section('container')
    <div class="container-fluid">
        <div class="row my-3 d-flex col-12 col-md-12">
            <form class="" action="" method="GET">
                <div class="input-group flex-column flex-md-row justify-content-center">
                    <div class="col-12 col-md-2">
                        <select class="form-select mb-2 mb-md-0" id="location" name="location"
                            aria-label="Default select example">
                            <option value="tes">Test</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <input type="text" class="form-control flatpickr-date mb-2 mb-md-0" id="date" name="date"
                            placeholder="dd-mm-yyyy">
                    </div>
                    <div class="col-12 col-md-1 align-items-end">
                        <button type="submit" class="btn btn-success"><i class="bi bi-search"></i></button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
