# Manual de usuario: Administrador operativo

## Perfil

Nivel operativo con permisos de gestion en topologia, inventario, monitoreo y usuarios, pero sin funciones de administracion de tenant para despliegue de instaladores.

## Que puede hacer

1. Ver y gestionar topologia.
2. Ver y gestionar inventario.
3. Ver monitoreo.
4. Ver y gestionar usuarios.
5. Enviar enlace de reseteo de password.

## Que no puede hacer

1. Descargar instalador de agente.
2. Descargar instalador ZIP/EXE del agente.
3. Descargar plantilla SNMP de administracion tenant.

## Flujos recomendados

### 1) Operacion diaria de topologia

1. Entrar a /red.
2. Revisar nodos por campus.
3. Corregir estatus, etiquetas y relaciones.
4. Guardar layout si hubo cambios.

### 2) Operacion diaria de inventario

1. Entrar al panel admin.
2. Revisar activos por campus.
3. Ejecutar reasignaciones necesarias.
4. Generar responsiva cuando proceda.
5. Confirmar bitacora de entrega/recepcion.

### 3) Soporte de usuarios

1. Entrar a /admin/users.
2. Editar perfil/acceso cuando cambie funcion.
3. Enviar reset link si el usuario no puede acceder.
4. Verificar alcance por campus para evitar bloqueos.

## Checklist de cierre de jornada

1. Cambios de topologia guardados.
2. Reasignaciones con firma y bitacora completa.
3. Alertas de monitoreo revisadas.
4. Incidencias de acceso de usuarios atendidas.

## Errores comunes y solucion

1. No aparece boton de gestion: permiso faltante en perfil.
2. No ve datos de otro campus: comportamiento esperado por alcance.
3. No puede descargar instaladores: es restriccion del perfil operativo.
