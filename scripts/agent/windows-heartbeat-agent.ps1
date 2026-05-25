param(
    [string]$Endpoint = "http://demo-itcity.localhost:8000/agent/heartbeat",
    [string]$ObservationsEndpoint = "",
    [string]$AgentKey = "",
    [string]$TenantHost = "",
    [string]$AssetTag = "",
    [int]$BranchId = 1,
    [int]$IntervalSeconds = 30,
    [int]$InventoryIntervalHours = 24,
    [int]$NetworkDiscoveryIntervalMinutes = 10,
    [int]$NetworkDiscoverySampleLimit = 256,
    [int]$SnmpDiscoveryIntervalMinutes = 15,
    [int]$SnmpSampleLimit = 500,
    [string]$ConfigPath = "",
    [string]$StatePath = "",
    [switch]$RunOnce
)

function Get-DefaultConfigPath {
    if ([string]::IsNullOrWhiteSpace($env:ProgramData)) {
        return $null
    }

    return Join-Path $env:ProgramData "ITCity\Agent\agent-config.json"
}

function Get-DefaultStatePath {
    $defaultConfigPath = Get-DefaultConfigPath
    if (-not [string]::IsNullOrWhiteSpace($defaultConfigPath)) {
        $configDirectory = Split-Path -Path $defaultConfigPath -Parent
        if (-not [string]::IsNullOrWhiteSpace($configDirectory)) {
            return Join-Path $configDirectory "agent-state.json"
        }
    }

    return $null
}

function Unprotect-String([string]$CipherText) {
    if ([string]::IsNullOrWhiteSpace($CipherText)) {
        return $null
    }

    try {
        $cipherBytes = [Convert]::FromBase64String($CipherText)
        $plainBytes = [System.Security.Cryptography.ProtectedData]::Unprotect(
            $cipherBytes,
            $null,
            [System.Security.Cryptography.DataProtectionScope]::LocalMachine
        )
        return [System.Text.Encoding]::UTF8.GetString($plainBytes)
    } catch {
        Write-Warning "No se pudo descifrar agent_key_protected desde la configuracion."
        return $null
    }
}

function Get-ConfigValue($Config, [string]$PropertyName) {
    if ($null -eq $Config) {
        return $null
    }

    $property = $Config.PSObject.Properties[$PropertyName]
    if ($null -eq $property) {
        return $null
    }

    return $property.Value
}

function Resolve-AgentSettings {
    $resolvedConfigPath = $ConfigPath
    if ([string]::IsNullOrWhiteSpace($resolvedConfigPath)) {
        $defaultConfigPath = Get-DefaultConfigPath
        if (-not [string]::IsNullOrWhiteSpace($defaultConfigPath) -and (Test-Path $defaultConfigPath)) {
            $resolvedConfigPath = $defaultConfigPath
        }
    }

    $config = $null
    if (-not [string]::IsNullOrWhiteSpace($resolvedConfigPath) -and (Test-Path $resolvedConfigPath)) {
        try {
            $config = Get-Content -Path $resolvedConfigPath -Raw -Encoding UTF8 | ConvertFrom-Json
        } catch {
            Write-Warning "No se pudo leer la configuracion del agente en $resolvedConfigPath. Se usaran solo argumentos de linea de comandos."
        }
    }

    if ([string]::IsNullOrWhiteSpace($Endpoint)) {
        $Endpoint = [string](Get-ConfigValue $config 'endpoint')
    }

    if ([string]::IsNullOrWhiteSpace($ObservationsEndpoint)) {
        $ObservationsEndpoint = [string](Get-ConfigValue $config 'observations_endpoint')
    }

    if ([string]::IsNullOrWhiteSpace($TenantHost)) {
        $TenantHost = [string](Get-ConfigValue $config 'tenant_host')
    }

    if ([string]::IsNullOrWhiteSpace($AssetTag)) {
        $AssetTag = [string](Get-ConfigValue $config 'asset_tag')
    }

    if ($BranchId -le 0) {
        $configBranchId = Get-ConfigValue $config 'branch_id'
        if ($configBranchId) {
            $BranchId = [int]$configBranchId
        }
    }

    if ($IntervalSeconds -le 0) {
        $configInterval = Get-ConfigValue $config 'interval_seconds'
        if ($configInterval) {
            $IntervalSeconds = [int]$configInterval
        }
    }

    if ($InventoryIntervalHours -le 0) {
        $configInventoryInterval = Get-ConfigValue $config 'inventory_interval_hours'
        if ($configInventoryInterval) {
            $InventoryIntervalHours = [int]$configInventoryInterval
        }
    }

    if ($NetworkDiscoveryIntervalMinutes -le 0) {
        $configNetworkDiscoveryInterval = Get-ConfigValue $config 'network_discovery_interval_minutes'
        if ($configNetworkDiscoveryInterval) {
            $NetworkDiscoveryIntervalMinutes = [int]$configNetworkDiscoveryInterval
        }
    }

    if ($NetworkDiscoverySampleLimit -le 0) {
        $configNetworkDiscoverySampleLimit = Get-ConfigValue $config 'network_discovery_sample_limit'
        if ($configNetworkDiscoverySampleLimit) {
            $NetworkDiscoverySampleLimit = [int]$configNetworkDiscoverySampleLimit
        }
    }

    if ($SnmpDiscoveryIntervalMinutes -le 0) {
        $configSnmpDiscoveryInterval = Get-ConfigValue $config 'snmp_discovery_interval_minutes'
        if ($configSnmpDiscoveryInterval) {
            $SnmpDiscoveryIntervalMinutes = [int]$configSnmpDiscoveryInterval
        }
    }

    if ($SnmpSampleLimit -le 0) {
        $configSnmpSampleLimit = Get-ConfigValue $config 'snmp_sample_limit'
        if ($configSnmpSampleLimit) {
            $SnmpSampleLimit = [int]$configSnmpSampleLimit
        }
    }

    if ([string]::IsNullOrWhiteSpace($StatePath)) {
        $StatePath = [string](Get-ConfigValue $config 'state_path')
    }

    if ([string]::IsNullOrWhiteSpace($AgentKey)) {
        $protectedAgentKey = [string](Get-ConfigValue $config 'agent_key_protected')
        if (-not [string]::IsNullOrWhiteSpace($protectedAgentKey)) {
            $AgentKey = Unprotect-String $protectedAgentKey
        }
    }

    if ([string]::IsNullOrWhiteSpace($AgentKey)) {
        $AgentKey = [string](Get-ConfigValue $config 'agent_key')
    }

    if ([string]::IsNullOrWhiteSpace($Endpoint)) {
        $Endpoint = "http://demo-itcity.localhost:8000/agent/heartbeat"
    }

    if ([string]::IsNullOrWhiteSpace($ObservationsEndpoint)) {
        if ($Endpoint -match '/agent/heartbeat/?$') {
            $ObservationsEndpoint = ($Endpoint -replace '/agent/heartbeat/?$', '/agent/network-observations')
        } else {
            $ObservationsEndpoint = "$Endpoint"
        }
    }

    if ($BranchId -le 0) {
        $BranchId = 1
    }

    if ($IntervalSeconds -le 0) {
        $IntervalSeconds = 30
    }

    if ($InventoryIntervalHours -le 0) {
        $InventoryIntervalHours = 24
    }

    if ($NetworkDiscoveryIntervalMinutes -le 0) {
        $NetworkDiscoveryIntervalMinutes = 10
    }

    if ($NetworkDiscoverySampleLimit -le 0) {
        $NetworkDiscoverySampleLimit = 256
    }

    if ($SnmpDiscoveryIntervalMinutes -le 0) {
        $SnmpDiscoveryIntervalMinutes = 15
    }

    if ($SnmpSampleLimit -le 0) {
        $SnmpSampleLimit = 500
    }

    $snmpTargets = @()
    $configuredSnmpTargets = Get-ConfigValue $config 'snmp_targets'
    if ($configuredSnmpTargets) {
        foreach ($target in @($configuredSnmpTargets)) {
            $targetHost = [string](Get-ConfigValue $target 'host')
            $targetNodeId = [int](Get-ConfigValue $target 'node_id')
            if ([string]::IsNullOrWhiteSpace($targetHost) -or $targetNodeId -le 0) {
                continue
            }

            $targetVersion = [string](Get-ConfigValue $target 'version')
            if ([string]::IsNullOrWhiteSpace($targetVersion)) {
                $targetVersion = '2c'
            }
            $targetVersion = $targetVersion.Trim().ToLowerInvariant()
            if ($targetVersion -notin @('1', '2c', '3')) {
                $targetVersion = '2c'
            }

            $securityLevel = [string](Get-ConfigValue $target 'security_level')
            if ([string]::IsNullOrWhiteSpace($securityLevel)) {
                $securityLevel = 'authPriv'
            }

            $authProtocol = [string](Get-ConfigValue $target 'auth_protocol')
            if ([string]::IsNullOrWhiteSpace($authProtocol)) {
                $authProtocol = 'SHA'
            }

            $privProtocol = [string](Get-ConfigValue $target 'priv_protocol')
            if ([string]::IsNullOrWhiteSpace($privProtocol)) {
                $privProtocol = 'AES'
            }

            $snmpTargets += [ordered]@{
                name = [string](Get-ConfigValue $target 'name')
                host = $targetHost
                node_id = $targetNodeId
                community = if ([string]::IsNullOrWhiteSpace([string](Get-ConfigValue $target 'community'))) { 'public' } else { [string](Get-ConfigValue $target 'community') }
                version = $targetVersion
                port = if ([int](Get-ConfigValue $target 'port') -le 0) { 161 } else { [int](Get-ConfigValue $target 'port') }
                timeout_seconds = if ([int](Get-ConfigValue $target 'timeout_seconds') -le 0) { 5 } else { [int](Get-ConfigValue $target 'timeout_seconds') }
                retries = if ([int](Get-ConfigValue $target 'retries') -lt 0) { 1 } else { [int](Get-ConfigValue $target 'retries') }
                sample_limit = if ([int](Get-ConfigValue $target 'sample_limit') -le 0) { $SnmpSampleLimit } else { [int](Get-ConfigValue $target 'sample_limit') }
                security_name = [string](Get-ConfigValue $target 'security_name')
                security_level = $securityLevel
                auth_protocol = $authProtocol
                auth_passphrase = [string](Get-ConfigValue $target 'auth_passphrase')
                priv_protocol = $privProtocol
                priv_passphrase = [string](Get-ConfigValue $target 'priv_passphrase')
                context_name = [string](Get-ConfigValue $target 'context_name')
            }
        }
    }

    if ([string]::IsNullOrWhiteSpace($StatePath)) {
        $StatePath = Get-DefaultStatePath
    }

    return [ordered]@{
        Endpoint = $Endpoint
        ObservationsEndpoint = $ObservationsEndpoint
        AgentKey = $AgentKey
        TenantHost = $TenantHost
        AssetTag = $AssetTag
        BranchId = $BranchId
        IntervalSeconds = $IntervalSeconds
        InventoryIntervalHours = $InventoryIntervalHours
        NetworkDiscoveryIntervalMinutes = $NetworkDiscoveryIntervalMinutes
        NetworkDiscoverySampleLimit = $NetworkDiscoverySampleLimit
        SnmpDiscoveryIntervalMinutes = $SnmpDiscoveryIntervalMinutes
        SnmpSampleLimit = $SnmpSampleLimit
        SnmpTargets = $snmpTargets
        ConfigPath = $resolvedConfigPath
        StatePath = $StatePath
    }
}

