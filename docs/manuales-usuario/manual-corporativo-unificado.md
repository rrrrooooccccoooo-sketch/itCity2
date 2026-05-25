# Manual corporativo ITCity

## Portada

- Documento: Manual corporativo de uso ITCity
- Version: 1.0
- Fecha: 2026-04-16
- Ambito: Tenant
- Audiencia: Administradores, Operaciones, Auditoria

## Objetivo

Estandarizar la operacion de topologia, monitoreo, inventario y gestion de usuarios de acuerdo con el nivel de acceso asignado.

## Alcance funcional

1. Topologia global y gestion de nodos/enlaces.
2. Monitoreo y seguimiento de alertas.
3. Inventario, reasignaciones y responsivas.
4. Administracion de usuarios y perfiles.

## Niveles de acceso

### Administrador completo

- Permisos efectivos: todos los modulos.
- Uso principal: configuracion integral y administracion del tenant.

### Administrador operativo

- Permisos efectivos: operacion y gestion diaria sin administracion tenant avanzada.
- Uso principal: continuidad operativa de red, inventario y usuarios.

### Auditor (solo lectura)

- Permisos efectivos: consulta de topologia, monitoreo, inventario y usuarios.
- Uso principal: validacion, evidencia y reporte de hallazgos.

## Rutas clave

1. Topologia global: /red
2. Panel admin: /admin
3. Usuarios: /admin/users
4. Monitoreo: /admin/monitoring/overview
5. Verificacion de responsiva: /admin/responsiva/verify

## Procedimientos corporativos

### P1. Alta o actualizacion de usuario

1. Acceder a /admin/users.
2. Registrar datos de identidad y acceso.
3. Asignar rol y perfil de permisos.
4. Definir alcance por campus.
5. Guardar y validar acceso con prueba de login.

### P2. Gestion de topologia

1. Acceder a /red.
2. Validar campus y nodos existentes.
3. Crear/editar nodos y relaciones.
4. Revisar enlaces inter-campus.
5. Guardar layout.

### P3. Inventario y reasignacion

1. Abrir panel admin e identificar activo.
2. Ejecutar reasignacion de responsable.
3. Registrar firmas de entrega y recepcion.
4. Generar PDF de responsiva.
5. Verificar codigo de validacion y bitacora.

### P4. Monitoreo operativo

1. Revisar panel de monitoreo.
2. Priorizar nodos con warning o error.
3. Registrar acciones de remediacion.
4. Confirmar recuperacion y cierre.

## Reglas de gobierno

1. Todo usuario no admin debe tener al menos un campus asignado.
2. Cambios de inventario deben dejar bitacora trazable.
3. Responsivas deben contener evidencia de firma cuando aplique.
4. Auditoria no debe ejecutar cambios de datos.

## Matriz resumida de capacidades

| Capacidad | Admin completo | Admin operativo | Auditor |
|---|---|---|---|
| Ver topologia | Si | Si | Si |
| Gestionar topologia | Si | Si | No |
| Ver monitoreo | Si | Si | Si |
| Gestionar inventario | Si | Si | No |
| Gestionar usuarios | Si | Si | No |
| Enviar reset password | Si | Si | No |
| Instalar/descargar agente | Si | No | No |

## Criterios de cumplimiento

1. No debe existir operacion fuera del alcance por campus.
2. Toda reasignacion debe ser auditable.
3. Toda revision de auditoria debe tener evidencia minima registrada.

## Referencias

- manual-admin-completo.md
- manual-admin-operativo.md
- manual-auditor-lectura.md
