@extends('tenant.layouts.app')

@section('title', 'Importar desde Active Directory')
@section('page_title', 'Importar usuarios desde Active Directory')

@section('topbar_actions')
    <a href="{{ url('/admin/users') }}" class="btn btn-sm btn-outline-secondary">← Volver a Usuarios</a>
@endsection

@push('styles')
<style>
    .ad-step { display:none; }
    .ad-step.active { display:block; }

    .ad-field label { font-size:.82rem; font-weight:600; color:#374151; margin-bottom:.25rem; }
    .ad-field input, .ad-field select { font-size:.875rem; }

    .ad-user-table thead th { font-size:.75rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#64748b; background:#f8fafc; border-bottom:2px solid #e2e8f0; }
    .ad-user-table tbody tr:hover { background:#f8fafc; }
    .ad-user-table td { font-size:.85rem; vertical-align:middle; }

    .badge-dept { background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; padding:.15rem .5rem; border-radius:999px; font-size:.72rem; }
    .badge-exists { background:#fef9c3; color:#92400e; border:1px solid #fde68a; padding:.15rem .5rem; border-radius:999px; font-size:.72rem; font-weight:600; }
    .badge-new    { background:#dcfce7; color:#166534; border:1px solid #86efac; padding:.15rem .5rem; border-radius:999px; font-size:.72rem; font-weight:600; }
    .badge-disa   { background:#fee2e2; color:#991b1b; border:1px solid #fca5a5; padding:.15rem .5rem; border-radius:999px; font-size:.72rem; font-weight:600; }

    #adSpinner { display:none; }
    #adAlert   { display:none; }

    .import-opts label { font-size:.82rem; font-weight:600; color:#374151; margin-bottom:.25rem; }

    #adResultsSection { display:none; }

    .ad-count-badge { font-size:.8rem; font-weight:600; background:#dbeafe; color:#1d4ed8; border:1px solid #93c5fd;
                      padding:.2rem .6rem; border-radius:999px; margin-left:.5rem; }

    .check-col { width:38px; }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">

    {{-- Flash alerts --}}
    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle me-1"></i>{{ session('status') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">

        {{-- ── LEFT: Connection config ─────────────────────────── --}}
        <div class="col-12 col-xl-4">
            <div class="card ic-card h-100">
                <div class="card-header d-flex align-items-center gap-2 py-3">
                    <i class="bi bi-diagram-3 text-primary"></i>
                    <span class="fw-700 fs-6">Conexión al Active Directory</span>
                </div>
                <div class="card-body">
                    <p class="text-muted" style="font-size:.82rem;">
                        Los datos de conexión se usan únicamente para esta sesión y <strong>no se almacenan</strong> en el sistema.
                    </p>

                    <div id="adAlert" class="alert alert-danger py-2 px-3" style="font-size:.83rem;"></div>

                    <form id="adConnForm" novalidate>
                        @csrf

                        {{-- Host --}}
                        <div class="mb-3 ad-field">
                            <label for="adHost">Servidor (host / IP)</label>
                            <input type="text" id="adHost" name="host" class="form-control form-control-sm"
                                   placeholder="dc01.empresa.local" required>
                        </div>

                        {{-- Port + SSL --}}
                        <div class="row g-2 mb-3">
                            <div class="col-7 ad-field">
                                <label for="adPort">Puerto</label>
                                <input type="number" id="adPort" name="port" class="form-control form-control-sm"
                                       value="389" min="1" max="65535" required>
                            </div>
                            <div class="col-5 ad-field d-flex flex-column justify-content-end pb-1">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="adUseSsl" name="use_ssl">
                                    <label class="form-check-label" for="adUseSsl" style="font-size:.82rem;">
                                        Usar LDAPS (SSL)
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Base DN --}}
                        <div class="mb-3 ad-field">
                            <label for="adBaseDn">Base DN</label>
                            <input type="text" id="adBaseDn" name="base_dn" class="form-control form-control-sm"
                                   placeholder="DC=empresa,DC=local" required>
                        </div>

                        {{-- Bind DN --}}
                        <div class="mb-3 ad-field">
                            <label for="adBindDn">Usuario de enlace (Bind DN)</label>
                            <input type="text" id="adBindDn" name="bind_dn" class="form-control form-control-sm"
                                   placeholder="CN=svc-it,OU=Servicios,DC=empresa,DC=local" required>
                        </div>

                        {{-- Bind Password --}}
                        <div class="mb-3 ad-field">
                            <label for="adBindPass">Contraseña del usuario de enlace</label>
                            <div class="input-group input-group-sm">
                                <input type="password" id="adBindPass" name="bind_pass"
                                       class="form-control form-control-sm" required autocomplete="off">
                                <button class="btn btn-outline-secondary" type="button" id="adTogglePass">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Filter --}}
                        <div class="mb-4 ad-field">
                            <label for="adFilter">Filtro LDAP <span class="text-muted fw-400">(opcional)</span></label>
                            <input type="text" id="adFilter" name="filter" class="form-control form-control-sm"
                                   placeholder="(&(objectClass=user)(mail=*))">
                            <div class="form-text">Deja vacío para traer todos los usuarios con email.</div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100" id="adSearchBtn">
                            <span id="adSpinner" class="spinner-border spinner-border-sm me-1"></span>
                            <i class="bi bi-search me-1" id="adSearchIcon"></i>
                            Conectar y buscar usuarios
                        </button>
                    </form>

                    {{-- SSL auto-port hint --}}
                    <p class="text-muted mt-3 mb-0" style="font-size:.76rem;">
                        <i class="bi bi-info-circle me-1"></i>
                        Puerto 389 = LDAP sin cifrar &nbsp;|&nbsp; Puerto 636 = LDAPS (SSL)
                    </p>
                </div>
            </div>
        </div>

        {{-- ── RIGHT: Results + Import ────────────────────────── --}}
        <div class="col-12 col-xl-8">

            {{-- Empty state --}}
            <div id="adEmptyState" class="card ic-card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-people" style="font-size:2.5rem; color:#94a3b8;"></i>
                    <p class="text-muted mt-3 mb-0">Configura la conexión y haz clic en <strong>Conectar y buscar usuarios</strong> para ver los resultados.</p>
                </div>
            </div>

            {{-- Results section (hidden until fetch) --}}
            <div id="adResultsSection">

                {{-- Import options bar --}}
                <div class="card ic-card mb-3">
                    <div class="card-header py-2 d-flex align-items-center gap-2">
                        <i class="bi bi-sliders text-primary"></i>
                        <span class="fw-700" style="font-size:.9rem;">Opciones de importación</span>
                        <span id="adFoundBadge" class="ad-count-badge ms-auto">0 usuarios encontrados</span>
                    </div>
                    <div class="card-body py-3">
                        <div class="row g-3">
                            <div class="col-sm-4 import-opts">
                                <label>Rol</label>
                                <select id="importRole" class="form-select form-select-sm">
                                    <option value="user">Usuario</option>
                                    <option value="admin">Administrador</option>
                                </select>
                            </div>
                            <div class="col-sm-4 import-opts">
                                <label>Perfil de acceso</label>
                                <select id="importProfile" class="form-select form-select-sm">
                                    <option value="">— Sin perfil —</option>
                                    @foreach($permissionProfiles as $key => $profile)
                                        <option value="{{ $key }}">{{ $profile['label'] ?? $key }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-4 import-opts">
                                <label>Estado</label>
                                <select id="importActive" class="form-select form-select-sm">
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                            </div>
                            <div class="col-12 import-opts">
                                <label>Sedes asignadas</label>
                                <select id="importBranchScopes" class="form-select form-select-sm" multiple>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                                <div class="form-text">Mantén Ctrl para seleccionar varias. Opcional para administradores.</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Users table --}}
                <div class="card ic-card">
                    <div class="card-header py-2 d-flex align-items-center gap-2">
                        <div class="form-check mb-0">
                            <input class="form-check-input" type="checkbox" id="adSelectAll">
                            <label class="form-check-label fw-700" for="adSelectAll" style="font-size:.88rem;">
                                Seleccionar todos
                            </label>
                        </div>
                        <span id="adSelectedCount" class="ms-auto text-muted" style="font-size:.8rem;">0 seleccionados</span>
                        <button type="button" class="btn btn-primary btn-sm ms-2" id="adImportBtn" disabled>
                            <i class="bi bi-cloud-download me-1"></i> Importar seleccionados
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 ad-user-table">
                            <thead>
                                <tr>
                                    <th class="check-col"></th>
                                    <th>Nombre</th>
                                    <th>Email</th>
                                    <th>Usuario (SAM)</th>
                                    <th>Departamento</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody id="adUsersTbody">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div><!-- /adResultsSection -->
        </div><!-- /col -->
    </div><!-- /row -->

    {{-- Hidden import form (submitted programmatically) --}}
    <form id="adImportForm" method="POST" action="{{ url('/admin/users/ad-import/import') }}" style="display:none;">
        @csrf
        <div id="hiddenSelectedEmailsContainer"></div>
        <input type="hidden" name="import_role"       id="hiddenRole">
        <input type="hidden" name="import_profile"    id="hiddenProfile">
        <input type="hidden" name="import_is_active"  id="hiddenIsActive">
        <div id="hiddenBranchScopesContainer"></div>
    </form>

</div><!-- /container-fluid -->
@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    /* ── Helpers ─────────────────────────────────────────── */
    const $ = id => document.getElementById(id);
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    function escHtml(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    /* ── Toggle password visibility ─────────────────────── */
    $('adTogglePass').addEventListener('click', () => {
        const inp = $('adBindPass');
        const icon = $('adTogglePass').querySelector('i');
        if (inp.type === 'password') {
            inp.type = 'text';
            icon.className = 'bi bi-eye-slash';
        } else {
            inp.type = 'password';
            icon.className = 'bi bi-eye';
        }
    });

    /* ── Auto-port on SSL toggle ─────────────────────────── */
    $('adUseSsl').addEventListener('change', function () {
        $('adPort').value = this.checked ? '636' : '389';
    });

    /* ── Fetch users from AD ─────────────────────────────── */
    let adUsers = [];

    $('adConnForm').addEventListener('submit', async function (e) {
        e.preventDefault();

        const btn    = $('adSearchBtn');
        const spin   = $('adSpinner');
        const icon   = $('adSearchIcon');
        const alert  = $('adAlert');

        btn.disabled  = true;
        spin.style.display = 'inline-block';
        icon.style.display = 'none';
        alert.style.display = 'none';

        const fd = new FormData(this);

        try {
            const res  = await fetch('{{ url('/admin/users/ad-import/fetch') }}', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: fd,
            });
            const data = await res.json();

            if (!res.ok || data.error) {
                showAlert(data.error ?? data.message ?? 'Error desconocido.');
                return;
            }

            adUsers = data.users ?? [];
            renderTable(adUsers);

        } catch (err) {
            showAlert('Error de red: ' + err.message);
        } finally {
            btn.disabled  = false;
            spin.style.display = 'none';
            icon.style.display = '';
        }
    });

    function showAlert(msg) {
        const el = $('adAlert');
        el.textContent = msg;
        el.style.display = 'block';
    }

    /* ── Render AD users table ───────────────────────────── */
    function renderTable(users) {
        $('adEmptyState').style.display    = 'none';
        $('adResultsSection').style.display = 'block';
        $('adFoundBadge').textContent = users.length + ' usuario' + (users.length !== 1 ? 's' : '') + ' encontrado' + (users.length !== 1 ? 's' : '');

        const tbody = $('adUsersTbody');
        if (users.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">No se encontraron usuarios con los criterios indicados.</td></tr>';
            updateCounts();
            return;
        }

        tbody.innerHTML = users.map((u, idx) => {
            const disabledAttr = u.exists_locally ? 'disabled' : '';
            const checked      = (!u.exists_locally && !u.disabled_in_ad) ? 'checked' : '';
            let statusBadge = '';
            if (u.exists_locally)   statusBadge += '<span class="badge-exists me-1">Ya existe</span>';
            if (u.disabled_in_ad)   statusBadge += '<span class="badge-disa me-1">Desactivado en AD</span>';
            if (!u.exists_locally && !u.disabled_in_ad) statusBadge = '<span class="badge-new">Nuevo</span>';

            return `<tr data-idx="${idx}" class="${u.exists_locally ? 'opacity-50' : ''}">
                <td class="check-col">
                    <input class="form-check-input ad-user-check" type="checkbox"
                           data-idx="${idx}" ${checked} ${disabledAttr}>
                </td>
                <td>${escHtml(u.name)}</td>
                <td class="text-muted">${escHtml(u.email)}</td>
                <td class="text-muted" style="font-size:.8rem;">${escHtml(u.username)}</td>
                <td>${u.department ? '<span class="badge-dept">' + escHtml(u.department) + '</span>' : '<span class="text-muted">—</span>'}</td>
                <td>${statusBadge}</td>
            </tr>`;
        }).join('');

        // attach change listeners
        tbody.querySelectorAll('.ad-user-check').forEach(cb => {
            cb.addEventListener('change', updateCounts);
        });

        updateCounts();
    }

    /* ── Select All ─────────────────────────────────────── */
    $('adSelectAll').addEventListener('change', function () {
        document.querySelectorAll('.ad-user-check:not(:disabled)').forEach(cb => {
            cb.checked = this.checked;
        });
        updateCounts();
    });

    /* ── Count selected ─────────────────────────────────── */
    function updateCounts() {
        const checked = document.querySelectorAll('.ad-user-check:checked').length;
        $('adSelectedCount').textContent = checked + ' seleccionado' + (checked !== 1 ? 's' : '');
        $('adImportBtn').disabled = checked === 0;

        const total     = document.querySelectorAll('.ad-user-check:not(:disabled)').length;
        $('adSelectAll').checked       = total > 0 && checked === total;
        $('adSelectAll').indeterminate = checked > 0 && checked < total;
    }

    /* ── Import ─────────────────────────────────────────── */
    $('adImportBtn').addEventListener('click', function () {
        const selectedEmails = [];
        document.querySelectorAll('.ad-user-check:checked').forEach(cb => {
            const adUser = adUsers[parseInt(cb.dataset.idx)];
            if (adUser?.email) {
                selectedEmails.push(String(adUser.email).trim().toLowerCase());
            }
        });

        if (selectedEmails.length === 0) return;

        if (!confirm(`¿Importar ${selectedEmails.length} usuario(s) desde Active Directory?`)) return;

        const selectedContainer = $('hiddenSelectedEmailsContainer');
        selectedContainer.innerHTML = '';
        selectedEmails.forEach(email => {
            const inp = document.createElement('input');
            inp.type  = 'hidden';
            inp.name  = 'selected_emails[]';
            inp.value = email;
            selectedContainer.appendChild(inp);
        });

        // Build branch scopes inputs
        const container = $('hiddenBranchScopesContainer');
        container.innerHTML = '';
        const scopeSel = $('importBranchScopes');
        Array.from(scopeSel.selectedOptions).forEach(opt => {
            const inp = document.createElement('input');
            inp.type  = 'hidden';
            inp.name  = 'import_branch_scope_ids[]';
            inp.value = opt.value;
            container.appendChild(inp);
        });

        $('hiddenRole').value          = $('importRole').value;
        $('hiddenProfile').value       = $('importProfile').value;
        $('hiddenIsActive').value      = $('importActive').value;

        $('adImportForm').submit();
    });
})();
</script>
@endpush