$resolvedSettings = Resolve-AgentSettings
$Endpoint = [string]$resolvedSettings.Endpoint
$ObservationsEndpoint = [string]$resolvedSettings.ObservationsEndpoint
$AgentKey = [string]$resolvedSettings.AgentKey
$TenantHost = [string]$resolvedSettings.TenantHost
$AssetTag = [string]$resolvedSettings.AssetTag
$BranchId = [int]$resolvedSettings.BranchId
$IntervalSeconds = [int]$resolvedSettings.IntervalSeconds
$InventoryIntervalHours = [int]$resolvedSettings.InventoryIntervalHours
$NetworkDiscoveryIntervalMinutes = [int]$resolvedSettings.NetworkDiscoveryIntervalMinutes
$NetworkDiscoverySampleLimit = [int]$resolvedSettings.NetworkDiscoverySampleLimit
$SnmpDiscoveryIntervalMinutes = [int]$resolvedSettings.SnmpDiscoveryIntervalMinutes
$SnmpSampleLimit = [int]$resolvedSettings.SnmpSampleLimit
$SnmpTargets = @($resolvedSettings.SnmpTargets)
$ConfigPath = [string]$resolvedSettings.ConfigPath
$StatePath = [string]$resolvedSettings.StatePath

if ([string]::IsNullOrWhiteSpace($AgentKey)) {
    Write-Error "Debes enviar -AgentKey con TENANT_AGENT_INGEST_KEY o configurar agent-config.json"
    exit 1
}

function Parse-StateDate($Value) {
    if ([string]::IsNullOrWhiteSpace([string]$Value)) {
        return $null
    }

    $parsedDate = $null
    if ([datetime]::TryParse([string]$Value, [ref]$parsedDate)) {
        return $parsedDate
    }

    return $null
}

function Load-AgentState {
    if ([string]::IsNullOrWhiteSpace($StatePath) -or -not (Test-Path $StatePath)) {
        return [ordered]@{}
    }

    try {
        $state = Get-Content -Path $StatePath -Raw -Encoding UTF8 | ConvertFrom-Json
        $loadedState = [ordered]@{}
        foreach ($property in $state.PSObject.Properties) {
            $loadedState[$property.Name] = $property.Value
        }
        return $loadedState
    } catch {
        Write-Warning "No se pudo leer el estado local del agente."
        return [ordered]@{}
    }
}

function Save-AgentState([hashtable]$State) {
    if ([string]::IsNullOrWhiteSpace($StatePath)) {
        return
    }

    $stateDirectory = Split-Path -Path $StatePath -Parent
    if (-not [string]::IsNullOrWhiteSpace($stateDirectory)) {
        New-Item -ItemType Directory -Path $stateDirectory -Force | Out-Null
    }

    $State | ConvertTo-Json -Depth 6 | Set-Content -Path $StatePath -Encoding UTF8
}

function Get-Percent([double]$Used, [double]$Total) {
    if ($Total -le 0) { return $null }
    return [math]::Round(($Used / $Total) * 100, 2)
}

function Get-CpuUsagePercent {
    try {
        $cpuCounter = Get-Counter '\Processor(_Total)\% Processor Time' -ErrorAction Stop
        if ($cpuCounter -and $cpuCounter.CounterSamples.Count -gt 0) {
            $value = $cpuCounter.CounterSamples[0].CookedValue
            if ($null -ne $value) {
                return [math]::Round([math]::Min(100, [math]::Max(0, [double]$value)), 2)
            }
        }
    } catch {
    }

    try {
        $processorSample = Get-CimInstance Win32_PerfFormattedData_PerfOS_Processor -ErrorAction Stop |
            Where-Object { $_.Name -eq '_Total' } |
            Select-Object -First 1
        if ($processorSample -and $null -ne $processorSample.PercentProcessorTime) {
            return [math]::Round([math]::Min(100, [math]::Max(0, [double]$processorSample.PercentProcessorTime)), 2)
        }
    } catch {
    }

    return $null
}

function Get-ComputerEquipmentType([int]$PcSystemType, [string]$Model) {
    switch ($PcSystemType) {
        2 { return 'laptop' }
        3 { return 'workstation' }
        4 { return 'server' }
        5 { return 'server' }
        7 { return 'server' }
        8 { return 'server' }
        default {
            if ([string]::IsNullOrWhiteSpace($Model)) {
                return 'desktop'
            }

            if ($Model -match 'laptop|notebook') { return 'laptop' }
            return 'desktop'
        }
    }
}

