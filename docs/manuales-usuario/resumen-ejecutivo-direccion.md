# Resumen ejecutivo ITCity para direccion

## Proposito

Este documento resume en una sola pagina el estado de gobierno operativo, control de accesos y trazabilidad de ITCity para toma de decisiones directivas.

## Panorama general

ITCity opera con control por perfiles y alcance por campus. La plataforma separa funciones de topologia, monitoreo, inventario y administracion de usuarios para reducir riesgo operativo.

## Niveles de acceso

1. Administrador completo
- Control total del tenant.
- Define estructura, usuarios, inventario y despliegues de agente.

2. Administrador operativo
- Gestion diaria de topologia, monitoreo, inventario y usuarios.
- Sin funciones de administracion tenant avanzada.

3. Auditor (solo lectura)
- Visibilidad integral sin permisos de modificacion.
- Enfocado en verificacion y evidencia.

## Controles clave

1. Control de permisos por perfil.
2. Restriccion de acceso por campus (scope).
3. Bitacora de reasignaciones de activos.
4. Flujo de responsiva con evidencia de firma.
5. Verificacion de responsiva por codigo/QR.

## Riesgos principales y mitigaciones

1. Riesgo: acceso excesivo por mala asignacion de perfil.
- Mitigacion: revision mensual de perfiles y overrides.

2. Riesgo: visualizacion de datos fuera de campus asignado.
- Mitigacion: enforcement de scope por campus y prueba de acceso.

3. Riesgo: baja trazabilidad en cambios de inventario.
- Mitigacion: uso obligatorio de bitacora de entrega/recepcion y responsiva.

4. Riesgo: dependencia operativa de una sola cuenta privilegiada.
- Mitigacion: separar cuentas nominales y limitar admin global de contingencia.

## Indicadores directivos sugeridos (KPI)

1. Porcentaje de usuarios con perfil correcto y alcance validado.
2. Tiempo promedio de cierre de incidentes de monitoreo.
3. Porcentaje de reasignaciones con evidencia completa.
4. Numero de hallazgos de auditoria abiertos vs cerrados por periodo.
5. Cobertura de nodos monitoreados por campus.

## Acciones recomendadas (30-60 dias)

1. Establecer comite mensual de revision de accesos y scopes.
2. Definir politica formal de reasignacion con evidencia minima obligatoria.
3. Publicar tablero de KPI operativo y de auditoria.
4. Ejecutar capacitacion trimestral por rol con evaluacion.

## Mensaje ejecutivo

El modelo actual permite operar con segregacion de funciones y trazabilidad razonable. La prioridad para direccion debe centrarse en disciplina de permisos, evidencia de procesos e indicadores de cumplimiento para sostener escalabilidad y control.
