@extends('tenant.layouts.app')

@section('content')
<div class="container-fluid py-4">

    {{-- Panel Header --}}
    <div class="mb-4">
        <h1 class="display-6">Panel Técnico Oscuro</h1>
        <p class="text-muted">Gestión avanzada · Catálogos con formularios expandibles</p>
        <hr class="my-3">
    </div>

    {{-- Navigation Pills --}}
    <div class="sticky-navigation mb-4">
        <div class="btn-group-horizontal flex-wrap gap-2">
            <a href="#crud-equipment-brands" class="btn btn-sm btn-outline-primary crud-nav-link">Marcas de equipo</a>
            <a href="#crud-equipment-models" class="btn btn-sm btn-outline-primary crud-nav-link">Modelos de equipo</a>
        </div>
    </div>

    {{-- ===== BRANDS CRUD (COLLAPSIBLE-BASED) ===== --}}
    <div id="crud-equipment-brands" class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h3 class="mb-1">Marcas de equipo</h3>
                <p class="text-muted small">Catálogo de fabricantes con formularios expandibles</p>
            </div>
        </div>

        {{-- Create Brand Accordion --}}
        <div class="accordion mb-4" id="accordionBrands">
            <div class="accordion-item bg-dark text-light border-secondary">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed bg-dark text-light" type="button" data-bs-toggle="collapse" data-bs-target="#brandCreateForm">
                        <i class="bi bi-plus-circle me-2"></i> Nueva marca
                    </button>
                </h2>
                <div id="brandCreateForm" class="accordion-collapse collapse" data-bs-parent="#accordionBrands">
                    <div class="accordion-body bg-dark text-light">
                        <form method="POST" action="{{ url('/admin/equipment-brands') }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Nombre de la marca</label>
                                <input type="text" name="brand_name" class="form-control bg-secondary text-light border-secondary" 
                                       maxlength="120" required placeholder="Ej: Cisco, Ubiquiti, TP-Link">
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-check-circle"></i> Crear
                                </button>
                                <button type="reset" class="btn btn-secondary">
                                    <i class="bi bi-arrow-clockwise"></i> Limpiar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Brands List with Inline Edit --}}
        <div class="accordion" id="accordionBrandsList">
            @forelse ($equipmentBrands as $eb)
                <div class="accordion-item bg-dark text-light border-secondary mb-2">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed bg-dark text-light fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#brand{{ $eb->id }}">
                            <span>{{ $eb->name }}</span>
                            <span class="badge bg-info ms-auto me-3">{{ $eb->equipmentModels()->count() }} modelos</span>
                        </button>
                    </h2>
                    <div id="brand{{ $eb->id }}" class="accordion-collapse collapse" data-bs-parent="#accordionBrandsList">
                        <div class="accordion-body bg-dark text-light">
                            {{-- Edit Form --}}
                            <form method="POST" action="{{ url('/admin/equipment-brands/' . $eb->id) }}" class="mb-4">
                                @csrf
                                @method('PUT')
                                <div class="mb-3">
                                    <label class="form-label">Nombre de la marca</label>
                                    <input type="text" name="brand_name" class="form-control bg-secondary text-light border-secondary" 
                                           maxlength="120" required value="{{ $eb->name }}">
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-warning btn-sm">
                                        <i class="bi bi-pencil"></i> Actualizar
                                    </button>
                                </div>
                            </form>

                            <hr class="bg-secondary">

                            {{-- Delete Form --}}
                            <form method="POST" action="{{ url('/admin/equipment-brands/' . $eb->id) }}" 
                                  data-confirm="¿Eliminar marca {{ $eb->name }}?" 
                                  data-confirm-title="Eliminar marca" 
                                  data-confirm-icon="warning" 
                                  data-confirm-button-text="Sí, eliminar">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="bi bi-trash"></i> Eliminar esta marca
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="alert alert-info mb-0">
                    <i class="bi bi-inbox"></i> No hay marcas registradas. Crea la primera usando el formulario de arriba.
                </div>
            @endforelse
        </div>
    </div>

    {{-- ===== MODELS CRUD (COLLAPSIBLE-BASED) ===== --}}
    <div id="crud-equipment-models" class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h3 class="mb-1">Modelos de equipo</h3>
                <p class="text-muted small">Define modelos con parámetros RF para Access Points</p>
            </div>
        </div>

        {{-- Create Model Accordion --}}
        <div class="accordion mb-4" id="accordionModels">
            <div class="accordion-item bg-dark text-light border-secondary">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed bg-dark text-light" type="button" data-bs-toggle="collapse" data-bs-target="#modelCreateForm">
                        <i class="bi bi-plus-circle me-2"></i> Nuevo modelo
                    </button>
                </h2>
                <div id="modelCreateForm" class="accordion-collapse collapse" data-bs-parent="#accordionModels">
                    <div class="accordion-body bg-dark text-light">
                        <form method="POST" action="{{ url('/admin/equipment-models') }}" id="createModelForm">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Marca</label>
                                    <select name="eqmodel_brand_id" class="form-select bg-secondary text-light border-secondary" required>
                                        <option value="">Selecciona...</option>
                                        @foreach ($equipmentBrands as $eb)
                                            <option value="{{ $eb->id }}">{{ $eb->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Tipo de equipo</label>
                                    <select name="eqmodel_equipment_type" class="form-select bg-secondary text-light border-secondary" id="createModelType" required>
                                        @foreach ($equipmentModelTypes as $etv => $etl)
                                            <option value="{{ $etv }}">{{ $etl }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Nombre del modelo</label>
                                    <input type="text" name="eqmodel_name" class="form-control bg-secondary text-light border-secondary" 
                                           maxlength="120" required placeholder="Ej: UAP-AC-PRO">
                                </div>

                                {{-- AP Fields --}}
                                <div id="createApFields" class="col-12" style="display: none;">
                                    <hr class="bg-secondary">
                                    <h6><i class="bi bi-broadcast"></i> Parámetros RF</h6>
                                    <div class="row g-2">
                                        <div class="col-md-6">
                                            <label class="form-label small">Radio mín. (m)</label>
                                            <input type="number" name="eqmodel_radius_min" class="form-control form-control-sm bg-secondary text-light border-secondary" 
                                                   min="0.1" step="0.1" placeholder="8">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small">Radio máx. (m)</label>
                                            <input type="number" name="eqmodel_radius_max" class="form-control form-control-sm bg-secondary text-light border-secondary" 
                                                   min="0.1" step="0.1" placeholder="30">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small">Señal (dBm)</label>
                                            <input type="number" name="eqmodel_signal_dbm" class="form-control form-control-sm bg-secondary text-light border-secondary" 
                                                   min="-120" max="0" step="1" placeholder="-55">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small">Altura montaje (m)</label>
                                            <input type="number" name="eqmodel_mount_height_m" class="form-control form-control-sm bg-secondary text-light border-secondary" 
                                                   min="0.1" step="0.1" placeholder="2.6">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label small">Patrón radiación</label>
                                            <select name="eqmodel_radiation_pattern" class="form-select form-select-sm bg-secondary text-light border-secondary">
                                                <option value="">Selecciona...</option>
                                                <option value="omni-donut">Omni / dona</option>
                                                <option value="sphere">Esférico</option>
                                                <option value="sector-120">Sectorial 120°</option>
                                                <option value="directional-60">Direccional 60°</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Notas</label>
                                    <textarea name="eqmodel_notes" class="form-control form-control-sm bg-secondary text-light border-secondary" rows="2"></textarea>
                                </div>

                                <div class="col-12">
                                    <button type="submit" class="btn btn-success">
                                        <i class="bi bi-check-circle"></i> Crear modelo
                                    </button>
                                    <button type="reset" class="btn btn-secondary">Limpiar</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Models List with Inline Edit --}}
        <div class="accordion" id="accordionModelsList">
            @forelse ($equipmentModels as $em)
                <div class="accordion-item bg-dark text-light border-secondary mb-2">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed bg-dark text-light fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#model{{ $em->id }}">
                            <span>{{ $em->name }}</span>
                            <span class="badge bg-secondary ms-auto me-3">{{ optional($em->brand)->name ?? 'Sin marca' }}</span>
                        </button>
                    </h2>
                    <div id="model{{ $em->id }}" class="accordion-collapse collapse" data-bs-parent="#accordionModelsList">
                        <div class="accordion-body bg-dark text-light">
                            {{-- Edit Form --}}
                            <form method="POST" action="{{ url('/admin/equipment-models/' . $em->id) }}" class="mb-4">
                                @csrf
                                @method('PUT')
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Marca</label>
                                        <select name="eqmodel_brand_id" class="form-select bg-secondary text-light border-secondary" required>
                                            @foreach ($equipmentBrands as $eb)
                                                <option value="{{ $eb->id }}" @selected($em->brand_id === $eb->id)>{{ $eb->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Tipo de equipo</label>
                                        <select name="eqmodel_equipment_type" class="form-select bg-secondary text-light border-secondary" required>
                                            @foreach ($equipmentModelTypes as $etv => $etl)
                                                <option value="{{ $etv }}" @selected($em->equipment_type === $etv)>{{ $etl }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Nombre del modelo</label>
                                        <input type="text" name="eqmodel_name" class="form-control bg-secondary text-light border-secondary" 
                                               maxlength="120" required value="{{ $em->name }}">
                                    </div>

                                    @if ($em->equipment_type === 'access-point')
                                        <div class="col-12">
                                            <hr class="bg-secondary">
                                            <h6><i class="bi bi-broadcast"></i> Parámetros RF</h6>
                                            <div class="row g-2">
                                                <div class="col-md-6">
                                                    <label class="form-label small">Radio mín. (m)</label>
                                                    <input type="number" name="eqmodel_radius_min" class="form-control form-control-sm bg-secondary text-light border-secondary" 
                                                           min="0.1" step="0.1" value="{{ $em->coverage_radius_min_m }}">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small">Radio máx. (m)</label>
                                                    <input type="number" name="eqmodel_radius_max" class="form-control form-control-sm bg-secondary text-light border-secondary" 
                                                           min="0.1" step="0.1" value="{{ $em->coverage_radius_max_m }}">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small">Señal (dBm)</label>
                                                    <input type="number" name="eqmodel_signal_dbm" class="form-control form-control-sm bg-secondary text-light border-secondary" 
                                                           min="-120" max="0" step="1" value="{{ $em->default_signal_dbm }}">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small">Altura montaje (m)</label>
                                                    <input type="number" name="eqmodel_mount_height_m" class="form-control form-control-sm bg-secondary text-light border-secondary" 
                                                           min="0.1" step="0.1" value="{{ $em->mount_height_m }}">
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label small">Patrón radiación</label>
                                                    <select name="eqmodel_radiation_pattern" class="form-select form-select-sm bg-secondary text-light border-secondary">
                                                        <option value="">Selecciona...</option>
                                                        <option value="omni-donut" @selected($em->radiation_pattern === 'omni-donut')>Omni / dona</option>
                                                        <option value="sphere" @selected($em->radiation_pattern === 'sphere')>Esférico</option>
                                                        <option value="sector-120" @selected($em->radiation_pattern === 'sector-120')>Sectorial 120°</option>
                                                        <option value="directional-60" @selected($em->radiation_pattern === 'directional-60')>Direccional 60°</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="col-12">
                                        <label class="form-label">Notas</label>
                                        <textarea name="eqmodel_notes" class="form-control form-control-sm bg-secondary text-light border-secondary" rows="2">{{ $em->notes }}</textarea>
                                    </div>

                                    <div class="col-12">
                                        <button type="submit" class="btn btn-warning btn-sm">
                                            <i class="bi bi-pencil"></i> Actualizar
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <hr class="bg-secondary">

                            {{-- Delete Form --}}
                            <form method="POST" action="{{ url('/admin/equipment-models/' . $em->id) }}" 
                                  data-confirm="¿Eliminar modelo {{ $em->name }}?" 
                                  data-confirm-title="Eliminar modelo" 
                                  data-confirm-icon="warning" 
                                  data-confirm-button-text="Sí, eliminar">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="bi bi-trash"></i> Eliminar este modelo
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="alert alert-info mb-0">
                    <i class="bi bi-inbox"></i> No hay modelos registrados. Crea el primero usando el formulario de arriba.
                </div>
            @endforelse
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const typeSelect = document.getElementById('createModelType');
    const apFields = document.getElementById('createApFields');
    
    typeSelect.addEventListener('change', function () {
        apFields.style.display = this.value === 'access-point' ? '' : 'none';
    });
});
</script>

<style>
    .sticky-navigation {
        position: sticky;
        top: 0;
        background: white;
        z-index: 100;
        padding: 1rem 0;
        border-bottom: 1px solid #dee2e6;
    }

    .accordion-button:not(.collapsed) {
        background-color: #1f2937 !important;
        color: #e5e7eb !important;
    }

    .accordion-button {
        padding: 0.75rem 1rem;
    }

    .accordion-button:focus {
        border-color: #495057 !important;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important;
    }

    .btn-group-horizontal {
        display: flex;
    }

    .crud-nav-link:hover {
        background-color: #e7f1ff !important;
        color: #0c63e4 !important;
    }
</style>

@endsection
