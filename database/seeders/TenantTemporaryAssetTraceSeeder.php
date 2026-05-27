<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\ComputerAsset;
use Illuminate\Database\Seeder;

class TenantTemporaryAssetTraceSeeder extends Seeder
{
    public function run(): void
    {
        $corporativoId = Branch::query()->whereRaw('LOWER(name) = ?', ['corporativo'])->value('id');
        $andaresId = Branch::query()->whereRaw('LOWER(name) = ?', ['andares'])->value('id');
        $tepicId = Branch::query()->whereRaw('LOWER(name) = ?', ['tepic'])->value('id');

        if (!$corporativoId || !$andaresId || !$tepicId) {
            $this->command?->warn('No se encontraron las sedes requeridas: Corporativo, Andares y Tepic.');
            return;
        }

        $serialNumber = 'TMP-MOV-20260527-01';
        $assetTag = 'TEST-DELETE-ME-0001';

        $assignmentLog = [
            [
                'at' => '2025-01-15T10:00:00-06:00',
                'by' => 'Sistema QA',
                'event_type' => 'purchase',
                'serial_number' => $serialNumber,
                'description' => 'laptop | Lenovo | ThinkPad T14 Gen 3 | LT-QA-001 | ' . $assetTag,
                'invoice_folio' => 'FAC-AND-250115-001',
                'supplier' => 'Distribuidora Demo Norte',
                'campus' => 'Andares',
                'assigned_at' => '2025-01-16',
                'received_by' => 'Valeria Compras',
                'assigned_user' => 'Jorge Perez (ficticio)',
                'change_reason' => 'Alta inicial por compra en Andares',
                'interaction_note' => 'Registro inicial para pruebas de trazabilidad',
            ],
            [
                'at' => '2025-03-02T09:30:00-06:00',
                'by' => 'Mesa de Ayuda QA',
                'event_type' => 'reassignment',
                'serial_number' => $serialNumber,
                'description' => 'laptop | Lenovo | ThinkPad T14 Gen 3 | LT-QA-001 | ' . $assetTag,
                'invoice_folio' => 'FAC-AND-250115-001',
                'supplier' => 'Distribuidora Demo Norte',
                'campus' => 'Andares',
                'assigned_at' => '2025-03-02',
                'received_by' => 'Lucia Soporte',
                'assigned_user' => 'Ana Ruiz (ficticio)',
                'change_reason' => 'Reasignacion interna por cambio de area',
                'interaction_note' => 'Se actualiza responsable del activo',
            ],
            [
                'at' => '2025-05-20T12:10:00-06:00',
                'by' => 'Control de Activos QA',
                'event_type' => 'transfer',
                'serial_number' => $serialNumber,
                'description' => 'laptop | Lenovo | ThinkPad T14 Gen 3 | LT-QA-001 | ' . $assetTag,
                'invoice_folio' => 'TRAS-TEP-250520-014',
                'supplier' => 'Transferencia interna',
                'campus' => 'Tepic',
                'assigned_at' => '2025-05-22',
                'received_by' => 'Carlos Almacen Tepic',
                'assigned_user' => 'Mario Leon (ficticio)',
                'transfer_request_id' => 5014,
                'transfer_from_branch' => 'Andares',
                'transfer_to_branch' => 'Tepic',
                'transfer_from_user' => 'Ana Ruiz (ficticio)',
                'transfer_to_user' => 'Mario Leon (ficticio)',
                'transfer_priority' => 'high',
                'change_reason' => 'Prestamo temporal para apertura de sede Tepic',
                'interaction_note' => 'Movimiento intersede Andares a Tepic',
            ],
            [
                'at' => '2025-08-08T16:45:00-06:00',
                'by' => 'Control de Activos QA',
                'event_type' => 'transfer',
                'serial_number' => $serialNumber,
                'description' => 'laptop | Lenovo | ThinkPad T14 Gen 3 | LT-QA-001 | ' . $assetTag,
                'invoice_folio' => 'TRAS-CORP-250808-031',
                'supplier' => 'Transferencia interna',
                'campus' => 'Corporativo',
                'assigned_at' => '2025-08-09',
                'received_by' => 'Patricia RH Corporativo',
                'assigned_user' => 'Sofia Medina (ficticio)',
                'transfer_request_id' => 5031,
                'transfer_from_branch' => 'Tepic',
                'transfer_to_branch' => 'Corporativo',
                'transfer_from_user' => 'Mario Leon (ficticio)',
                'transfer_to_user' => 'Sofia Medina (ficticio)',
                'transfer_priority' => 'normal',
                'change_reason' => 'Retorno del activo a Corporativo',
                'interaction_note' => 'Movimiento intersede Tepic a Corporativo',
            ],
            [
                'at' => '2025-11-12T11:20:00-06:00',
                'by' => 'Soporte Nivel 2 QA',
                'event_type' => 'reassignment',
                'serial_number' => $serialNumber,
                'description' => 'laptop | Lenovo | ThinkPad T14 Gen 3 | LT-QA-001 | ' . $assetTag,
                'invoice_folio' => 'AJU-CORP-251112-007',
                'supplier' => 'N/A',
                'campus' => 'Corporativo',
                'assigned_at' => '2025-11-12',
                'received_by' => 'Sofia Medina (ficticio)',
                'assigned_user' => 'Daniela Cruz (ficticio)',
                'change_reason' => 'Rotacion interna de equipo por reemplazo temporal',
                'interaction_note' => 'Evento de prueba adicional para nutrir la bitacora',
            ],
            [
                'at' => '2025-12-03T08:15:00-06:00',
                'by' => 'Mesa de Servicio QA',
                'event_type' => 'repair',
                'serial_number' => $serialNumber,
                'description' => 'laptop | Lenovo | ThinkPad T14 Gen 3 | LT-QA-001 | ' . $assetTag,
                'invoice_folio' => 'RPR-CORP-251203-004',
                'supplier' => 'Soporte Técnico Interno',
                'campus' => 'Corporativo',
                'assigned_at' => '2025-12-03',
                'received_by' => 'Taller TI Corporativo',
                'assigned_user' => 'En reparacion',
                'change_reason' => 'Equipo enviado a mantenimiento preventivo por desgaste de batería',
                'interaction_note' => 'Ingreso a taller interno con diagnóstico inicial',
            ],
            [
                'at' => '2025-12-06T18:25:00-06:00',
                'by' => 'Taller TI Corporativo',
                'event_type' => 'repair_return',
                'serial_number' => $serialNumber,
                'description' => 'laptop | Lenovo | ThinkPad T14 Gen 3 | LT-QA-001 | ' . $assetTag,
                'invoice_folio' => 'RPR-CORP-251203-004',
                'supplier' => 'Soporte Técnico Interno',
                'campus' => 'Corporativo',
                'assigned_at' => '2025-12-06',
                'received_by' => 'Daniela Cruz (ficticio)',
                'assigned_user' => 'Daniela Cruz (ficticio)',
                'change_reason' => 'Equipo liberado de mantenimiento y devuelto a operación',
                'interaction_note' => 'Se prueba el equipo y queda funcional nuevamente',
            ],
            [
                'at' => '2026-02-10T10:05:00-06:00',
                'by' => 'Mesa de Ayuda QA',
                'event_type' => 'reassignment',
                'serial_number' => $serialNumber,
                'description' => 'laptop | Lenovo | ThinkPad T14 Gen 3 | LT-QA-001 | ' . $assetTag,
                'invoice_folio' => 'REASIG-CORP-260210-008',
                'supplier' => 'N/A',
                'campus' => 'Corporativo',
                'assigned_at' => '2026-02-10',
                'received_by' => 'Daniela Cruz (ficticio)',
                'assigned_user' => 'Ricardo Flores (ficticio)',
                'change_reason' => 'Reasignacion por cambio de proyecto',
                'interaction_note' => 'Nueva asignacion temporal para pruebas de carga',
            ],
            [
                'at' => '2026-04-18T14:40:00-06:00',
                'by' => 'Control de Activos QA',
                'event_type' => 'transfer',
                'serial_number' => $serialNumber,
                'description' => 'laptop | Lenovo | ThinkPad T14 Gen 3 | LT-QA-001 | ' . $assetTag,
                'invoice_folio' => 'TRAS-CORP-260418-019',
                'supplier' => 'Transferencia interna',
                'campus' => 'Tepic',
                'assigned_at' => '2026-04-20',
                'received_by' => 'Almacen Tepic',
                'assigned_user' => 'Ricardo Flores (ficticio)',
                'transfer_request_id' => 6019,
                'transfer_from_branch' => 'Corporativo',
                'transfer_to_branch' => 'Tepic',
                'transfer_from_user' => 'Ricardo Flores (ficticio)',
                'transfer_to_user' => 'Mario Leon (ficticio)',
                'transfer_priority' => 'normal',
                'change_reason' => 'Traslado temporal para pruebas en sitio',
                'interaction_note' => 'El equipo vuelve a rotar por necesidad operativa',
            ],
        ];

        $details = [
            'procurement' => [
                'purchase_order_number' => 'OC-AND-2025-00127',
                'purchased_at' => '2025-01-15',
                'purchase_branch' => 'Andares',
                'supplier' => 'Distribuidora Demo Norte',
                'origin_branch' => 'Andares',
                'transit_branch' => 'Tepic',
                'current_branch' => 'Corporativo',
                'lifecycle_events' => count($assignmentLog),
            ],
            'assignment_log' => $assignmentLog,
            'history' => [
                [
                    'at' => '2025-01-15T10:00:00-06:00',
                    'by' => 'Sistema QA',
                    'changes' => ['Activo de pruebas creado para trazabilidad avanzada'],
                    'note' => 'Eliminar cuando termine validacion funcional',
                ],
            ],
            'qa_fixture' => [
                'temporary' => true,
                'delete_when_done' => true,
                'label' => 'asset_traceability_fixture_20260527',
            ],
        ];

        $asset = ComputerAsset::query()->updateOrCreate(
            ['serial_number' => $serialNumber],
            [
                'branch_id' => (int) $corporativoId,
                'equipment_type' => 'laptop',
                'asset_tag' => $assetTag,
                'hostname' => 'LT-QA-001',
                'assigned_user' => 'Daniela Cruz (ficticio)',
                'brand' => 'Lenovo',
                'model' => 'ThinkPad T14 Gen 3',
                'cpu' => 'Intel Core i7-1260P',
                'ram_gb' => 16,
                'storage_type' => 'ssd',
                'storage_gb' => 512,
                'operating_system' => 'Windows 11 Pro',
                'purchase_date' => '2025-01-15',
                'status' => 'in_use',
                'notes' => 'ACTIVO TEMPORAL DE PRUEBA - ELIMINAR AL FINALIZAR VALIDACIONES',
                'details' => $details,
            ]
        );

        $this->command?->info(sprintf(
            'Activo temporal listo: ID %d | Tag %s | Serie %s | Bitacora %d eventos',
            (int) $asset->id,
            (string) $asset->asset_tag,
            (string) $asset->serial_number,
            count($assignmentLog)
        ));
    }
}
