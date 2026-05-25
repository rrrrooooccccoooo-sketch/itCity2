# Manual de usuario: Administrador completo

## Perfil

Nivel orientado a administracion total del tenant. Tiene acceso a todos los modulos.

## Objetivos del rol

1. Configurar estructura de red y catalogos base.
2. Administrar usuarios y perfiles.
3. Operar inventario, responsivas y reasignaciones.
4. Supervisar monitoreo y desplegar agentes.

## Menu y modulos principales

1. Topologia Global (/red)
2. Panel administrativo (/admin)
3. Usuarios (/admin/users)
4. Monitoreo (/admin/monitoring/overview)
5. Inventario dentro de panel admin

## Flujos operativos clave

### 1) Alta de usuarios y permisos

1. Ir a /admin/users.
2. Crear usuario nuevo o editar existente.
3. Definir rol y perfil.
4. Asignar campus en alcance (si aplica).
5. Guardar y validar acceso.

### 2) Gestion de topologia

1. Abrir /red.
2. Crear o editar nodos.
3. Crear o editar relaciones.
4. Ajustar layout y guardar.
5. Verificar consistencia de enlaces inter-campus.

### 3) Inventario y responsivas

1. Abrir panel admin.
2. Registrar o editar activos.
3. Reasignar activo cuando cambie de responsable.
4. Capturar firma de quien entrega/recibe.
5. Generar y descargar responsiva PDF.
6. Validar responsiva con QR en /admin/responsiva/verify.

### 4) Monitoreo y despliegue de agente

1. Revisar dashboard de monitoreo.
2. Descargar instaladores del agente.
3. Descargar plantilla SNMP.
4. Revisar activos y nodos con alertas.

## Buenas practicas

1. Mantener actualizado el alcance de campus de cada usuario.
2. Evitar usar admin sin perfil salvo contingencia.
3. Registrar reasignaciones con trazabilidad completa.
4. Revisar periodicamente bitacoras y estatus de monitoreo.

## Errores comunes y solucion

1. 403 por campus: revisar alcance de campus del usuario.
2. No aparece modulo: validar perfil y permisos del usuario.
3. Responsiva sin firma: confirmar firma personal y de recepcion antes de generar PDF.
