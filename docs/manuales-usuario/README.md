# Manuales de usuario por nivel

Este paquete documenta como usar ITCity segun el nivel de acceso asignado.

Los archivos .docx generados se guardan en `docs/manuales-usuario/docx/`.
Para que aparezcan imagenes en Word, deben estar insertadas en cada .md con sintaxis Markdown: `![texto](capturas/archivo.png)`.

## Niveles documentados

1. Administrador completo
2. Administrador operativo
3. Auditor (solo lectura)

## Archivos

- manual-admin-completo.md
- manual-admin-operativo.md
- manual-auditor-lectura.md
- manual-corporativo-unificado.md
- guia-capacitacion-roles.md
- plantilla-capturas.md
- resumen-ejecutivo-direccion.md
- presentacion-comite-direccion.md

## Matriz rapida de capacidades

| Capacidad | Admin completo | Admin operativo | Auditor |
|---|---|---|---|
| Ver topologia | Si | Si | Si |
| Gestionar topologia | Si | Si | No |
| Ver monitoreo | Si | Si | Si |
| Descargar instaladores y plantilla SNMP | Si | No | No |
| Ver inventario | Si | Si | Si |
| Gestionar inventario y reasignaciones | Si | Si | No |
| Ver usuarios | Si | Si | Si |
| Gestionar usuarios | Si | Si | No |
| Enviar reset de password | Si | Si | No |

## Nota de alcance por campus

El sistema puede restringir el acceso por campus. Si tu usuario no tiene campus asignados, algunas pantallas administrativas pueden responder con error 403.

## Nota de configuracion

Los niveles documentados provienen de perfiles de permisos tenant:

- full_admin
- operations_admin
- read_only_auditor

Adicionalmente, existe un caso legado: usuario con rol admin sin perfil asignado, que en codigo tiene acceso total.
