# Manual de usuario: Auditor (solo lectura)

## Perfil

Nivel de consulta. Permite visibilidad operacional sin cambios sobre datos criticos.

## Que puede hacer

1. Ver topologia.
2. Ver monitoreo.
3. Ver inventario.
4. Ver listado de usuarios.

## Que no puede hacer

1. Crear, editar o borrar nodos, relaciones o software.
2. Crear, editar, borrar o reasignar activos.
3. Crear, editar o borrar usuarios.
4. Enviar reset links.
5. Ejecutar tareas de administracion de tenant.

## Flujos de auditoria sugeridos

### 1) Revision de topologia

1. Abrir /red.
2. Revisar conectividad entre nodos y campus.
3. Identificar inconsistencias para reporte.

### 2) Revision de monitoreo

1. Abrir /admin/monitoring/overview.
2. Registrar nodos con warning/error.
3. Solicitar plan de remediacion al equipo operativo.

### 3) Revision de inventario y responsivas

1. Revisar activos en panel admin.
2. Abrir bitacora de asignacion por activo.
3. Verificar evidencia de firmas y trazabilidad.
4. Validar codigos de verificacion de responsivas cuando aplique.

## Evidencia minima recomendada

1. Fecha y hora de consulta.
2. Campus revisado.
3. Hallazgo detectado.
4. Riesgo estimado.
5. Accion recomendada.

## Errores comunes y solucion

1. No puede guardar cambios: esperado por perfil de solo lectura.
2. No ve modulo completo: validar alcance por campus y permisos activos.
