# Checklist QA — Descubrimiento de red (Topología + Plano 2D)

Objetivo: validar que el flujo completo (agente -> backend -> UI) detecta dispositivos conectados y los clasifica por pertenencia (dominio/gestionado/externo) sin exponer secretos.

## Precondiciones

- Tenant operativo y accesible.
- Migraciones al día:
  - `php artisan migrate`
- Nodo(s) de red existentes en ITCity (switch/router/AP) con `node_id` válido.
- Agente Windows instalado con `agent-config.json`.
- Si se prueba SNMP: `snmpwalk` instalado y en `PATH`.

## Ejecución rápida (15–20 min)

Usa este flujo cuando necesites una validación mínima antes de promoción:

1. **Preparar backend (2 min)**
   - Ejecutar `php artisan migrate`.
   - Confirmar que el panel de monitoreo abre sin errores.

2. **Probar heartbeat + discovery base (3 min)**
   - Ejecutar: `powershell -ExecutionPolicy Bypass -File .\windows-heartbeat-agent.ps1 -RunOnce -ConfigPath .\agent-config.json`.
   - Verificar en consola: `Heartbeat OK`.

3. **Probar SNMP (5–7 min)**
   - Configurar 1 target SNMP v2c y 1 target SNMP v3 en `snmp_targets`.
   - Repetir `-RunOnce`.
   - Verificar que haya envío/observación para ambos nodos (`node_id` correctos).

4. **Validar UI topología/plano (4–5 min)**
   - Click en nodo de red en topología global y en AP del plano 2D.
   - Confirmar visualización de: dispositivos observados, ownership y puertos/relaciones.

5. **Validar seguridad de logs (2 min)**
   - Forzar fallo controlado (credencial SNMP incorrecta).
   - Confirmar que logs no muestran secretos (`community`, `auth_passphrase`, `priv_passphrase`, `AgentKey`).

Resultado rápido esperado: `PASS` si los pasos 2, 3, 4 y 5 se cumplen sin exposición de secretos.

## Criterios de aceptación (10 puntos)

1. **Heartbeat básico operativo**
   - Ejecutar agente en modo una corrida: `powershell -ExecutionPolicy Bypass -File .\windows-heartbeat-agent.ps1 -RunOnce -ConfigPath .\agent-config.json`
   - Esperado: mensaje `Heartbeat OK` y actualización de activo en backend.

2. **Ingesta de discovery por heartbeat**
   - Con `network_discovery_interval_minutes` habilitado, verificar que el payload incluye `details.network_discovery.devices`.
   - Esperado: dispositivos observados persistidos para el nodo resuelto.

3. **Endpoint dedicado de observaciones**
   - Verificar que `POST /agent/network-observations` responde OK con `X-Agent-Key` válido.
   - Esperado: registros en `node_observed_devices` sin errores CSRF.

4. **SNMP v2c funcional**
   - Configurar al menos 1 target v2c (`community`, `version=2c`, `node_id`).
   - Esperado: envío de observaciones `observed_via=snmp-fdb` y conteos > 0 cuando exista FDB.

5. **SNMP v3 funcional**
   - Configurar al menos 1 target v3 (`security_name`, `security_level`, `auth_*`, `priv_*` según nivel).
   - Esperado: consulta SNMP exitosa y observaciones asociadas al `node_id`.

6. **Clasificación de pertenencia correcta**
   - Validar en snapshot de nodo que aparezcan categorías esperadas (gestionado / dominio tenant / externo / desconocido).
   - Esperado: dispositivos del dominio corporativo no se marcan como externos.

7. **Inspector en topología global**
   - Click en switch/router/AP en topología.
   - Esperado: panel con activos asociados + dispositivos observados + puertos/relaciones + badges de ownership.

8. **Inspector en plano 2D**
   - Click en punto AP vinculado a nodo en el floor plan.
   - Esperado: tarjeta de “Dispositivos del nodo” con datos de observación y clasificación.

9. **Seguridad de logs (sin secretos)**
   - Forzar error (credencial SNMP inválida o endpoint no disponible).
   - Esperado: logs sin mostrar `community`, `auth_passphrase`, `priv_passphrase` ni `AgentKey` en claro.

10. **Export de plantilla SNMP desde admin**
    - Descargar plantilla desde monitoreo.
    - Esperado: incluye campos v2c y placeholders v3 (`security_name`, `security_level`, `auth_protocol`, `auth_passphrase`, `priv_protocol`, `priv_passphrase`, `context_name`).

## Evidencia recomendada por prueba

- Captura de consola del agente.
- Captura de UI (topología/plano) en el nodo probado.
- JSON de respuesta relevante (`/red/nodos/{id}` si aplica).
- Conteo/consulta rápida en BD para `node_observed_devices` (antes/después).

## Criterio de salida

Se considera aprobado cuando:
- 10/10 criterios pasan en al menos 1 tenant de prueba.
- No hay exposición de secretos en logs.
- Los datos visualizados en topología/plano son coherentes con observaciones reales de red.

## Acta de pruebas (formato PASS/FAIL)

Completar durante la ejecución para dejar evidencia formal.

| ID | Prueba | Resultado (PASS/FAIL) | Evidencia | Observaciones / Acción correctiva |
|---|---|---|---|---|
| 1 | Heartbeat básico operativo |  |  |  |
| 2 | Ingesta de discovery por heartbeat |  |  |  |
| 3 | Endpoint dedicado de observaciones |  |  |  |
| 4 | SNMP v2c funcional |  |  |  |
| 5 | SNMP v3 funcional |  |  |  |
| 6 | Clasificación de pertenencia correcta |  |  |  |
| 7 | Inspector en topología global |  |  |  |
| 8 | Inspector en plano 2D |  |  |  |
| 9 | Seguridad de logs (sin secretos) |  |  |  |
| 10 | Export de plantilla SNMP desde admin |  |  |  |

### Resumen de ejecución

- Fecha:
- Tenant:
- Ejecutado por:
- Resultado global:
- Bloqueadores para promoción (si aplica):
