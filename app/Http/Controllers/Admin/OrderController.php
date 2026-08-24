<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Admin/Orders/Index');
    }

    public function store(Request $request)
    {
        try {
            $category = (new CategoryService)->create($request->all());

            return response()->json(['category' => $category], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $order = Order::findOrFail($id);

            // Validar el estado
            $request->validate([
                'status_id' => 'required|integer|in:0,1,2,3,4,5,6,7,8',
            ]);

            $updateData = [
                'status_id' => $request->status_id,
            ];

            // Si se marca como MANUAL, guardar quién lo cambió
            if ($request->status_id == OrderStatus::MANUAL->value) {
                $updateData['created_by_user_id'] = auth()->id();
            }

            // Si se marca como pagado y no tiene fecha de pago, agregarla
            if ($request->status_id == OrderStatus::PAID->value && ! $order->paid_at) {
                $updateData['paid_at'] = now();
            }

            // Si se cambia de DEMO o DEMO_EXPIRED a PAGADO, limpiar fecha de expiración
            if ($request->status_id == OrderStatus::PAID->value && 
                in_array($order->status_id, [OrderStatus::DEMO->value, OrderStatus::DEMO_EXPIRED->value])) {
                $updateData['demo_expires_at'] = null;
            }

            $order->update($updateData);

            return response()->json([
                'order' => $order->load(['items', 'user']),
                'message' => 'Estado de orden actualizado exitosamente',
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function show(Request $request, string $id)
    {
        $order = Order::with(['items.course', 'user'])->find($id);

        return Inertia::render('Admin/Orders/Show', ['order' => $order]);
    }

    public function list(Request $request): JsonResponse
    {
        $query = Order::query()->withCount(['items'])->with(['user']);
        $perPage = $this->getPerPage($request);

        // Búsqueda general en múltiples campos
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                // Buscar en número de orden
                $q->where('number', 'like', "%{$search}%")
                  // Buscar en descripción de la orden
                    ->orWhere('description', 'like', "%{$search}%")
                  // Buscar en datos del usuario
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhereRaw("CONCAT(name, ' ', last_name) like ?", ["%{$search}%"])
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        // Filtro por estado
        if ($request->filled('status')) {
            $query->where('status_id', $request->input('status'));
        }

        // Filtro por rango de fechas
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $data = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json($data);
    }

    public function updateStatus(Request $request, string $id)
    {
        try {
            $order = Order::findOrFail($id);

            $request->validate([
                'status_id' => 'required|integer|in:0,1,2,3,4,5,6,7,8',
            ]);

            $updateData = [
                'status_id' => $request->status_id,
            ];

            // Si se marca como MANUAL, guardar quién lo cambió
            if ($request->status_id == OrderStatus::MANUAL->value) {
                $updateData['created_by_user_id'] = auth()->id();
            }

            // Si se marca como pagado y no tiene fecha de pago, agregarla
            if ($request->status_id == OrderStatus::PAID->value && ! $order->paid_at) {
                $updateData['paid_at'] = now();
            }

            // Si se cambia de DEMO o DEMO_EXPIRED a PAGADO, limpiar fecha de expiración
            if ($request->status_id == OrderStatus::PAID->value && 
                in_array($order->status_id, [OrderStatus::DEMO->value, OrderStatus::DEMO_EXPIRED->value])) {
                $updateData['demo_expires_at'] = null;
            }

            $order->update($updateData);

            return response()->json([
                'order' => $order->fresh(['items', 'user']),
                'message' => 'Estado actualizado exitosamente',
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Eliminar un item de orden (para compras WATI)
     */
    public function deleteItem(string $id): JsonResponse
    {
        try {
            $orderItem = \App\Models\OrderItem::findOrFail($id);
            $orderId = $orderItem->order_id;

            // Eliminar el item
            $orderItem->delete();

            // Verificar si la orden quedó sin items
            $order = Order::with('items')->find($orderId);
            
            if ($order && $order->items->count() === 0) {
                // Si no quedan items, eliminar la orden también
                $order->delete();
            }

            return response()->json([
                'success' => true,
                'message' => 'Item eliminado exitosamente',
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Actualizar una orden demo a pagada
     */
    public function upgradeDemoToPaid(string $id): JsonResponse
    {
        try {
            $order = Order::findOrFail($id);

            if (!in_array($order->status_id, [OrderStatus::DEMO->value, OrderStatus::DEMO_EXPIRED->value])) {
                return response()->json([
                    'success' => false,
                    'message' => 'La orden no es un demo o demo expirado',
                ], Response::HTTP_BAD_REQUEST);
            }

            $order->update([
                'status_id' => OrderStatus::PAID->value,
                'paid_at' => now(),
                'demo_expires_at' => null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Demo actualizado a pagado exitosamente',
                'order' => $order,
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
}