function Get-StorageTypeSummary($PhysicalDisks) {
    if (-not $PhysicalDisks) {
        return 'other'
    }

    $rawText = (($PhysicalDisks | ForEach-Object {
        @($_.MediaType, $_.Model, $_.InterfaceType) -join ' '
    }) -join ' ').ToLowerInvariant()

    if ($rawText -match 'nvme') { return 'nvme' }
    if ($rawText -match 'ssd|solid state') { return 'ssd' }
    if ($rawText -match 'hdd|hard disk|sata') { return 'hdd' }
    return 'other'
}

function Get-InstalledSoftware {
    $registryPaths = @(
        'HKLM:\SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall\*',
        'HKLM:\SOFTWARE\WOW6432Node\Microsoft\Windows\CurrentVersion\Uninstall\*',
        'HKCU:\SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall\*'
    )

    $software = @()
    foreach ($path in $registryPaths) {
        try {
            $items = Get-ItemProperty -Path $path -ErrorAction SilentlyContinue |
                Where-Object { -not [string]::IsNullOrWhiteSpace($_.DisplayName) } |
                Select-Object DisplayName, DisplayVersion, Publisher, InstallDate
            if ($items) {
                $software += $items
            }
        } catch {
        }
    }

    return $software |
        Sort-Object DisplayName, DisplayVersion -Unique |
        ForEach-Object {
            [ordered]@{
                name = $_.DisplayName
                version = if ([string]::IsNullOrWhiteSpace($_.DisplayVersion)) { $null } else { $_.DisplayVersion }
                publisher = if ([string]::IsNullOrWhiteSpace($_.Publisher)) { $null } else { $_.Publisher }
                install_date = if ([string]::IsNullOrWhiteSpace($_.InstallDate)) { $null } else { $_.InstallDate }
            }
        }
}

function Get-AntivirusProducts {
    try {
        $products = Get-CimInstance -Namespace 'root/SecurityCenter2' -ClassName AntivirusProduct -ErrorAction Stop
        return $products | ForEach-Object {
            [ordered]@{
                display_name = $_.displayName
                path_to_signed_product_exe = $_.pathToSignedProductExe
                path_to_signed_reporting_exe = $_.pathToSignedReportingExe
                product_state = $_.productState
                timestamp = $_.timestamp
            }
        }
    } catch {
        return @()
    }
}

function Get-OfficeVersion($InstalledSoftware) {
    $office = $InstalledSoftware | Where-Object {
        $_.name -match 'Microsoft 365|Microsoft Office|Office 365|Office LTSC|Visio|Project'
    } | Select-Object -First 1

    if ($office) {
        return ($office.name, $office.version -join ' ').Trim()
    }

    return $null
}

function Collect-InventoryBundle {
    $computerSystem = Get-CimInstance Win32_ComputerSystem -ErrorAction SilentlyContinue
    $bios = Get-CimInstance Win32_BIOS -ErrorAction SilentlyContinue
    $baseBoard = Get-CimInstance Win32_BaseBoard -ErrorAction SilentlyContinue
    $os = Get-CimInstance Win32_OperatingSystem -ErrorAction SilentlyContinue
    $processors = @(Get-CimInstance Win32_Processor -ErrorAction SilentlyContinue)
    $memoryModules = @(Get-CimInstance Win32_PhysicalMemory -ErrorAction SilentlyContinue)
    $physicalDisks = @(Get-CimInstance Win32_DiskDrive -ErrorAction SilentlyContinue)
    $logicalDisks = @(Get-CimInstance Win32_LogicalDisk -ErrorAction SilentlyContinue | Where-Object { $_.DriveType -eq 3 })
    $videoControllers = @(Get-CimInstance Win32_VideoController -ErrorAction SilentlyContinue)
    $networkAdapters = @(Get-CimInstance Win32_NetworkAdapterConfiguration -ErrorAction SilentlyContinue | Where-Object { $_.IPEnabled -eq $true })
    $installedSoftware = @(Get-InstalledSoftware)
    $installedSoftwareTotal = $installedSoftware.Count
    $installedSoftwarePreview = @($installedSoftware | Select-Object -First 200)
    $antivirusProducts = @(Get-AntivirusProducts)
    $hotfixes = @(Get-HotFix -ErrorAction SilentlyContinue)

    $totalStorageBytes = ($logicalDisks | Measure-Object -Property Size -Sum).Sum
    $storageGb = if ($totalStorageBytes -gt 0) { [int][math]::Round($totalStorageBytes / 1GB, 0) } else { $null }
    $manufacturer = if ($computerSystem) { $computerSystem.Manufacturer } else { $null }
    $model = if ($computerSystem) { $computerSystem.Model } else { $null }
    $pcSystemType = 1
    if ($computerSystem -and $null -ne $computerSystem.PCSystemType) {
        $pcSystemType = [int]$computerSystem.PCSystemType
    }
    $equipmentType = Get-ComputerEquipmentType -PcSystemType $pcSystemType -Model ([string]$model)
    $storageType = Get-StorageTypeSummary -PhysicalDisks $physicalDisks
    $officeVersion = Get-OfficeVersion -InstalledSoftware $installedSoftware

    return [ordered]@{
        brand = $manufacturer
        model = $model
        equipment_type = $equipmentType
        storage_type = $storageType
        storage_gb = $storageGb
        office_version = $officeVersion
        details = [ordered]@{
            inventory = [ordered]@{
                captured_at = (Get-Date).ToString('o')
                capture_scope = 'extended'
                last_extended_captured_at = (Get-Date).ToString('o')
                hardware = [ordered]@{
                    system = [ordered]@{
                        manufacturer = $manufacturer
                        model = $model
                        domain = $computerSystem.Domain
                        total_physical_memory_gb = if ($computerSystem.TotalPhysicalMemory) { [math]::Round([double]$computerSystem.TotalPhysicalMemory / 1GB, 2) } else { $null }
                        pc_system_type = $computerSystem.PCSystemType
                    }
                    bios = [ordered]@{
                        serial_number = $bios.SerialNumber
                        version = ($bios.SMBIOSBIOSVersion, $bios.Version | Where-Object { $_ } | Select-Object -Unique)
                        manufacturer = $bios.Manufacturer
                        release_date = if ($bios.ReleaseDate) { ([datetime]$bios.ReleaseDate).ToString('o') } else { $null }
                    }
                    motherboard = [ordered]@{
                        manufacturer = $baseBoard.Manufacturer
                        product = $baseBoard.Product
                        serial_number = $baseBoard.SerialNumber
                    }
                    processors = $processors | ForEach-Object {
                        [ordered]@{
                            name = $_.Name
                            manufacturer = $_.Manufacturer
                            cores = $_.NumberOfCores
                            logical_processors = $_.NumberOfLogicalProcessors
                            max_clock_mhz = $_.MaxClockSpeed
                        }
                    }
                    memory_modules = $memoryModules | ForEach-Object {
                        [ordered]@{
                            bank_label = $_.BankLabel
                            capacity_gb = if ($_.Capacity) { [math]::Round([double]$_.Capacity / 1GB, 2) } else { $null }
                            speed_mhz = $_.Speed
                            manufacturer = $_.Manufacturer
                            serial_number = $_.SerialNumber
                            part_number = $_.PartNumber
                        }
                    }
                    physical_disks = $physicalDisks | ForEach-Object {
                        [ordered]@{
                            model = $_.Model
                            serial_number = $_.SerialNumber
                            interface_type = $_.InterfaceType
                            media_type = $_.MediaType
                            size_gb = if ($_.Size) { [math]::Round([double]$_.Size / 1GB, 2) } else { $null }
                        }
                    }
                    logical_disks = $logicalDisks | ForEach-Object {
                        [ordered]@{
                            device_id = $_.DeviceID
                            volume_name = $_.VolumeName
                            file_system = $_.FileSystem
                            size_gb = if ($_.Size) { [math]::Round([double]$_.Size / 1GB, 2) } else { $null }
                            free_gb = if ($_.FreeSpace) { [math]::Round([double]$_.FreeSpace / 1GB, 2) } else { $null }
                        }
                    }
                    video_controllers = $videoControllers | ForEach-Object {
                        [ordered]@{
                            name = $_.Name
                            adapter_ram_gb = if ($_.AdapterRAM) { [math]::Round([double]$_.AdapterRAM / 1GB, 2) } else { $null }
                            driver_version = $_.DriverVersion
                        }
                    }
                    network_adapters = $networkAdapters | ForEach-Object {
                        $ipv4 = @($_.IPAddress | Where-Object { $_ -match '^\d{1,3}(\.\d{1,3}){3}$' })
                        [ordered]@{
                            description = $_.Description
                            mac_address = if ([string]::IsNullOrWhiteSpace($_.MACAddress)) { $null } else { ($_.MACAddress -replace '-', ':').ToUpperInvariant() }
                            dhcp_enabled = $_.DHCPEnabled
                            ip_addresses = $ipv4
                            dns_servers = @($_.DNSServerSearchOrder)
                            default_gateway = @($_.DefaultIPGateway)
                        }
                    }
                }
                software = [ordered]@{
                    operating_system = [ordered]@{
                        caption = $os.Caption
                        version = $os.Version
                        build_number = $os.BuildNumber
                        install_date = if ($os.InstallDate) { ([datetime]$os.InstallDate).ToString('o') } else { $null }
                        last_boot_up_time = if ($os.LastBootUpTime) { ([datetime]$os.LastBootUpTime).ToString('o') } else { $null }
                    }
                    office_version = $officeVersion
                    installed_programs = $installedSoftwarePreview
                    installed_programs_total = $installedSoftwareTotal
                    antivirus = $antivirusProducts
                    hotfix_count = $hotfixes.Count
                }
            }
        }
    }
}

