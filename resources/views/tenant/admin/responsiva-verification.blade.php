@extends('tenant.layouts.app')

@section('title', 'Validacion de Responsiva')
@section('page_title', 'Validacion de Responsiva')

@section('topbar_actions')
    <a href="{{ url('/admin') }}" class="btn btn-sm btn-outline-secondary">Volver al panel</a>
@endsection

@section('content')
<div class="container-fluid py-4">
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title mb-3">Validar folio responsiva</h5>
            <form method="GET" action="{{ url('/admin/responsiva/verify') }}" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">ID activo</label>
                    <input type="number" min="1" name="asset_id" class="form-control" value="{{ $requestedAssetId ?? '' }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Referencia</label>
                    <input type="text" name="reference" maxlength="120" class="form-control" value="{{ $requestedReference ?? '' }}" placeholder="RESP-YYYYMMDD-0001">
                </div>
                <div class="col-md-5">
                    <label class="form-label">Digest SHA-256</label>
                    <input type="text" name="digest" maxlength="64" class="form-control" value="{{ $requestedDigest ?? '' }}" placeholder="64 caracteres hexadecimales">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Validar</button>
                </div>
            </form>
        </div>
    </div>

    @if (!$hasQuery)
        <div class="alert alert-info mb-0">Escanea el QR de la responsiva o captura digest y referencia para validar su autenticidad.</div>
    @else
        @if ($isValid && $matchedAsset)
            <div class="alert alert-success">
                Documento validado correctamente contra el registro del activo.
            </div>
        @else
            <div class="alert alert-danger">
                No se pudo validar el documento con los datos proporcionados.
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <h6 class="mb-3">Resultado de validacion</h6>
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="small text-muted">Activo</div>
                        <div class="fw-semibold">{{ $matchedAsset ? (($matchedAsset->asset_tag ?: 'Sin etiqueta') . ' (#' . $matchedAsset->id . ')') : 'No encontrado' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="small text-muted">Referencia registrada</div>
                        <div class="fw-semibold">{{ $storedReference ?: 'N/A' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="small text-muted">Sede</div>
                        <div class="fw-semibold">{{ $matchedAsset ? (optional($matchedAsset->branch)->name ?: 'N/A') : 'N/A' }}</div>
                    </div>
                    <div class="col-md-12">
                        <div class="small text-muted">Digest registrado</div>
                        <div class="text-break"><code>{{ strtoupper($storedDigest ?: 'N/A') }}</code></div>
                    </div>
                    <div class="col-md-12">
                        <div class="small text-muted">Digest consultado</div>
                        <div class="text-break"><code>{{ strtoupper($requestedDigest ?: 'N/A') }}</code></div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Generado por</div>
                        <div>{{ $storedGeneratedBy ?: 'N/A' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Fecha generacion registrada</div>
                        <div>{{ $storedGeneratedAt ? \Illuminate\Support\Carbon::parse($storedGeneratedAt)->format('d/m/Y H:i:s') : 'N/A' }}</div>
                    </div>
                    <div class="col-md-12">
                        <div class="small text-muted">Huella de firma de entrega</div>
                        <div class="text-break"><code>{{ strtoupper($storedSignatureHash ?: 'N/A') }}</code></div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
