<?php

namespace App\Enums;

enum OrderStatus: int
{
    case PENDING = 0;
    case PROCESSING = 1;
    case PAID = 2;
    case FAILED = 3;
    case CANCELLED = 4;
    case REFUNDED = 5;
    case MANUAL = 6; // Para compras fuera de la plataforma
    case DEMO = 7; // Demo temporal (12 horas) desde WATI
    case DEMO_EXPIRED = 8; // Demo que ya expiró

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pendiente',
            self::PROCESSING => 'Procesando',
            self::PAID => 'Pagado',
            self::FAILED => 'Fallido',
            self::CANCELLED => 'Cancelado',
            self::REFUNDED => 'Reembolsado',
            self::MANUAL => 'Manual',
            self::DEMO => 'Demo',
            self::DEMO_EXPIRED => 'Demo Expirado',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => '#F59E0B',
            self::PROCESSING => '#3B82F6',
            self::PAID => '#10B981',
            self::FAILED => '#EF4444',
            self::CANCELLED => '#6B7280',
            self::REFUNDED => '#8B5CF6',
            self::MANUAL => '#F97316',
            self::DEMO => '#EC4899', // Rosa para demo
            self::DEMO_EXPIRED => '#DC2626', // Rojo para demo expirado
        };
    }
}