function Should-CollectInventory([hashtable]$State) {
    if ($RunOnce) {
        return $true
    }

    $now = Get-Date
    $lastInventoryAt = Parse-StateDate $State['last_inventory_at']
    $lastInventoryAttemptAt = Parse-StateDate $State['last_inventory_attempt_at']

    if ($lastInventoryAttemptAt -and (($now - $lastInventoryAttemptAt).TotalMinutes -lt 60)) {
        return $false
    }

    if (-not $lastInventoryAt) {
        return $true
    }

    return (($now - $lastInventoryAt).TotalHours -ge $InventoryIntervalHours)
}

function Should-CollectNetworkDiscovery([hashtable]$State) {
    if ($RunOnce) {
        return $true
    }

    $now = Get-Date
    $lastDiscoveryAt = Parse-StateDate $State['last_network_discovery_at']
    $lastDiscoveryAttemptAt = Parse-StateDate $State['last_network_discovery_attempt_at']

    if ($lastDiscoveryAttemptAt -and (($now - $lastDiscoveryAttemptAt).TotalMinutes -lt 2)) {
        return $false
    }

    if (-not $lastDiscoveryAt) {
        return $true
    }

    return (($now - $lastDiscoveryAt).TotalMinutes -ge $NetworkDiscoveryIntervalMinutes)
}

function Should-CollectSnmpDiscovery([hashtable]$State) {
    if ($RunOnce) {
        return $true
    }

    if (-not $SnmpTargets -or $SnmpTargets.Count -eq 0) {
        return $false
    }

    $now = Get-Date
    $lastSnmpDiscoveryAt = Parse-StateDate $State['last_snmp_discovery_at']
    $lastSnmpDiscoveryAttemptAt = Parse-StateDate $State['last_snmp_discovery_attempt_at']

    if ($lastSnmpDiscoveryAttemptAt -and (($now - $lastSnmpDiscoveryAttemptAt).TotalMinutes -lt 2)) {
        return $false
    }

    if (-not $lastSnmpDiscoveryAt) {
        return $true
    }

    return (($now - $lastSnmpDiscoveryAt).TotalMinutes -ge $SnmpDiscoveryIntervalMinutes)
}

function Protect-SensitiveMessage([string]$Message, [hashtable]$Target = $null) {
    if ([string]::IsNullOrWhiteSpace($Message)) {
        return $Message
    }

    $sanitized = [string]$Message

    if ($Target) {
        $sensitiveValues = @(
            [string]$Target.community,
            [string]$Target.auth_passphrase,
            [string]$Target.priv_passphrase
        ) | Where-Object { -not [string]::IsNullOrWhiteSpace($_) }

        foreach ($value in $sensitiveValues) {
            $escaped = [regex]::Escape($value)
            $sanitized = [regex]::Replace($sanitized, $escaped, '***', [System.Text.RegularExpressions.RegexOptions]::IgnoreCase)
        }
    }

    if (-not [string]::IsNullOrWhiteSpace($AgentKey)) {
        $escapedAgentKey = [regex]::Escape([string]$AgentKey)
        $sanitized = [regex]::Replace($sanitized, $escapedAgentKey, '***', [System.Text.RegularExpressions.RegexOptions]::IgnoreCase)
    }

    return $sanitized
}

function Get-SnmpwalkCommand {
    $command = Get-Command snmpwalk -ErrorAction SilentlyContinue
    if ($command) {
        return $command.Source
    }

    return $null
}

function Invoke-SnmpWalk([hashtable]$Target, [string]$Oid) {
    $snmpwalkPath = Get-SnmpwalkCommand
    if ([string]::IsNullOrWhiteSpace($snmpwalkPath)) {
        return @()
    }

    $version = [string]$Target.version
    if ([string]::IsNullOrWhiteSpace($version)) {
        $version = '2c'
    }
    $version = $version.Trim().ToLowerInvariant()
    if ($version -notin @('1', '2c', '3')) {
        $version = '2c'
    }

    $arguments = @(
        '-v', $version,
        '-t', [string]$Target.timeout_seconds,
        '-r', [string]$Target.retries
    )

    if ($version -eq '3') {
        $securityName = [string]$Target.security_name
        if ([string]::IsNullOrWhiteSpace($securityName)) {
            Write-Warning "[$(Get-Date -Format s)] SNMPv3 $([string]$Target.host): falta security_name, se omite target."
            return @()
        }

        $securityLevel = [string]$Target.security_level
        if ([string]::IsNullOrWhiteSpace($securityLevel)) {
            $securityLevel = 'authPriv'
        }

        $authProtocol = [string]$Target.auth_protocol
        if ([string]::IsNullOrWhiteSpace($authProtocol)) {
            $authProtocol = 'SHA'
        }

        $privProtocol = [string]$Target.priv_protocol
        if ([string]::IsNullOrWhiteSpace($privProtocol)) {
            $privProtocol = 'AES'
        }

        $arguments += @('-l', $securityLevel, '-u', $securityName)

        if ($securityLevel -in @('authNoPriv', 'authPriv')) {
            $authPassphrase = [string]$Target.auth_passphrase
            if ([string]::IsNullOrWhiteSpace($authPassphrase)) {
                Write-Warning "[$(Get-Date -Format s)] SNMPv3 $([string]$Target.host): falta auth_passphrase para security_level=$securityLevel, se omite target."
                return @()
            }
            $arguments += @('-a', $authProtocol, '-A', $authPassphrase)
        }

        if ($securityLevel -eq 'authPriv') {
            $privPassphrase = [string]$Target.priv_passphrase
            if ([string]::IsNullOrWhiteSpace($privPassphrase)) {
                Write-Warning "[$(Get-Date -Format s)] SNMPv3 $([string]$Target.host): falta priv_passphrase para security_level=authPriv, se omite target."
                return @()
            }
            $arguments += @('-x', $privProtocol, '-X', $privPassphrase)
        }

        if (-not [string]::IsNullOrWhiteSpace([string]$Target.context_name)) {
            $arguments += @('-n', [string]$Target.context_name)
        }
    }
    else {
        $arguments += @('-c', [string]$Target.community)
    }

    $targetHost = [string]$Target.host
    if ([int]$Target.port -gt 0 -and [int]$Target.port -ne 161) {
        $targetHost = "$($Target.host):$($Target.port)"
    }

    $arguments += @($targetHost, $Oid)

    try {
        $output = & $snmpwalkPath @arguments 2>$null
        if (-not $output) {
            return @()
        }

        return @($output)
    } catch {
        $errorText = Protect-SensitiveMessage -Message $_.Exception.Message -Target $Target
        Write-Warning "[$(Get-Date -Format s)] Error SNMP en $targetHost OID $Oid: $errorText"
        return @()
    }
}

