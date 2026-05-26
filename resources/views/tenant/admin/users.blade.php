@extends('tenant.layouts.app')

@section('title', 'Usuarios')
@section('page_title', 'Gestión de Usuarios')

@section('topbar_actions')
    @php
        $adminContextBranchId = max(0, (int) request()->integer('branch_id', (int) ($currentContextBranchId ?? 0)));
        $adminPanelUrl = $adminContextBranchId > 0 ? url('/admin?branch_id=' . $adminContextBranchId) : url('/admin');
        $adImportUrl = $adminContextBranchId > 0 ? url('/admin/users/ad-import?branch_id=' . $adminContextBranchId) : url('/admin/users/ad-import');
    @endphp
    <a href="{{ $adminPanelUrl }}" class="btn btn-sm btn-outline-secondary">← Panel Admin</a>
@endsection

@push('styles')
<style>
    .role-badge-admin { background:#dbeafe; color:#1d4ed8; border:1px solid #93c5fd; padding:.2rem .55rem; border-radius:999px; font-size:.72rem; font-weight:700; }
    .role-badge-user  { background:#e2e8f0; color:#334155; border:1px solid #cbd5e1; padding:.2rem .55rem; border-radius:999px; font-size:.72rem; font-weight:700; }
    .auth-badge-local { background:#dcfce7; color:#166534; border:1px solid #86efac; padding:.2rem .55rem; border-radius:999px; font-size:.72rem; font-weight:700; }
    .auth-badge-ad { background:#ede9fe; color:#5b21b6; border:1px solid #c4b5fd; padding:.2rem .55rem; border-radius:999px; font-size:.72rem; font-weight:700; }
    .perm-badge { display:inline-flex; align-items:center; gap:.25rem; padding:.15rem .5rem; border-radius:999px; font-size:.7rem; font-weight:600; border:1px solid; }
    .perm-badge-yes  { background:#dcfce7; color:#166534; border-color:#86efac; }
    .perm-badge-all  { background:#dbeafe; color:#1d4ed8; border-color:#93c5fd; }
    .perm-badge-no   { background:#f1f5f9; color:#94a3b8; border-color:#cbd5e1; }
    .perm-expand-row td { background:#f8fafc; }
    .perm-toggle { cursor:pointer; padding:0 .3rem; font-size:.75rem; color:#64748b; text-decoration:none; }
    .perm-toggle:hover { color:#0f172a; }
</style>
@endpush

@section('content')
@php
    $canUsersManage = auth()->user()?->hasTenantPermission('users.manage') ?? false;
    $canUsersReset = auth()->user()?->hasTenantPermission('users.reset') ?? false;
@endphp
<div class="container-fluid py-4">

    {{-- Alerts --}}
    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h4 mb-1">Usuarios del sistema</h1>
            <p class="text-muted small mb-0">Crea, edita y asigna roles y campus a los usuarios.</p>
        </div>
        @if ($canUsersManage)
            <div class="d-flex gap-2">
                <a href="{{ $adImportUrl }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-diagram-3 me-1"></i> Importar desde AD
                </a>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalCreateUser">
                    <i class="bi bi-person-plus me-1"></i> Nuevo usuario
                </button>
            </div>
        @endif
    </div>

    {{-- Users Table --}}
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th></th>
                        <th>Nombre</th>
                        <th>Correo electrónico</th>
                        <th>Rol</th>
                        <th>Origen</th>
                        <th>Perfil</th>
                        <th>Estado</th>
                        <th>Firma digital</th>
                        <th>Campus / Sede</th>
                        <th style="width:320px">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        @php
                            $authSource = $user->auth_source ?? 'local';
                            $isActive = $user->is_active ?? true;
                            $scopeBranches = $user->branchScopes;
                            $scopeBranchIds = $scopeBranches->pluck('id')->all();
                            $scopeBranchNames = $scopeBranches->pluck('name')->all();
                            $userProfile = $user->access_profile ?? '';
                            $userPermissions = $user->resolvedTenantPermissions();
                            $isWildcard = in_array('*', (array) data_get($permissionProfiles, $userProfile . '.permissions', []), true);
                            $overrides = is_array($user->permission_overrides) ? $user->permission_overrides : [];
                            $overrideAllow = collect((array) ($overrides['allow'] ?? []))->filter()->values()->all();
                            $overrideDeny = collect((array) ($overrides['deny'] ?? []))->filter()->values()->all();
                            $hasSignature = !empty($user->signature_data_url);
                            $signatureUpdatedAt = $user->signature_updated_at;
                            $signatureUa = trim((string) ($user->signature_last_user_agent ?? ''));
                            $signatureUaShort = $signatureUa !== '' ? mb_substr($signatureUa, 0, 80) : null;
                            $allPermKeys = [
                                'topology.view'    => 'Topología · ver',
                                'topology.manage'  => 'Topología · gestionar',
                                'inventory.view'   => 'Inventario · ver',
                                'inventory.manage' => 'Inventario · gestionar',
                                'inventory.catalogs.view' => 'Catalogos inventario · ver',
                                'inventory.catalogs.manage' => 'Catalogos inventario · gestionar',
                                'monitoring.view'  => 'Monitoreo · ver',
                                'users.view'       => 'Usuarios · ver',
                                'users.manage'     => 'Usuarios · gestionar',
                                'users.reset'      => 'Usuarios · reset contraseña',
                                'tenant.admin'     => 'Administración · instaladores',
                            ];
                            $expandId = 'perms-' . $user->id;
                        @endphp
                        <tr>
                            <td class="text-center" style="width:32px">
                                <a class="perm-toggle" data-bs-toggle="collapse" href="#{{ $expandId }}" aria-expanded="false" title="Ver permisos">
                                    <i class="bi bi-shield"></i>
                                </a>
                            </td>
                            <td class="fw-semibold">
                                {{ $user->name }}
                                @if ($user->id === auth()->id())
                                    <span class="badge bg-secondary ms-1" style="font-size:.65rem">Tú</span>
                                @endif
                            </td>
                            <td class="text-muted">{{ $user->email }}</td>
                            <td>
                                @if ($user->role === 'admin')
                                    <span class="role-badge-admin">Administrador</span>
                                @else
                                    <span class="role-badge-user">Usuario</span>
                                @endif
                            </td>
                            <td>
                                @if ($authSource === 'ad')
                                    <span class="auth-badge-ad">Active Directory</span>
                                @else
                                    <span class="auth-badge-local">Local</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $profileLabel = data_get($permissionProfiles, ($user->access_profile ?? '') . '.label');
                                @endphp
                                <span class="badge bg-light text-dark">{{ $profileLabel ?: 'Sin perfil' }}</span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $isActive ? 'success' : 'secondary' }}">{{ $isActive ? 'Activo' : 'Inactivo' }}</span>
                            </td>
                            <td>
                                @if ($hasSignature)
                                    <span class="badge bg-success">Registrada</span>
                                    <div class="small text-muted">{{ $signatureUpdatedAt ? $signatureUpdatedAt->format('d/m/Y H:i') : 'Sin fecha' }}</div>
                                @else
                                    <span class="badge bg-secondary">Sin firma</span>
                                @endif
                            </td>
                            <td>
                                @if (!empty($scopeBranchNames))
                                    {{ implode(', ', $scopeBranchNames) }}
                                @else
                                    {{ optional($user->branch)->name ?? '— (acceso global)' }}
                                @endif
                            </td>
                            <td>
                                @if ($canUsersManage)
                                    <button class="btn btn-sm btn-outline-primary"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalEditUser"
                                        data-user-id="{{ $user->id }}"
                                        data-user-name="{{ $user->name }}"
                                        data-user-email="{{ $user->email }}"
                                        data-user-role="{{ $user->role }}"
                                        data-user-branch="{{ $user->branch_id ?? '' }}"
                                        data-user-branch-scopes="{{ implode(',', $scopeBranchIds) }}"
                                        data-user-auth-source="{{ $authSource }}"
                                        data-user-is-active="{{ $isActive ? '1' : '0' }}"
                                        data-user-access-profile="{{ $user->access_profile ?? '' }}"
                                        data-user-permissions-allow="{{ implode(',', $overrideAllow) }}"
                                        data-user-permissions-deny="{{ implode(',', $overrideDeny) }}">
                                        <i class="bi bi-pencil"></i> Editar
                                    </button>
                                @endif
                                @if ($canUsersReset && $authSource === 'local' && $isActive && $user->id !== auth()->id())
                                    <form method="POST" action="{{ url('/admin/users/' . $user->id . '/send-reset-link') }}" class="d-inline"
                                          data-confirm="¿Enviar enlace de restablecimiento a {{ $user->email }}?"
                                          data-confirm-title="Enviar enlace"
                                          data-confirm-icon="question"
                                          data-confirm-button-text="Sí, enviar">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-warning">
                                            <i class="bi bi-envelope"></i> Reset
                                        </button>
                                    </form>
                                @endif
                                @if ($canUsersManage && $user->id !== auth()->id())
                                    <form method="POST" action="{{ url('/admin/users/' . $user->id) }}"
                                          class="d-inline"
                                          data-confirm="¿Eliminar al usuario {{ $user->name }}? Esta acción no se puede deshacer."
                                          data-confirm-title="Eliminar usuario"
                                          data-confirm-icon="warning"
                                          data-confirm-button-text="Sí, eliminar">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i> Eliminar
                                        </button>
                                    </form>
                                @endif
                                @if (!$canUsersManage && !$canUsersReset)
                                    <span class="text-muted small">Solo lectura</span>
                                @endif
                            </td>
                        </tr>
                        {{-- Expandable permission row --}}
                        <tr class="perm-expand-row">
                            <td colspan="10" class="p-0">
                                <div class="collapse" id="{{ $expandId }}">
                                    <div class="px-4 py-3">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <i class="bi bi-shield-check text-secondary"></i>
                                            <span class="small fw-semibold text-muted">Permisos activos del perfil</span>
                                            @if ($userProfile)
                                                <span class="badge bg-light text-dark border">{{ data_get($permissionProfiles, $userProfile . '.label', $userProfile) }}</span>
                                            @else
                                                <span class="badge bg-secondary">Sin perfil asignado</span>
                                            @endif
                                        </div>
                                        @if (!empty($overrideAllow) || !empty($overrideDeny))
                                            <div class="small mb-2">
                                                @if (!empty($overrideAllow))
                                                    <div class="text-success">Overrides +: {{ implode(', ', $overrideAllow) }}</div>
                                                @endif
                                                @if (!empty($overrideDeny))
                                                    <div class="text-danger">Overrides -: {{ implode(', ', $overrideDeny) }}</div>
                                                @endif
                                            </div>
                                        @endif
                                        <div class="d-flex flex-wrap gap-2">
                                            @if (!$userProfile)
                                                <span class="perm-badge perm-badge-no"><i class="bi bi-dash-circle"></i> Sin permisos asignados</span>
                                            @elseif ($isWildcard)
                                                @foreach ($allPermKeys as $permKey => $permLabel)
                                                    <span class="perm-badge perm-badge-all" title="Acceso total (*)"><i class="bi bi-check-all"></i> {{ $permLabel }}</span>
                                                @endforeach
                                            @else
                                                @foreach ($allPermKeys as $permKey => $permLabel)
                                                    @if (in_array($permKey, $userPermissions))
                                                        <span class="perm-badge perm-badge-yes"><i class="bi bi-check-circle"></i> {{ $permLabel }}</span>
                                                    @else
                                                        <span class="perm-badge perm-badge-no"><i class="bi bi-x-circle"></i> {{ $permLabel }}</span>
                                                    @endif
                                                @endforeach
                                            @endif
                                        </div>
                                        <div class="mt-3">
                                            <div class="small fw-semibold text-muted mb-2">Auditoría de firma digital</div>
                                            @if ($hasSignature)
                                                <div class="small text-muted">Última actualización: <strong>{{ $signatureUpdatedAt ? $signatureUpdatedAt->format('d/m/Y H:i:s') : 'N/A' }}</strong></div>
                                                <div class="small text-muted">IP: <strong>{{ $user->signature_last_ip ?: 'N/A' }}</strong></div>
                                                <div class="small text-muted">Navegador: <strong title="{{ $signatureUa ?: '' }}">{{ $signatureUaShort ?: 'N/A' }}</strong></div>
                                                <div class="small text-muted">Huella: <strong>{{ strtoupper((string) ($user->signature_hash ?: 'N/A')) }}</strong></div>
                                            @else
                                                <span class="text-muted small">Este usuario no ha registrado firma digital.</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">No hay usuarios registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Note --}}
    <p class="text-muted small mt-3">
        <i class="bi bi-info-circle"></i>
        Los usuarios con rol <strong>Administrador</strong> sin sedes asignadas tienen acceso global.
        Los usuarios con rol <strong>Usuario</strong> solo ven información de sus sedes asignadas.
        Si un usuario con rol <em>Usuario</em> no tiene ninguna sede asignada, el sistema le negará el acceso.
    </p>
</div>

@if ($canUsersManage)
{{-- ===== MODAL: Create User ===== --}}
<div class="modal fade" id="modalCreateUser" tabindex="-1" aria-labelledby="modalCreateUserLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ url('/admin/users') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCreateUserLabel">Nuevo usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre completo <span class="text-danger">*</span></label>
                        <input type="text" name="user_name" class="form-control" required maxlength="255"
                               value="{{ old('user_name') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Correo electrónico <span class="text-danger">*</span></label>
                        <input type="email" name="user_email" class="form-control" required maxlength="255"
                               value="{{ old('user_email') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Origen de autenticación <span class="text-danger">*</span></label>
                        <select name="user_auth_source" id="createUserAuthSource" class="form-select" required>
                            <option value="local" {{ old('user_auth_source', 'local') === 'local' ? 'selected' : '' }}>Local</option>
                            <option value="ad" {{ old('user_auth_source') === 'ad' ? 'selected' : '' }}>Active Directory</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contraseña <span class="text-danger" id="createPasswordRequiredMark">*</span></label>
                        <input type="password" name="user_password" id="createUserPassword" class="form-control" minlength="8" autocomplete="new-password">
                        <div class="form-text" id="createPasswordHelp">Mínimo 8 caracteres. Requerida para cuentas locales.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirmar contraseña <span class="text-danger" id="createPasswordConfirmationRequiredMark">*</span></label>
                        <input type="password" name="user_password_confirmation" id="createUserPasswordConfirmation" class="form-control" autocomplete="new-password">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rol <span class="text-danger">*</span></label>
                        <select name="user_role" class="form-select" required>
                            <option value="user" {{ old('user_role') === 'user' ? 'selected' : '' }}>Usuario (acceso por campus)</option>
                            <option value="admin" {{ old('user_role') === 'admin' ? 'selected' : '' }}>Administrador (acceso total)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Perfil de permisos</label>
                        <select name="user_access_profile" class="form-select">
                            <option value="">— Sin perfil (admins legacy = acceso total) —</option>
                            @foreach ($permissionProfiles as $profileKey => $profile)
                                <option value="{{ $profileKey }}" {{ old('user_access_profile') === $profileKey ? 'selected' : '' }}>{{ $profile['label'] ?? $profileKey }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Permisos adicionales por usuario (Allow)</label>
                        <select name="user_permissions_allow[]" class="form-select" multiple size="5">
                            @foreach ($tenantPermissionOptions as $permissionKey => $permissionLabel)
                                <option value="{{ $permissionKey }}" {{ collect(old('user_permissions_allow', []))->contains($permissionKey) ? 'selected' : '' }}>{{ $permissionLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Permisos denegados por usuario (Deny)</label>
                        <select name="user_permissions_deny[]" class="form-select" multiple size="5">
                            @foreach ($tenantPermissionOptions as $permissionKey => $permissionLabel)
                                <option value="{{ $permissionKey }}" {{ collect(old('user_permissions_deny', []))->contains($permissionKey) ? 'selected' : '' }}>{{ $permissionLabel }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Deny tiene prioridad sobre perfil y allow.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sede principal</label>
                        <select name="user_branch_id" class="form-select">
                            <option value="">— Sin campus asignado (solo para admins) —</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}" {{ old('user_branch_id') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">Opcional. Si defines alcance múltiple, esta sede se agrega automáticamente al alcance.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sedes con acceso</label>
                        <select name="user_branch_scope_ids[]" class="form-select" multiple size="6">
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}" {{ collect(old('user_branch_scope_ids', []))->contains((string) $branch->id) || collect(old('user_branch_scope_ids', []))->contains($branch->id) ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">Para rol <em>Usuario</em>, debes seleccionar al menos una sede entre principal y alcance.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Estado <span class="text-danger">*</span></label>
                        <select name="user_is_active" class="form-select" required>
                            <option value="1" {{ old('user_is_active', '1') === '1' ? 'selected' : '' }}>Activo</option>
                            <option value="0" {{ old('user_is_active') === '0' ? 'selected' : '' }}>Inactivo</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Crear usuario</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ===== MODAL: Edit User ===== --}}
<div class="modal fade" id="modalEditUser" tabindex="-1" aria-labelledby="modalEditUserLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="formEditUser" action="">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditUserLabel">Editar usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre completo <span class="text-danger">*</span></label>
                        <input type="text" name="user_name" id="editUserName" class="form-control" required maxlength="255">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Correo electrónico <span class="text-danger">*</span></label>
                        <input type="email" name="user_email" id="editUserEmail" class="form-control" required maxlength="255">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Origen de autenticación <span class="text-danger">*</span></label>
                        <select name="user_auth_source" id="editUserAuthSource" class="form-select" required>
                            <option value="local">Local</option>
                            <option value="ad">Active Directory</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nueva contraseña</label>
                        <input type="password" name="user_password" class="form-control" minlength="8" autocomplete="new-password"
                               placeholder="Dejar vacío para no cambiar">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirmar nueva contraseña</label>
                        <input type="password" name="user_password_confirmation" class="form-control" autocomplete="new-password">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rol <span class="text-danger">*</span></label>
                        <select name="user_role" id="editUserRole" class="form-select" required>
                            <option value="user">Usuario (acceso por campus)</option>
                            <option value="admin">Administrador (acceso total)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Perfil de permisos</label>
                        <select name="user_access_profile" id="editUserAccessProfile" class="form-select">
                            <option value="">— Sin perfil (admins legacy = acceso total) —</option>
                            @foreach ($permissionProfiles as $profileKey => $profile)
                                <option value="{{ $profileKey }}">{{ $profile['label'] ?? $profileKey }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Permisos adicionales por usuario (Allow)</label>
                        <select name="user_permissions_allow[]" id="editUserPermissionsAllow" class="form-select" multiple size="5">
                            @foreach ($tenantPermissionOptions as $permissionKey => $permissionLabel)
                                <option value="{{ $permissionKey }}">{{ $permissionLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Permisos denegados por usuario (Deny)</label>
                        <select name="user_permissions_deny[]" id="editUserPermissionsDeny" class="form-select" multiple size="5">
                            @foreach ($tenantPermissionOptions as $permissionKey => $permissionLabel)
                                <option value="{{ $permissionKey }}">{{ $permissionLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sede principal</label>
                        <select name="user_branch_id" id="editUserBranch" class="form-select">
                            <option value="">— Sin campus asignado (solo para admins) —</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sedes con acceso</label>
                        <select name="user_branch_scope_ids[]" id="editUserBranchScopes" class="form-select" multiple size="6">
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Estado <span class="text-danger">*</span></label>
                        <select name="user_is_active" id="editUserIsActive" class="form-select" required>
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script>
    @if ($canUsersManage)
    const createAuthSourceSelect = document.getElementById('createUserAuthSource');
    const createPasswordInput = document.getElementById('createUserPassword');
    const createPasswordConfirmationInput = document.getElementById('createUserPasswordConfirmation');
    const createPasswordRequiredMark = document.getElementById('createPasswordRequiredMark');
    const createPasswordConfirmationRequiredMark = document.getElementById('createPasswordConfirmationRequiredMark');
    const createPasswordHelp = document.getElementById('createPasswordHelp');

    const syncCreatePasswordRequirements = () => {
        const isLocal = (createAuthSourceSelect?.value || 'local') === 'local';
        if (createPasswordInput) createPasswordInput.required = isLocal;
        if (createPasswordConfirmationInput) createPasswordConfirmationInput.required = isLocal;
        if (createPasswordRequiredMark) createPasswordRequiredMark.classList.toggle('d-none', !isLocal);
        if (createPasswordConfirmationRequiredMark) createPasswordConfirmationRequiredMark.classList.toggle('d-none', !isLocal);
        if (createPasswordHelp) {
            createPasswordHelp.textContent = isLocal
                ? 'Mínimo 8 caracteres. Requerida para cuentas locales.'
                : 'Para usuarios AD se permite crear la cuenta sin contraseña local.';
        }
    };

    createAuthSourceSelect?.addEventListener('change', syncCreatePasswordRequirements);
    syncCreatePasswordRequirements();

    // Populate edit modal from row data attributes
    document.getElementById('modalEditUser').addEventListener('show.bs.modal', function (event) {
        const btn    = event.relatedTarget;
        const form   = document.getElementById('formEditUser');
        const userId = btn.dataset.userId;

        form.action = '/admin/users/' + userId;

        document.getElementById('editUserName').value  = btn.dataset.userName  || '';
        document.getElementById('editUserEmail').value = btn.dataset.userEmail || '';

        const roleSelect = document.getElementById('editUserRole');
        roleSelect.value = btn.dataset.userRole || 'user';

        const branchSelect = document.getElementById('editUserBranch');
        branchSelect.value = btn.dataset.userBranch || '';

        const branchScopesRaw = (btn.dataset.userBranchScopes || '').trim();
        const branchScopeIds = branchScopesRaw === ''
            ? []
            : branchScopesRaw.split(',').map(id => id.trim()).filter(Boolean);

        const branchScopesSelect = document.getElementById('editUserBranchScopes');
        if (branchScopesSelect) {
            Array.from(branchScopesSelect.options).forEach(option => {
                option.selected = branchScopeIds.includes(option.value);
            });
        }

        const authSourceSelect = document.getElementById('editUserAuthSource');
        authSourceSelect.value = btn.dataset.userAuthSource || 'local';

        const activeSelect = document.getElementById('editUserIsActive');
        activeSelect.value = btn.dataset.userIsActive || '1';

        const accessProfileSelect = document.getElementById('editUserAccessProfile');
        accessProfileSelect.value = btn.dataset.userAccessProfile || '';

        const allowRaw = (btn.dataset.userPermissionsAllow || '').trim();
        const denyRaw = (btn.dataset.userPermissionsDeny || '').trim();
        const allowItems = allowRaw === '' ? [] : allowRaw.split(',').map(item => item.trim()).filter(Boolean);
        const denyItems = denyRaw === '' ? [] : denyRaw.split(',').map(item => item.trim()).filter(Boolean);

        const editAllowSelect = document.getElementById('editUserPermissionsAllow');
        if (editAllowSelect) {
            Array.from(editAllowSelect.options).forEach(option => {
                option.selected = allowItems.includes(option.value);
            });
        }

        const editDenySelect = document.getElementById('editUserPermissionsDeny');
        if (editDenySelect) {
            Array.from(editDenySelect.options).forEach(option => {
                option.selected = denyItems.includes(option.value);
            });
        }

        // Clear password fields when opening
        form.querySelector('[name="user_password"]').value              = '';
        form.querySelector('[name="user_password_confirmation"]').value = '';
    });
    @endif
</script>
@endpush
@endsection
