export enum OrderStatus {
    PENDING = 0,
    PROCESSING = 1,
    PAID = 2,
    FAILED = 3,
    CANCELLED = 4,
    REFUNDED = 5,
    MANUAL = 6,
    DEMO = 7,
    DEMO_EXPIRED = 8,
}

export function getOrderStatusLabel(status: OrderStatus): string {
    switch (status) {
        case OrderStatus.PENDING:
            return 'Pendiente';
        case OrderStatus.PROCESSING:
            return 'Procesando';
        case OrderStatus.PAID:
            return 'Pagado';
        case OrderStatus.FAILED:
            return 'Fallido';
        case OrderStatus.CANCELLED:
            return 'Cancelado';
        case OrderStatus.REFUNDED:
            return 'Reembolsado';
        case OrderStatus.MANUAL:
            return 'Manual';
        case OrderStatus.DEMO:
            return 'Demo';
        case OrderStatus.DEMO_EXPIRED:
            return 'Demo Expirado';
        default:
            return 'Desconocido';
    }
}
