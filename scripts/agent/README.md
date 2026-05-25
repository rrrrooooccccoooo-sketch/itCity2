# ITCity Windows Agent

Este directorio contiene el agente de monitoreo para equipos Windows y su instalador.

## Archivos

- `windows-heartbeat-agent.ps1`: agente que envia heartbeats al endpoint de monitoreo.
- `install-scheduled-task.ps1`: instalador interactivo para dejar el agente configurado como tarea programada.

## Instalacion interactiva

Ejecuta PowerShell como administrador y corre:

```powershell
cd scripts\agent
.\install-scheduled-task.ps1
```

En modo simple el instalador pide solo lo esencial:

- asset tag (opcional)
- agent key (obligatorio)
- confirmacion para abrir opciones avanzadas (opcional)

En cualquier pregunta puedes escribir `q` y presionar Enter para cancelar sin quedar bloqueado.

Si eliges opciones avanzadas, ahi si podras editar endpoints, intervalos y credenciales de tarea programada.

La configuracion se guarda en:

```text
C:\ProgramData\ITCity\Agent\agent-config.json
```

El `agent_key` se guarda protegido con DPAPI de Windows en alcance `LocalMachine`.

## Instalacion no interactiva

```powershell
.\install-scheduled-task.ps1 \
  -NonInteractive \
  -Endpoint "http://demo-itcity.localhost:8000/agent/heartbeat" \
  -TenantHost "demo-itcity.localhost" \
  -BranchId 1 \
  -IntervalSeconds 30 \
  -InventoryIntervalHours 24 \
  -NetworkDiscoveryIntervalMinutes 10 \
  -NetworkDiscoverySampleLimit 256 \
  -AssetTag "PC-RECEPCION-01" \
  -AgentKey "TU_TENANT_AGENT_INGEST_KEY"
```

## Que recolecta el inventario extendido

En cada inventario extendido el agente intenta recuperar:

- fabricante y modelo del equipo
- BIOS y motherboard
- CPU y memoria por modulo
- discos fisicos y unidades logicas
- GPU
- adaptadores de red activos
- sistema operativo y build
- antivirus detectado
- conteo de hotfixes
- software instalado desde el registro de Windows

El heartbeat frecuente sigue enviando solo metricas operativas ligeras, mientras el inventario pesado se manda con la cadencia configurada.

## Discovery SNMP (switches/AP)

El agente soporta un ciclo SNMP para leer tabla FDB (MACs aprendidas por puerto) y enviar esas observaciones al endpoint:

- `POST /agent/network-observations`
- `observed_via = snmp-fdb`
- requiere definir targets en `snmp_targets` con `node_id` del switch/AP dentro de ITCity

### Dependencia

Se usa `snmpwalk` (Net-SNMP). Debe estar instalado y disponible en `PATH`.

Si `snmpwalk` no existe, el agente omite el discovery SNMP y sigue enviando heartbeat normal.

### Configuracion sugerida en agent-config.json

```json
{
  "endpoint": "http://demo-itcity.localhost:8000/agent/heartbeat",
  "observations_endpoint": "http://demo-itcity.localhost:8000/agent/network-observations",
  "snmp_discovery_interval_minutes": 15,
  "snmp_sample_limit": 500,
  "snmp_targets": [
    {
      "name": "SW-Core-Principal",
      "host": "10.10.0.2",
      "node_id": 41,
      "community": "public",
      "version": "2c",
      "port": 161,
      "timeout_seconds": 5,
      "retries": 1,
      "sample_limit": 800
    },
    {
      "name": "AP-Lobby",
      "host": "10.10.2.31",
      "node_id": 58,
      "community": "public",
      "version": "2c"
    },
    {
      "name": "SW-Core-SNMPv3",
      "host": "10.10.0.10",
      "node_id": 77,
      "version": "3",
      "security_name": "snmp-itcity",
      "security_level": "authPriv",
      "auth_protocol": "SHA",
      "auth_passphrase": "CAMBIAR_AUTH",
      "priv_protocol": "AES",
      "priv_passphrase": "CAMBIAR_PRIV",
      "context_name": ""
    }
  ]
}
```

Notas:

- `node_id` debe ser el nodo ITCity que representa ese switch/AP en topologia/plano.
- Para `version: "3"`, el agente usa `security_name` y `security_level` (`noAuthNoPriv`, `authNoPriv`, `authPriv`).
- En SNMPv3 con autenticacion (`authNoPriv`/`authPriv`) se requiere `auth_passphrase`.
- En SNMPv3 con privacidad (`authPriv`) se requiere `priv_passphrase`.
- En v1 SNMP se prioriza FDB de bridge (`dot1dTpFdbPort`) + mapeo a interfaz (`dot1dBasePortIfIndex`, `ifName`).
- Se intentan hints de vecino por puerto con LLDP (`lldpRemSysName/lldpRemPortDesc`) y CDP (`cdpCacheDeviceId/cdpCacheDevicePort`) cuando el equipo los expone.
- Se agrega `vendor_name` por OUI (tabla local de prefijos MAC comunes).
- Para clientes WiFi avanzados por fabricante, conviene sumar API del controlador en una fase posterior.

## Discovery de red (switch / router / AP)

El agente ahora puede adjuntar al heartbeat una muestra de dispositivos detectados por tabla de vecinos IPv4 (`Get-NetNeighbor`).

- Frecuencia configurable: `network_discovery_interval_minutes`
- Limite por ciclo: `network_discovery_sample_limit`
- Se envia dentro de `details.network_discovery`
- El backend lo consume y lo guarda como dispositivos observados del nodo resuelto para ese heartbeat

Estructura esperada:

```json
{
  "details": {
    "network_discovery": {
      "source": "windows-netneighbor",
      "observed_at": "2026-03-25T10:15:00.0000000-06:00",
      "devices": [
        {
          "mac_address": "AA:BB:CC:DD:EE:FF",
          "ip_address": "10.10.1.24",
          "hostname": "LAPTOP-VENTAS01.midominio.local",
          "domain_name": "midominio.local",
          "vendor_name": null,
          "ssid": null,
          "switch_port": "Ethernet",
          "device_type": null,
          "meta": {
            "neighbor_state": "Reachable",
            "interface_alias": "Ethernet"
          },
          "last_seen_at": "2026-03-25T10:15:00.0000000-06:00"
        }
      ],
      "stats": {
        "scanned": 180,
        "kept": 120
      }
    }
  }
}
```

Tambien existe endpoint dedicado `POST /agent/network-observations` para colectores externos, pero el agente de Windows no lo necesita porque ya adjunta discovery al heartbeat.

## Desinstalar

```powershell
.\install-scheduled-task.ps1 -Uninstall
```

## Convertir a EXE

Si quieres distribuir esto como `.exe`, la ruta mas simple es empaquetar `install-scheduled-task.ps1` con `PS2EXE`.

Ejemplo:

```powershell
Install-Module ps2exe -Scope CurrentUser
Invoke-PS2EXE .\install-scheduled-task.ps1 .\ITCity-Agent-Installer.exe -requireAdmin
```

## Recomendacion de autenticacion

Para el agente no conviene pedir usuario/password de la aplicacion web.

Usa:

- `TENANT_AGENT_INGEST_KEY` para autenticar el agente contra ITCity
- usuario/password de Windows solo si quieres que la tarea programada corra bajo una cuenta especifica