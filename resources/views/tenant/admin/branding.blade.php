@extends('tenant.layouts.app')

@section('title', 'Branding Tenant')
@section('page_title', 'Branding de la Empresa')

@section('content')
<div class="container-fluid px-0">
    <div class="ic-card p-4">
        <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-3">
            <div>
                <h1 class="h4 mb-1">Configuración de identidad visual</h1>
                <p class="text-muted mb-0">Personaliza nombre comercial, logo y paleta de colores para este tenant.</p>
            </div>
        </div>

        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Revisa los datos:</strong>
                <ul class="mb-0 mt-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ url('/admin/branding') }}" enctype="multipart/form-data" class="row g-3">
            @csrf

            <div class="col-12 col-lg-7">
                <label for="branding_company_name" class="form-label">Nombre de la empresa</label>
                <input
                    type="text"
                    class="form-control"
                    id="branding_company_name"
                    name="branding_company_name"
                    maxlength="120"
                    value="{{ old('branding_company_name', $branding->company_name) }}"
                    placeholder="Ej. Hospital ERP Occidente"
                >
            </div>

            <div class="col-12 col-lg-5">
                <label for="branding_logo" class="form-label">Logo</label>
                <input type="file" class="form-control" id="branding_logo" name="branding_logo" accept=".png,.jpg,.jpeg,.webp,.svg">
                @if (!empty($branding->logo_path))
                    <div class="d-flex align-items-center gap-3 mt-2">
                        <img src="{{ $brandingLogoUrl }}" alt="Logo actual" style="max-height:48px; max-width:180px; object-fit:contain;">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="branding_remove_logo" value="1" id="branding_remove_logo">
                            <label class="form-check-label" for="branding_remove_logo">Quitar logo actual</label>
                        </div>
                    </div>
                @endif
            </div>

            <div class="col-12"><hr class="my-1"></div>

            <div class="col-6 col-md-4 col-lg-2">
                <label class="form-label" for="branding_primary_color">Color primario</label>
                <input type="color" class="form-control form-control-color w-100" id="branding_primary_color" name="branding_primary_color" value="{{ old('branding_primary_color', $branding->primary_color ?? '#2563eb') }}" title="Color primario">
            </div>

            <div class="col-6 col-md-4 col-lg-2">
                <label class="form-label" for="branding_secondary_color">Color secundario</label>
                <input type="color" class="form-control form-control-color w-100" id="branding_secondary_color" name="branding_secondary_color" value="{{ old('branding_secondary_color', $branding->secondary_color ?? '#0f172a') }}" title="Color secundario">
            </div>

            <div class="col-6 col-md-4 col-lg-2">
                <label class="form-label" for="branding_accent_color">Color acento</label>
                <input type="color" class="form-control form-control-color w-100" id="branding_accent_color" name="branding_accent_color" value="{{ old('branding_accent_color', $branding->accent_color ?? '#38bdf8') }}" title="Color acento">
            </div>

            <div class="col-6 col-md-4 col-lg-2">
                <label class="form-label" for="branding_background_color">Color fondo</label>
                <input type="color" class="form-control form-control-color w-100" id="branding_background_color" name="branding_background_color" value="{{ old('branding_background_color', $branding->background_color ?? '#f1f5f9') }}" title="Color de fondo">
            </div>

            <div class="col-6 col-md-4 col-lg-2">
                <label class="form-label" for="branding_text_color">Color texto</label>
                <input type="color" class="form-control form-control-color w-100" id="branding_text_color" name="branding_text_color" value="{{ old('branding_text_color', $branding->text_color ?? '#111827') }}" title="Color de texto">
            </div>

            <div class="col-12">
                <button type="submit" class="btn btn-primary">Guardar branding</button>
                <a href="{{ url('/admin') }}" class="btn btn-outline-secondary">Volver al panel</a>
            </div>
        </form>
    </div>
</div>
@endsection