function Convert-SnmpIndexSuffixToMac([string]$Suffix) {
    if ([string]::IsNullOrWhiteSpace($Suffix)) {
        return $null
    }

    $parts = $Suffix.Split('.', [System.StringSplitOptions]::RemoveEmptyEntries)
    if ($parts.Count -lt 6) {
        return $null
    }

    $macParts = @()
    foreach ($part in $parts[-6..-1]) {
        $byteValue = 0
        if (-not [int]::TryParse($part, [ref]$byteValue)) {
            return $null
        }
        if ($byteValue -lt 0 -or $byteValue -gt 255) {
            return $null
        }
        $macParts += ('{0:X2}' -f $byteValue)
    }

    return ($macParts -join ':')
}

function Get-OuiVendor([string]$MacAddress) {
    if ([string]::IsNullOrWhiteSpace($MacAddress)) {
        return $null
    }

    $normalized = $MacAddress.ToUpperInvariant().Replace('-', ':')
    if ($normalized -notmatch '^([0-9A-F]{2}:){5}[0-9A-F]{2}$') {
        return $null
    }

    $oui = ($normalized.Split(':')[0..2] -join ':')
    $known = @{
        '00:1B:63' = 'Apple'
        '28:CF:E9' = 'Apple'
        '3C:22:FB' = 'Apple'
        '00:50:56' = 'VMware'
        '00:0C:29' = 'VMware'
        '00:1C:14' = 'VMware'
        '00:15:5D' = 'Microsoft'
        '3C:52:82' = 'Hewlett Packard'
        '7C:FE:90' = 'HPE Aruba'
        'AC:4B:C8' = 'Cisco'
        '00:25:90' = 'Cisco'
        '00:1B:54' = 'Cisco Meraki'
        'F4:F5:D8' = 'Ubiquiti'
        '24:5A:4C' = 'Ubiquiti'
        '9C:5C:8E' = 'Hikvision'
        '00:1F:3F' = 'D-Link'
        '00:1A:8C' = 'TP-Link'
        'E8:94:F6' = 'TP-Link'
        '00:11:32' = 'Synology'
        '00:40:96' = 'Aironet'
        '00:90:27' = 'Intel'
        'D8:3B:BF' = 'Intel'
        'B8:27:EB' = 'Raspberry Pi'
        'DC:A6:32' = 'Raspberry Pi'
        '00:17:88' = 'Philips'
    }

    if ($known.ContainsKey($oui)) {
        return $known[$oui]
    }

    return $null
}

function Parse-SnmpIntegerByIndex([string[]]$Lines) {
    $map = @{}
    foreach ($line in $Lines) {
        if ([string]::IsNullOrWhiteSpace($line)) { continue }
        if ($line -match '^(?<left>.+?)\s*=\s*INTEGER:\s*(?<value>-?\d+)') {
            $left = [string]$matches.left
            $value = [int]$matches.value
            if ($left -match '\.(?<index>[0-9\.]+)$') {
                $index = [string]$matches.index
                $map[$index] = $value
            }
        }
    }
    return $map
}

function Parse-SnmpStringByIndex([string[]]$Lines) {
    $map = @{}
    foreach ($line in $Lines) {
        if ([string]::IsNullOrWhiteSpace($line)) { continue }
        if ($line -match '^(?<left>.+?)\s*=\s*(STRING|Hex-STRING):\s*(?<value>.*)$') {
            $left = [string]$matches.left
            $value = ([string]$matches.value).Trim()
            if ($left -match '\.(?<index>[0-9\.]+)$') {
                $index = [string]$matches.index
                $map[$index] = $value
            }
        }
    }
    return $map
}

function Resolve-PortLabelByBridgePort([int]$BridgePort, [hashtable]$BasePortMap, [hashtable]$IfNameMap, [hashtable]$IfDescrMap) {
    if ($BridgePort -le 0) {
        return $null
    }

    $ifIndex = if ($BasePortMap.ContainsKey([string]$BridgePort)) { [int]$BasePortMap[[string]$BridgePort] } else { $null }
    if ($ifIndex -ne $null) {
        $ifIndexKey = [string]$ifIndex
        if ($IfNameMap.ContainsKey($ifIndexKey) -and -not [string]::IsNullOrWhiteSpace([string]$IfNameMap[$ifIndexKey])) {
            return [string]$IfNameMap[$ifIndexKey]
        }
        if ($IfDescrMap.ContainsKey($ifIndexKey) -and -not [string]::IsNullOrWhiteSpace([string]$IfDescrMap[$ifIndexKey])) {
            return [string]$IfDescrMap[$ifIndexKey]
        }
    }

    return "bridge-port-$BridgePort"
}

function Build-LldpNeighborHints([hashtable]$Target, [hashtable]$BasePortMap, [hashtable]$IfNameMap, [hashtable]$IfDescrMap) {
    $hints = @{}
    $lldpSysNameOid = '.1.0.8802.1.1.2.1.4.1.1.9'
    $lldpPortDescOid = '.1.0.8802.1.1.2.1.4.1.1.8'

    $sysNameLines = Invoke-SnmpWalk -Target $Target -Oid $lldpSysNameOid
    $portDescLines = Invoke-SnmpWalk -Target $Target -Oid $lldpPortDescOid

    if ((-not $sysNameLines -or $sysNameLines.Count -eq 0) -and (-not $portDescLines -or $portDescLines.Count -eq 0)) {
        return $hints
    }

    $entryByLocalPort = @{}

    foreach ($line in @($sysNameLines + $portDescLines)) {
        if ([string]::IsNullOrWhiteSpace($line)) { continue }
        if (-not ($line -match '^(?<left>.+?)\s*=\s*(STRING|Hex-STRING):\s*(?<value>.*)$')) { continue }

        $left = [string]$matches.left
        $value = ([string]$matches.value).Trim()
        if ([string]::IsNullOrWhiteSpace($value)) { continue }
        if (-not ($left -match '\.(?<suffix>[0-9\.]+)$')) { continue }

        $suffixParts = ([string]$matches.suffix).Split('.', [System.StringSplitOptions]::RemoveEmptyEntries)
        if ($suffixParts.Count -lt 2) { continue }

        $localPort = 0
        if (-not [int]::TryParse($suffixParts[1], [ref]$localPort)) { continue }
        if ($localPort -le 0) { continue }

        if (-not $entryByLocalPort.ContainsKey($localPort)) {
            $entryByLocalPort[$localPort] = [ordered]@{ sys_name = $null; port_desc = $null }
        }

        if ($left -match '\.1\.0\.8802\.1\.1\.2\.1\.4\.1\.1\.9\.') {
            $entryByLocalPort[$localPort].sys_name = $value
        } else {
            $entryByLocalPort[$localPort].port_desc = $value
        }
    }

    foreach ($localPort in $entryByLocalPort.Keys) {
        $label = Resolve-PortLabelByBridgePort -BridgePort ([int]$localPort) -BasePortMap $BasePortMap -IfNameMap $IfNameMap -IfDescrMap $IfDescrMap
        if ([string]::IsNullOrWhiteSpace($label)) { continue }

        $entry = $entryByLocalPort[$localPort]
        $neighbor = @($entry.sys_name, $entry.port_desc | Where-Object { -not [string]::IsNullOrWhiteSpace($_) }) -join ' / '
        if ([string]::IsNullOrWhiteSpace($neighbor)) { continue }

        $hints[$label] = "LLDP: $neighbor"
    }

    return $hints
}

