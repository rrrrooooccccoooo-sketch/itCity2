@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div data-swal-flash data-swal-icon="success" data-swal-title="Operación completada" data-swal-text="{{ session('status') }}" data-swal-toast="1" data-swal-position="top-end" data-swal-timer="2600"></div>
                    @endif

                    <p class="mb-3">{{ __('You are logged in!') }}</p>

                    <a href="{{ route('admin.tenants.index') }}" class="btn btn-primary">
                        Administrar tenants ITCity
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
