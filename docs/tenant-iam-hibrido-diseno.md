# Diseño propuesto: Administración de usuarios y permisos por tenant (híbrido AD + local)

## 1) Estado actual del proyecto (confirmado)

- Ya existe CRUD de usuarios por tenant:
  - `GET /admin/users`, `POST /admin/users`, `PUT /admin/users/{user}`, `DELETE /admin/users/{user}`.
- Existe control de acceso básico por rol en `users.role`:
  - `admin` y `user`.
- Existe scope por sede/campus en `users.branch_id`.
- Middleware de admin tenant: `tenant.admin` (valida `isAdmin()`).
- Hay vistas de reset de contraseña y controladores Auth de Laravel, pero **en tenant no está explícito un flujo de recuperación administrado por el módulo de usuarios**.
- No hay integración LDAP/Active Directory actualmente.
- No existe tabla de password reset en migraciones tenant (`database/migrations/tenant`).

## 2) Objetivo funcional

Construir un módulo IAM por tenant con complejidad controlada (no hiper-granular), que permita:

1. Gestión de usuarios tenant (locales y externos AD).
2. Control claro de permisos por perfil (roles + permisos por módulo/acción).
3. Recuperación y reseteo de contraseñas para cuentas locales.
4. Integración híbrida con Active Directory:
   - Login federado para usuarios AD.
   - Usuarios locales para excepciones/no AD.

## 3) Modelo recomendado (granularidad media)

### 3.1 RBAC simple con permisos por módulo

Usar un esquema de 3 capas:

- **Rol** (p. ej. `tenant_admin`, `operador`, `auditor`, `inventario_manager`)
- **Permiso** (p. ej. `users.view`, `users.create`, `inventory.edit`, `monitoring.view`)
- **Asignación** usuario↔roles (muchos a muchos)

Evitar reglas complejas por campo/celda; mantener permiso por recurso + acción.

### 3.2 Scope por sede/campus

Separar explícitamente:

- Permiso funcional (qué puede hacer)
- Scope de datos (sobre qué sedes puede hacerlo)

Recomendación:

- `all_branches = true/false`
- Tabla pivote `user_branch_scopes` para accesos múltiples por sede.

Con eso puedes manejar:
- Admin global tenant.
- Operador multi-sede.
- Usuario restringido a 1 sede.

### 3.3 Origen de identidad (híbrido)

Agregar en `users`:

- `auth_source`: `local` | `ad`
- `external_id` (GUID/SID AD)
- `is_active`
- `last_synced_at`

Regla recomendada:

- `local`: password gestionada internamente.
- `ad`: autenticación por LDAP/AD (sin password local operativa).

## 4) Password reset

### Local

- Habilitar flujo estándar de Laravel por tenant:
  - Solicitud de enlace.
  - Token temporal.
  - Cambio de password.
- Asegurar tabla de tokens en DB tenant (`password_reset_tokens`).

### AD

- No resetear password desde ITCity (delegar a AD).
- Mostrar mensaje guiado al usuario AD con canal de soporte TI.

## 5) Integración Active Directory (híbrida)

## 5.1 Estrategia técnica sugerida

- Integrar LDAP/AD con una librería de Laravel (por ejemplo, LdapRecord-Laravel).
- Definir provider de autenticación AD para tenant.
- Resolver mapeo de usuario AD → usuario local tenant por:
  - `external_id` preferente
  - o `email` como fallback controlado

### 5.2 Modos de provisión

- **Just-in-time (JIT)**: se crea/actualiza usuario local al primer login AD.
- **Sync programado**: job nocturno para altas/bajas/cambios de atributos.

Recomendación inicial: JIT + sync diario.

### 5.3 Gobierno y seguridad

- Si usuario AD está deshabilitado/bloqueado, marcar `is_active=false` local.
- No permitir login local a usuarios `auth_source=ad` salvo política de contingencia.
- Auditar cada login y sincronización.

## 6) UI de administración tenant (MVP)

Pantalla `Admin > Usuarios` con:

1. Filtros: estado, origen (`local/ad`), rol, sede.
2. Alta local (nombre, email, rol/perfil, sedes).
3. Alta AD (búsqueda/importación desde AD por email o cuenta).
4. Edición:
   - perfiles/roles
   - scopes de sedes
   - activar/desactivar
5. Acciones rápidas:
   - reset password (solo local)
   - forzar cierre de sesiones (opcional fase 2)

## 7) Roadmap de implementación por fases

## Fase 1 (rápida, 1-2 sprints)

- Mantener rol simple actual y reforzar módulo de usuarios.
- Añadir `auth_source`, `is_active`.
- Password reset tenant para cuentas locales.
- Mejorar UI usuarios (filtros, estado, origen, reset manual admin).

Resultado: control básico sólido sin AD todavía.

## Fase 2 (RBAC medio)

- Introducir roles configurables y permisos por módulo/acción.
- Implementar scopes multi-sede.
- Middleware/policies por permiso.

Resultado: control fino suficiente sin ser hiper-granular.

## Fase 3 (AD híbrido)

- Integración LDAP/AD + JIT provisioning.
- Mapeo de grupos AD → roles tenant.
- Sync programado y desactivación automática.

Resultado: coexistencia AD + usuarios locales.

## 8) Riesgos y mitigaciones

- **Riesgo:** mezcla de auth central/tenant.
  - Mitigar separando claramente rutas de auth tenant y validando dominio tenant en login.
- **Riesgo:** drift entre AD y roles locales.
  - Mitigar con prioridad definida (AD group mapping) y sync recurrente.
- **Riesgo:** sobre-granularidad inmantenible.
  - Mitigar con catálogo acotado de permisos por módulo.

## 9) Decisiones recomendadas (para cerrar alcance)

1. Catálogo inicial de 4-6 perfiles funcionales.
2. Scope de sedes múltiple (no solo `branch_id` único).
3. AD en modo híbrido (no reemplazo total).
4. Password reset solo local.

## 10) Siguiente paso sugerido en código

Implementar Fase 1 con cambios mínimos y seguros:

- Migración tenant para:
  - `users.auth_source` (`local` por defecto)
  - `users.is_active` (`true` por defecto)
  - tabla `password_reset_tokens` tenant si no existe.
- Ajustes en `AdminController` y vista de usuarios para manejar origen/estado.
- Acción de "Enviar enlace de restablecimiento" para usuarios locales.

---

Este diseño prioriza control operativo, simplicidad y evolución incremental hacia AD híbrido sin rehacer todo el sistema.