function Build-CdpNeighborHints([hashtable]$Target, [hashtable]$IfNameMap, [hashtable]$IfDescrMap) {
    $hints = @{}
    $cdpDeviceIdOid = '.1.3.6.1.4.1.9.9.23.1.2.1.1.6'
    $cdpPortIdOid = '.1.3.6.1.4.1.9.9.23.1.2.1.1.7'

    $deviceIdLines = Invoke-SnmpWalk -Target $Target -Oid $cdpDeviceIdOid
    $portIdLines = Invoke-SnmpWalk -Target $Target -Oid $cdpPortIdOid

    if ((-not $deviceIdLines -or $deviceIdLines.Count -eq 0) -and (-not $portIdLines -or $portIdLines.Count -eq 0)) {
        return $hints
    }

    $deviceByIfIndex = @{}

    foreach ($line in @($deviceIdLines + $portIdLines)) {
        if ([string]::IsNullOrWhiteSpace($line)) { continue }
        if (-not ($line -match '^(?<left>.+?)\s*=\s*(STRING|Hex-STRING):\s*(?<value>.*)$')) { continue }

        $left = [string]$matches.left
        $value = ([string]$matches.value).Trim()
        if ([string]::IsNullOrWhiteSpace($value)) { continue }
        if (-not ($left -match '\.(?<suffix>[0-9\.]+)$')) { continue }

        $suffixParts = ([string]$matches.suffix).Split('.', [System.StringSplitOptions]::RemoveEmptyEntries)
        if ($suffixParts.Count -lt 1) { continue }

        $ifIndex = 0
        if (-not [int]::TryParse($suffixParts[0], [ref]$ifIndex)) { continue }
        if ($ifIndex -le 0) { continue }

        if (-not $deviceByIfIndex.ContainsKey($ifIndex)) {
            $deviceByIfIndex[$ifIndex] = [ordered]@{ device_id = $null; remote_port = $null }
        }

        if ($left -match '\.1\.3\.6\.1\.4\.1\.9\.9\.23\.1\.2\.1\.1\.6\.') {
            $deviceByIfIndex[$ifIndex].device_id = $value
        } else {
            $deviceByIfIndex[$ifIndex].remote_port = $value
        }
    }

    foreach ($ifIndex in $deviceByIfIndex.Keys) {
        $ifIndexKey = [string]$ifIndex
        $label = $null
        if ($IfNameMap.ContainsKey($ifIndexKey) -and -not [string]::IsNullOrWhiteSpace([string]$IfNameMap[$ifIndexKey])) {
            $label = [string]$IfNameMap[$ifIndexKey]
        } elseif ($IfDescrMap.ContainsKey($ifIndexKey) -and -not [string]::IsNullOrWhiteSpace([string]$IfDescrMap[$ifIndexKey])) {
            $label = [string]$IfDescrMap[$ifIndexKey]
        }

        if ([string]::IsNullOrWhiteSpace($label)) {
            $label = "ifIndex-$ifIndex"
        }

        $entry = $deviceByIfIndex[$ifIndex]
        $neighbor = @($entry.device_id, $entry.remote_port | Where-Object { -not [string]::IsNullOrWhiteSpace($_) }) -join ' / '
        if ([string]::IsNullOrWhiteSpace($neighbor)) { continue }

        $hints[$label] = "CDP: $neighbor"
    }

    return $hints
}

function Build-PortNeighborHints([hashtable]$Target, [hashtable]$BasePortMap, [hashtable]$IfNameMap, [hashtable]$IfDescrMap) {
    $hints = @{}

    $lldpHints = Build-LldpNeighborHints -Target $Target -BasePortMap $BasePortMap -IfNameMap $IfNameMap -IfDescrMap $IfDescrMap
    foreach ($key in $lldpHints.Keys) {
        $hints[$key] = $lldpHints[$key]
    }

    $cdpHints = Build-CdpNeighborHints -Target $Target -IfNameMap $IfNameMap -IfDescrMap $IfDescrMap
    foreach ($key in $cdpHints.Keys) {
        if ($hints.ContainsKey($key)) {
            $hints[$key] = "$($hints[$key]) | $($cdpHints[$key])"
        } else {
            $hints[$key] = $cdpHints[$key]
        }
    }

    return $hints
}

function Collect-SnmpObservedDevices([hashtable]$Target) {
    $fdbPortOid = '.1.3.6.1.2.1.17.4.3.1.2'
    $basePortToIfIndexOid = '.1.3.6.1.2.1.17.1.4.1.2'
    $ifNameOid = '.1.3.6.1.2.1.31.1.1.1.1'
    $ifDescrOid = '.1.3.6.1.2.1.2.2.1.2'

    $fdbLines = Invoke-SnmpWalk -Target $Target -Oid $fdbPortOid
    if (-not $fdbLines -or $fdbLines.Count -eq 0) {
        return @()
    }

    $basePortMap = Parse-SnmpIntegerByIndex (Invoke-SnmpWalk -Target $Target -Oid $basePortToIfIndexOid)
    $ifNameMap = Parse-SnmpStringByIndex (Invoke-SnmpWalk -Target $Target -Oid $ifNameOid)
    $ifDescrMap = Parse-SnmpStringByIndex (Invoke-SnmpWalk -Target $Target -Oid $ifDescrOid)
    $portNeighborHints = Build-PortNeighborHints -Target $Target -BasePortMap $basePortMap -IfNameMap $ifNameMap -IfDescrMap $ifDescrMap

    $devices = @()
    $seenMac = @{}

    foreach ($line in $fdbLines) {
        if ([string]::IsNullOrWhiteSpace($line)) { continue }
        if (-not ($line -match '^(?<left>.+?)\s*=\s*INTEGER:\s*(?<bridgePort>-?\d+)')) { continue }

        $left = [string]$matches.left
        $bridgePort = [int]$matches.bridgePort
        if ($bridgePort -le 0) { continue }

        if (-not ($left -match '\.(?<suffix>[0-9\.]+)$')) { continue }
        $suffix = [string]$matches.suffix
        $macAddress = Convert-SnmpIndexSuffixToMac $suffix
        if ([string]::IsNullOrWhiteSpace($macAddress)) { continue }

        if ($seenMac.ContainsKey($macAddress)) {
            continue
        }
        $seenMac[$macAddress] = $true

        $ifIndex = if ($basePortMap.ContainsKey([string]$bridgePort)) { [int]$basePortMap[[string]$bridgePort] } else { $null }
        $portLabel = Resolve-PortLabelByBridgePort -BridgePort $bridgePort -BasePortMap $basePortMap -IfNameMap $ifNameMap -IfDescrMap $ifDescrMap
        $neighborHint = if ($portNeighborHints.ContainsKey($portLabel)) { [string]$portNeighborHints[$portLabel] } else { $null }
        $vendorName = Get-OuiVendor $macAddress

        $devices += [ordered]@{
            mac_address = $macAddress
            ip_address = $null
            hostname = $null
            domain_name = $null
            vendor_name = $vendorName
            ssid = $null
            switch_port = $portLabel
            device_type = $null
            meta = [ordered]@{
                source = 'snmp-fdb'
                target_name = if ([string]::IsNullOrWhiteSpace([string]$Target.name)) { $Target.host } else { $Target.name }
                target_host = $Target.host
                bridge_port = $bridgePort
                if_index = $ifIndex
                neighbor_hint = $neighborHint
            }
            last_seen_at = (Get-Date).ToString('o')
        }

        if ($devices.Count -ge [int]$Target.sample_limit) {
            break
        }
    }

    return $devices
}

function Send-NetworkObservations([int]$NodeId, [string]$ObservedVia, $Devices) {
    if ($NodeId -le 0 -or -not $Devices -or $Devices.Count -eq 0) {
        return $false
    }

    $payload = [ordered]@{
        node_id = $NodeId
        observed_via = $ObservedVia
        observed_at = (Get-Date).ToString('o')
        devices = $Devices
    }

    $json = $payload | ConvertTo-Json -Depth 12
    $jsonBytes = [System.Text.Encoding]::UTF8.GetBytes($json)
    $headers = @{ 'X-Agent-Key' = $AgentKey; 'Accept' = 'application/json' }
    if (-not [string]::IsNullOrWhiteSpace($TenantHost)) {
        $headers['Host'] = $TenantHost
    }

    try {
        Invoke-RestMethod -Uri $ObservationsEndpoint -Method POST -Headers $headers -ContentType 'application/json; charset=utf-8' -Body $jsonBytes | Out-Null
        return $true
    } catch {
        Write-Warning "[$(Get-Date -Format s)] Error enviando observaciones de red para node_id=$NodeId: $($_.Exception.Message)"
        return $false
    }
}

