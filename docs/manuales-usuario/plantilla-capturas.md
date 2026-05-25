# Plantilla de capturas para manuales

Usa esta plantilla para anexar capturas de pantalla consistentes en cada manual.

## Ruta recomendada para capturas

- Guarda las imagenes en: `docs/manuales-usuario/capturas/`
- Usa rutas relativas en el manual Markdown, por ejemplo:

```markdown
![Topologia global - vista general](capturas/admin-operativo-topologia-paso-03.png)
```

Si la imagen no esta referenciada con la sintaxis anterior, no aparecera en el .docx.

## Convencion de nombres

- prefijo: rol-modulo-paso
- ejemplo: admin-operativo-topologia-paso-01.png

## Estructura sugerida

1. Pantalla de inicio de sesion
2. Home o panel principal
3. Topologia global
4. Monitoreo overview
5. Inventario (listado)
6. Reasignacion de activo
7. Responsiva PDF / verificacion
8. Usuarios (cuando aplique)

## Ficha por captura

- Archivo:
- Modulo:
- Rol:
- Paso del procedimiento:
- Descripcion funcional:
- Resultado esperado:

## Criterios de calidad

1. Mostrar URL o contexto de modulo.
2. Evitar datos sensibles reales.
3. Mantener resolucion legible.
4. Resaltar accion principal en la captura.

## Regeneracion a DOCX con imagenes

Cuando termines de agregar imagenes en los .md, vuelve a generar los .docx con Pandoc:

```powershell
$pandoc = "C:\wamp64\pandoc\pandoc-3.6.4\pandoc.exe"
$srcDir = "C:\wamp64\www\ITCity2\docs\manuales-usuario"
$outDir = "C:\wamp64\www\ITCity2\docs\manuales-usuario\docx"
Get-ChildItem -Path $srcDir -Filter "*.md" | ForEach-Object {
	$out = Join-Path $outDir ($_.BaseName + ".docx")
	& $pandoc $_.FullName -o $out --from=markdown --to=docx --standalone --resource-path=$srcDir
}
```
