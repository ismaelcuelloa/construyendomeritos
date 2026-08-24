<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function checkout(Request $request)
    {

        $items = $request->input('items');
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|integer|exists:courses,id',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        try {
            $response = app(OrderService::class)->createFromCart($items);

            return response()->json($response);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }

    }

    public function myPurchases()
    {
        return Inertia::render('Purchases/Index');
    }

    public function myPurchasesList(Request $request)
    {
        try {
            $user = auth()->user();

            $purchases = Order::query()
                ->with(['items.course'])
                ->where('user_id', $user->id)
                ->where('status_id', OrderStatus::PAID->value)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'purchases' => $purchases,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener compras',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