function Run-SnmpDiscoveryCycle {
    if (-not $SnmpTargets -or $SnmpTargets.Count -eq 0) {
        return
    }

    $snmpwalkPath = Get-SnmpwalkCommand
    if ([string]::IsNullOrWhiteSpace($snmpwalkPath)) {
        Write-Warning "snmpwalk no está disponible en PATH. Se omite discovery SNMP."
        return
    }

    $successfulPosts = 0
    $totalTargets = 0

    foreach ($target in $SnmpTargets) {
        $totalTargets++
        $devices = Collect-SnmpObservedDevices -Target $target
        if (-not $devices -or $devices.Count -eq 0) {
            Write-Host "[$(Get-Date -Format s)] SNMP $($target.host): sin dispositivos FDB detectados"
            continue
        }

        $ok = Send-NetworkObservations -NodeId ([int]$target.node_id) -ObservedVia 'snmp-fdb' -Devices $devices
        if ($ok) {
            $successfulPosts++
            Write-Host "[$(Get-Date -Format s)] SNMP $($target.host): enviados $($devices.Count) dispositivos al nodo $($target.node_id)"
        }
    }

    if ($successfulPosts -gt 0) {
        $script:AgentState['last_snmp_discovery_at'] = (Get-Date).ToString('o')
        Save-AgentState $script:AgentState
    }

    Write-Host "[$(Get-Date -Format s)] Discovery SNMP finalizado: $successfulPosts/$totalTargets targets con envío exitoso"
}

function Resolve-HostnameFromIPv4([string]$IpAddress) {
    if ([string]::IsNullOrWhiteSpace($IpAddress)) {
        return $null
    }

    try {
        $entry = [System.Net.Dns]::GetHostEntry($IpAddress)
        if ($entry -and -not [string]::IsNullOrWhiteSpace($entry.HostName)) {
            return $entry.HostName.Trim()
        }
    } catch {
    }

    return $null
}

function Resolve-DomainFromHostname([string]$Hostname) {
    if ([string]::IsNullOrWhiteSpace($Hostname)) {
        return $null
    }

    $parts = $Hostname.Split('.', [System.StringSplitOptions]::RemoveEmptyEntries)
    if ($parts.Count -le 1) {
        return $null
    }

    return ($parts[1..($parts.Count - 1)] -join '.').ToLowerInvariant()
}

function Collect-NetworkDiscoveryBundle {
    $neighbors = @()
    try {
        $neighbors = @(Get-NetNeighbor -AddressFamily IPv4 -ErrorAction Stop)
    } catch {
        return [ordered]@{
            source = 'windows-netneighbor'
            observed_at = (Get-Date).ToString('o')
            devices = @()
            stats = [ordered]@{
                scanned = 0
                kept = 0
                note = 'Get-NetNeighbor no disponible o sin permisos.'
            }
        }
    }

    $hostnameCache = @{}
    $devices = @()
    $seenKeys = @{}

    foreach ($neighbor in $neighbors) {
        $ip = [string]$neighbor.IPAddress
        $mac = [string]$neighbor.LinkLayerAddress
        $state = [string]$neighbor.State
        $iface = [string]$neighbor.InterfaceAlias

        if ([string]::IsNullOrWhiteSpace($ip) -or $ip -eq '0.0.0.0' -or $ip.StartsWith('169.254.')) {
            continue
        }

        if ([string]::IsNullOrWhiteSpace($mac) -or $mac -eq '00-00-00-00-00-00') {
            continue
        }

        $normalizedMac = ($mac -replace '-', ':').ToUpperInvariant()
        if ($normalizedMac -notmatch '^([0-9A-F]{2}:){5}[0-9A-F]{2}$') {
            continue
        }

        $key = "$ip|$normalizedMac"
        if ($seenKeys.ContainsKey($key)) {
            continue
        }
        $seenKeys[$key] = $true

        if (-not $hostnameCache.ContainsKey($ip)) {
            $hostnameCache[$ip] = Resolve-HostnameFromIPv4 $ip
        }
        $hostname = $hostnameCache[$ip]
        $domainName = Resolve-DomainFromHostname $hostname
        $vendorName = Get-OuiVendor $normalizedMac

        $devices += [ordered]@{
            mac_address = $normalizedMac
            ip_address = $ip
            hostname = $hostname
            domain_name = $domainName
            vendor_name = $vendorName
            ssid = $null
            switch_port = if ([string]::IsNullOrWhiteSpace($iface)) { $null } else { $iface }
            device_type = $null
            meta = [ordered]@{
                neighbor_state = $state
                interface_alias = $iface
            }
            last_seen_at = (Get-Date).ToString('o')
        }

        if ($devices.Count -ge $NetworkDiscoverySampleLimit) {
            break
        }
    }

    return [ordered]@{
        source = 'windows-netneighbor'
        observed_at = (Get-Date).ToString('o')
        devices = $devices
        stats = [ordered]@{
            scanned = $neighbors.Count
            kept = $devices.Count
        }
    }
}

