@extends('tenant.layouts.app')

@section('content')
<div class="container-fluid py-4">

    {{-- Panel Header --}}
    <div class="mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h1 class="h3 mb-0">Panel Compacto</h1>
                <p class="text-muted small mb-0">Catálogos · Edición directa en tabla</p>
            </div>
        </div>
        <hr class="my-3">
    </div>

    {{-- Navigation Pills --}}
    <div class="mb-3">
        <div class="btn-group gap-1" role="group">
            <a href="#section-brands" class="btn btn-sm btn-outline-primary">Marcas</a>
            <a href="#section-models" class="btn btn-sm btn-outline-primary">Modelos</a>
        </div>
    </div>

    {{-- ===== BRANDS SECTION (COMPACT EDITABLE TABLE) ===== --}}
    <div id="section-brands" class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Marcas de equipo</h5>
            <button class="btn btn-sm btn-success" data-bs-toggle="collapse" data-bs-target="#formBrandNew">
                <i class="bi bi-plus"></i> Agregar
            </button>
        </div>

        {{-- Quick Add Form --}}
        <div class="collapse mb-3" id="formBrandNew">
            <div class="card border-success">
                <div class="card-body p-3">
                    <form method="POST" action="{{ url('/admin/equipment-brands') }}" class="row g-2 align-items-end">
                        @csrf
                        <div class="col-auto flex-grow-1">
                            <input type="text" name="brand_name" class="form-control form-control-sm" 
                                   maxlength="120" required placeholder="Nombre de la marca" autofocus>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-sm btn-success">Crear</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Brands Table --}}
        <div class="table-responsive">
            <table class="table table-sm table-borderless mb-0">
                <thead class="border-bottom">
                    <tr>
                        <th>Marca</th>
                        <th class="text-center" style="width: 80px">Modelos</th>
                        <th style="width: 220px">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($equipmentBrands as $brand)
                        <tr class="border-bottom">
                            <td class="py-2 fw-semibold">{{ $brand->name }}</td>
                            <td class="text-center py-2">
                                <span class="badge bg-light text-dark">{{ $brand->equipmentModels()->count() }}</span>
                            </td>
                            <td class="py-2" style="width: 220px">
                                <button class="btn btn-link btn-sm" data-bs-toggle="collapse" data-bs-target="#editBrand{{ $brand->id }}">
                                    <i class="bi bi-pencil"></i> Editar
                                </button>
                                <form method="POST" action="{{ url('/admin/equipment-brands/' . $brand->id) }}" class="d-inline"
                                      data-confirm="¿Eliminar?" data-confirm-icon="warning">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-link btn-sm text-danger">
                                        <i class="bi bi-trash"></i> Eliminar
                                    </button>
                                </form>
                            </td>
                        </tr>
                        {{-- Inline Edit Row --}}
                        <tr class="collapse bg-light" id="editBrand{{ $brand->id }}">
                            <td colspan="3" class="py-3">
                                <form method="POST" action="{{ url('/admin/equipment-brands/' . $brand->id) }}" class="row g-2 align-items-end">
                                    @csrf
                                    @method('PUT')
                                    <div class="col-auto flex-grow-1">
                                        <input type="text" name="brand_name" class="form-control form-control-sm" 
                                               maxlength="120" value="{{ $brand->name }}" required>
                                    </div>
                                    <div class="col-auto">
                                        <button type="submit" class="btn btn-sm btn-warning">Actualizar</button>
                                        <button type="button" class="btn btn-sm btn-secondary" data-bs-toggle="collapse" data-bs-target="#editBrand{{ $brand->id }}">
                                            Cancelar
                                        </button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center py-3 text-muted">
                                <small><i class="bi bi-inbox"></i> Sin marcas registradas</small>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ===== MODELS SECTION (COMPACT EDITABLE TABLE) ===== --}}
    <div id="section-models">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Modelos de equipo</h5>
            <button class="btn btn-sm btn-success" data-bs-toggle="collapse" data-bs-target="#formModelNew">
                <i class="bi bi-plus"></i> Agregar
            </button>
        </div>

        {{-- Quick Add Form --}}
        <div class="collapse mb-3" id="formModelNew">
            <div class="card border-success">
                <div class="card-body p-3">
                    <form method="POST" action="{{ url('/admin/equipment-models') }}" id="createModelForm" class="row g-2 align-items-end">
                        @csrf
                        <div class="col-auto">
                            <select name="eqmodel_brand_id" class="form-select form-select-sm" required>
                                <option value="">Marca</option>
                                @foreach ($equipmentBrands as $eb)
                                    <option value="{{ $eb->id }}">{{ $eb->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-auto">
                            <select name="eqmodel_equipment_type" class="form-select form-select-sm" id="newModelType" required>
                                @foreach ($equipmentModelTypes as $etv => $etl)
                                    <option value="{{ $etv }}">{{ $etl }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-auto flex-grow-1">
                            <input type="text" name="eqmodel_name" class="form-control form-control-sm" 
                                   maxlength="120" required placeholder="Nombre del modelo" autofocus>
                        </div>

                        <div class="col-auto">
                            <button type="submit" class="btn btn-sm btn-success">Crear</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Models Table --}}
        <div class="table-responsive">
            <table class="table table-sm table-borderless mb-0">
                <thead class="border-bottom">
                    <tr>
                        <th>Modelo</th>
                        <th>Marca · Tipo</th>
                        <th class="text-center" style="width: 80px">Specs</th>
                        <th style="width: 200px">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($equipmentModels as $model)
                        <tr class="border-bottom">
                            <td class="py-2 fw-semibold small">{{ $model->name }}</td>
                            <td class="py-2 small">
                                {{ optional($model->brand)->name ?? '—' }} 
                                <span class="text-muted">({{ $equipmentModelTypes[$model->equipment_type] ?? $model->equipment_type }})</span>
                            </td>
                            <td class="text-center py-2 small">
                                @if ($model->equipment_type === 'access-point' && ($model->coverage_radius_min_m || $model->coverage_radius_max_m))
                                    <span class="badge bg-info">{{ $model->coverage_radius_min_m ?? '?' }}–{{ $model->coverage_radius_max_m ?? '?' }}m</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="py-2">
                                <button class="btn btn-link btn-sm" data-bs-toggle="collapse" data-bs-target="#editModel{{ $model->id }}">
                                    <i class="bi bi-pencil"></i> Editar
                                </button>
                                <form method="POST" action="{{ url('/admin/equipment-models/' . $model->id) }}" class="d-inline"
                                      data-confirm="¿Eliminar?" data-confirm-icon="warning">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-link btn-sm text-danger">
                                        <i class="bi bi-trash"></i> Eliminar
                                    </button>
                                </form>
                            </td>
                        </tr>
                        {{-- Inline Edit Row --}}
                        <tr class="collapse bg-light" id="editModel{{ $model->id }}">
                            <td colspan="4" class="py-3">
                                <form method="POST" action="{{ url('/admin/equipment-models/' . $model->id) }}" class="small">
                                    @csrf
                                    @method('PUT')
                                    <div class="row g-2 align-items-end">
                                        <div class="col-md-3">
                                            <label class="form-label mb-1">Marca</label>
                                            <select name="eqmodel_brand_id" class="form-select form-select-sm" required>
                                                @foreach ($equipmentBrands as $eb)
                                                    <option value="{{ $eb->id }}" @selected($model->brand_id === $eb->id)>{{ $eb->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label mb-1">Tipo</label>
                                            <select name="eqmodel_equipment_type" class="form-select form-select-sm" required>
                                                @foreach ($equipmentModelTypes as $etv => $etl)
                                                    <option value="{{ $etv }}" @selected($model->equipment_type === $etv)>{{ $etl }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label mb-1">Nombre</label>
                                            <input type="text" name="eqmodel_name" class="form-control form-control-sm" 
                                                   maxlength="120" value="{{ $model->name }}" required>
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label mb-1">Notas</label>
                                            <input type="text" name="eqmodel_notes" class="form-control form-control-sm" 
                                                   placeholder="Observaciones" value="{{ $model->notes }}">
                                        </div>

                                        @if ($model->equipment_type === 'access-point')
                                            <div class="col-12 pt-2 border-top">
                                                <div class="row g-2 align-items-end">
                                                    <div class="col-md-2">
                                                        <label class="form-label mb-1">Radio mín (m)</label>
                                                        <input type="number" name="eqmodel_radius_min" class="form-control form-control-sm" 
                                                               min="0.1" step="0.1" value="{{ $model->coverage_radius_min_m }}">
                                                    </div>

                                                    <div class="col-md-2">
                                                        <label class="form-label mb-1">Radio máx (m)</label>
                                                        <input type="number" name="eqmodel_radius_max" class="form-control form-control-sm" 
                                                               min="0.1" step="0.1" value="{{ $model->coverage_radius_max_m }}">
                                                    </div>

                                                    <div class="col-md-2">
                                                        <label class="form-label mb-1">Señal dBm</label>
                                                        <input type="number" name="eqmodel_signal_dbm" class="form-control form-control-sm" 
                                                               min="-120" max="0" step="1" value="{{ $model->default_signal_dbm }}">
                                                    </div>

                                                    <div class="col-md-2">
                                                        <label class="form-label mb-1">Altura (m)</label>
                                                        <input type="number" name="eqmodel_mount_height_m" class="form-control form-control-sm" 
                                                               min="0.1" step="0.1" value="{{ $model->mount_height_m }}">
                                                    </div>

                                                    <div class="col-md-4">
                                                        <label class="form-label mb-1">Patrón</label>
                                                        <select name="eqmodel_radiation_pattern" class="form-select form-select-sm">
                                                            <option value="">—</option>
                                                            <option value="omni-donut" @selected($model->radiation_pattern === 'omni-donut')>Omni/dona</option>
                                                            <option value="sphere" @selected($model->radiation_pattern === 'sphere')>Esférico</option>
                                                            <option value="sector-120" @selected($model->radiation_pattern === 'sector-120')>Sectorial 120°</option>
                                                            <option value="directional-60" @selected($model->radiation_pattern === 'directional-60')>Direccional 60°</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        <div class="col-12 pt-2 border-top">
                                            <button type="submit" class="btn btn-sm btn-warning">
                                                <i class="bi bi-check"></i> Guardar cambios
                                            </button>
                                            <button type="button" class="btn btn-sm btn-secondary" data-bs-toggle="collapse" data-bs-target="#editModel{{ $model->id }}">
                                                Cancelar
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-3 text-muted">
                                <small><i class="bi bi-inbox"></i> Sin modelos registrados</small>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<style>
    .table-sm {
        font-size: 0.875rem;
    }

    .btn-link {
        text-decoration: none;
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }

    .btn-link:hover {
        text-decoration: underline;
    }

    .bg-light {
        background-color: #f8f9fa !important;
    }

    .collapse:not(.show) {
        display: none;
    }

    .collapse.show {
        display: table-row;
    }

    h5 {
        font-size: 1rem;
        font-weight: 600;
    }

    .form-label {
        font-weight: 500;
        font-size: 0.8rem;
    }
</style>

@endsection