function Collect-Heartbeat($InventoryBundle, $NetworkDiscoveryBundle) {
    $cpu = Get-CpuUsagePercent

    $os = Get-CimInstance Win32_OperatingSystem
    $totalMemKb = [double]$os.TotalVisibleMemorySize
    $freeMemKb = [double]$os.FreePhysicalMemory
    $usedMemKb = $totalMemKb - $freeMemKb
    $memoryPercent = Get-Percent -Used $usedMemKb -Total $totalMemKb

    $systemDrive = (Get-CimInstance Win32_OperatingSystem).SystemDrive
    $disk = Get-CimInstance Win32_LogicalDisk -Filter "DeviceID='$systemDrive'" | Select-Object -First 1
    $diskPercent = $null
    if ($disk -and $disk.Size -gt 0) {
        $diskPercent = Get-Percent -Used ($disk.Size - $disk.FreeSpace) -Total $disk.Size
    }

    $uptimeSeconds = [int]((Get-Date) - $os.LastBootUpTime).TotalSeconds
    $processCount = (Get-Process | Measure-Object).Count

    $network = Get-Counter '\Network Interface(*)\Bytes Received/sec','\Network Interface(*)\Bytes Sent/sec' -ErrorAction SilentlyContinue
    $rxBps = 0.0
    $txBps = 0.0
    if ($network) {
        $network.CounterSamples | ForEach-Object {
            if ($_.Path -like '*Bytes Received/sec*') { $rxBps += $_.CookedValue }
            if ($_.Path -like '*Bytes Sent/sec*') { $txBps += $_.CookedValue }
        }
    }

    $hostname = $env:COMPUTERNAME
    if ([string]::IsNullOrWhiteSpace($hostname)) {
        $hostname = [System.Environment]::MachineName
    }
    if ([string]::IsNullOrWhiteSpace($hostname)) {
        $hostname = 'unknown-host'
    }
    $serial = (Get-CimInstance Win32_BIOS).SerialNumber
    $cpuName = (Get-CimInstance Win32_Processor | Select-Object -First 1).Name
    $ramGb = [math]::Round($totalMemKb / 1MB, 0)
    $netAdapter = Get-CimInstance Win32_NetworkAdapterConfiguration |
        Where-Object { $_.IPEnabled -eq $true -and $_.IPAddress } |
        Select-Object -First 1

    $primaryIPv4 = $null
    $macAddress = $null
    if ($netAdapter) {
        $primaryIPv4 = $netAdapter.IPAddress |
            Where-Object { $_ -match '^\d{1,3}(\.\d{1,3}){3}$' } |
            Select-Object -First 1
        if (-not [string]::IsNullOrWhiteSpace($netAdapter.MACAddress)) {
            $macAddress = ($netAdapter.MACAddress -replace '-', ':').ToUpperInvariant()
        }
    }

    $computerSystem = Get-CimInstance Win32_ComputerSystem | Select-Object -First 1
    $primaryGpu = (Get-CimInstance Win32_VideoController | Select-Object -First 1).Name

    $equipmentType = if ($InventoryBundle) { $InventoryBundle.equipment_type } else { 'desktop' }
    $storageType = if ($InventoryBundle -and $InventoryBundle.storage_type) { $InventoryBundle.storage_type } else { 'ssd' }
    $storageGb = if ($InventoryBundle) { $InventoryBundle.storage_gb } else { $null }
    $brand = if ($InventoryBundle) { $InventoryBundle.brand } else { $null }
    $model = if ($InventoryBundle) { $InventoryBundle.model } else { $null }
    $officeVersion = if ($InventoryBundle) { $InventoryBundle.office_version } else { $null }
    $details = if ($InventoryBundle -and $InventoryBundle.details) { $InventoryBundle.details } else { [ordered]@{} }

    if ($InventoryBundle -eq $null) {
        if (-not ($details.Contains('inventory'))) {
            $details['inventory'] = [ordered]@{}
        }

        $details['inventory']['captured_at'] = (Get-Date).ToString('o')
        $details['inventory']['capture_scope'] = 'lightweight'

        if (-not ($details['inventory'].Contains('hardware'))) {
            $details['inventory']['hardware'] = [ordered]@{}
        }

        $details['inventory']['hardware']['system'] = [ordered]@{
            domain = if ($computerSystem) { $computerSystem.Domain } else { $null }
        }

        $details['inventory']['hardware']['network'] = [ordered]@{
            primary_ip_address = $primaryIPv4
            primary_mac_address = $macAddress
        }

        $details['inventory']['hardware']['video'] = [ordered]@{
            primary_gpu = $primaryGpu
        }

        if (-not ($details['inventory'].Contains('software'))) {
            $details['inventory']['software'] = [ordered]@{}
        }

        $details['inventory']['software']['operating_system'] = [ordered]@{
            caption = $os.Caption
            version = $os.Version
            build_number = $os.BuildNumber
        }
    }

    if ($NetworkDiscoveryBundle -and $NetworkDiscoveryBundle.devices) {
        $details['network_discovery'] = [ordered]@{
            source = $NetworkDiscoveryBundle.source
            observed_at = $NetworkDiscoveryBundle.observed_at
            devices = $NetworkDiscoveryBundle.devices
            stats = $NetworkDiscoveryBundle.stats
        }
    }

    return [ordered]@{
        asset_tag = if ([string]::IsNullOrWhiteSpace($AssetTag)) { $hostname } else { $AssetTag }
        hostname = $hostname
        ip_address = $primaryIPv4
        mac_address = $macAddress
        branch_id = $BranchId
        equipment_type = $equipmentType
        serial_number = $serial
        brand = $brand
        model = $model
        operating_system = $os.Caption
        office_version = $officeVersion
        cpu = $cpuName
        ram_gb = [int]$ramGb
        storage_type = $storageType
        storage_gb = $storageGb
        status = 'in_use'
        agent_name = 'itcity-windows-agent'
        platform = 'windows'
        details = $details
        metrics = [ordered]@{
            cpu_usage_percent = $cpu
            memory_usage_percent = $memoryPercent
            disk_usage_percent = $diskPercent
            uptime_seconds = $uptimeSeconds
            net_rx_kbps = [math]::Round(($rxBps * 8) / 1000, 2)
            net_tx_kbps = [math]::Round(($txBps * 8) / 1000, 2)
            process_count = $processCount
            details = [ordered]@{
                os_build = $os.BuildNumber
                system_drive = $systemDrive
                inventory_refreshed = [bool]($InventoryBundle -ne $null)
                network_discovery_refreshed = [bool]($NetworkDiscoveryBundle -ne $null)
            }
        }
    }
}

$script:AgentState = Load-AgentState

function Send-Heartbeat {
    $inventoryBundle = $null
    if (Should-CollectInventory -State $script:AgentState) {
        $script:AgentState['last_inventory_attempt_at'] = (Get-Date).ToString('o')
        Save-AgentState $script:AgentState
        $inventoryBundle = Collect-InventoryBundle
    }

    $networkDiscoveryBundle = $null
    if (Should-CollectNetworkDiscovery -State $script:AgentState) {
        $script:AgentState['last_network_discovery_attempt_at'] = (Get-Date).ToString('o')
        Save-AgentState $script:AgentState
        $networkDiscoveryBundle = Collect-NetworkDiscoveryBundle
    }

    $shouldSnmpDiscovery = Should-CollectSnmpDiscovery -State $script:AgentState
    if ($shouldSnmpDiscovery) {
        $script:AgentState['last_snmp_discovery_attempt_at'] = (Get-Date).ToString('o')
        Save-AgentState $script:AgentState
    }

    $payload = Collect-Heartbeat -InventoryBundle $inventoryBundle -NetworkDiscoveryBundle $networkDiscoveryBundle
    if ([string]::IsNullOrWhiteSpace([string]$payload.hostname)) {
        $fallbackHostname = [System.Environment]::MachineName
        if ([string]::IsNullOrWhiteSpace($fallbackHostname)) {
            $fallbackHostname = 'unknown-host'
        }
        $payload.hostname = $fallbackHostname
    }

    if ([string]::IsNullOrWhiteSpace([string]$payload.asset_tag)) {
        $payload.asset_tag = $payload.hostname
    }

    $json = $payload | ConvertTo-Json -Depth 12
    $jsonBytes = [System.Text.Encoding]::UTF8.GetBytes($json)
    $headers = @{ 'X-Agent-Key' = $AgentKey; 'Accept' = 'application/json' }
    if (-not [string]::IsNullOrWhiteSpace($TenantHost)) {
        $headers['Host'] = $TenantHost
    }

    try {
        $response = Invoke-RestMethod -Uri $Endpoint -Method POST -Headers $headers -ContentType 'application/json; charset=utf-8' -Body $jsonBytes
        if ($inventoryBundle -ne $null) {
            $script:AgentState['last_inventory_at'] = (Get-Date).ToString('o')
        }
        if ($networkDiscoveryBundle -ne $null) {
            $script:AgentState['last_network_discovery_at'] = (Get-Date).ToString('o')
        }
        Save-AgentState $script:AgentState
        Write-Host "[$(Get-Date -Format s)] Heartbeat OK -> Asset ID: $($response.asset_id)"

        if ($shouldSnmpDiscovery) {
            Run-SnmpDiscoveryCycle
        }
    } catch {
        $safeExceptionMessage = Protect-SensitiveMessage -Message $_.Exception.Message
        Write-Warning "[$(Get-Date -Format s)] Error enviando heartbeat: $safeExceptionMessage"
        if ($_.Exception.Response -and $_.Exception.Response.GetResponseStream()) {
            $reader = New-Object System.IO.StreamReader($_.Exception.Response.GetResponseStream())
            $responseBody = $reader.ReadToEnd()
            if (-not [string]::IsNullOrWhiteSpace($responseBody)) {
                $safeResponseBody = Protect-SensitiveMessage -Message $responseBody
                Write-Warning "Respuesta del servidor: $safeResponseBody"
            }
        }
    }
}

if ($RunOnce) {
    Send-Heartbeat
    exit 0
}

Write-Host "Iniciando agente heartbeat. Endpoint: $Endpoint"
Write-Host "Endpoint observaciones: $ObservationsEndpoint"
Write-Host "Intervalo: $IntervalSeconds segundos"
Write-Host "Inventario extendido: cada $InventoryIntervalHours horas"
Write-Host "Discovery de red: cada $NetworkDiscoveryIntervalMinutes minutos (max $NetworkDiscoverySampleLimit dispositivos)"
Write-Host "Discovery SNMP: cada $SnmpDiscoveryIntervalMinutes minutos (max default $SnmpSampleLimit por target, targets configurados: $($SnmpTargets.Count))"
if (-not [string]::IsNullOrWhiteSpace($ConfigPath)) {
    Write-Host "Configuracion: $ConfigPath"
}
if (-not [string]::IsNullOrWhiteSpace($StatePath)) {
    Write-Host "Estado: $StatePath"
}

while ($true) {
    Send-Heartbeat
    Start-Sleep -Seconds $IntervalSeconds
}
