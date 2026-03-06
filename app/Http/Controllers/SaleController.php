<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function pos()
    {
        $products = Product::where('stock', '>', 0)->orderBy('name')->get();
        return view('admin.sales.pos', compact('products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'payment_method' => 'required|in:cash,transfer,hybrid',
            'cash_amount' => 'nullable|numeric|min:0',
            'transfer_amount' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        return DB::transaction(function () use ($validated) {
            $total = 0;
            $saleItemsData = [];

            foreach ($validated['items'] as $itemData) {
                $product = Product::lockForUpdate()->find($itemData['product_id']);

                if ($product->stock < $itemData['quantity']) {
                    throw new \Exception("Stock insuficiente para el producto: {$product->name}");
                }

                $subtotal = $product->price * $itemData['quantity'];
                $total += $subtotal;

                $saleItemsData[] = [
                    'product_id' => $product->id,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $product->price,
                    'subtotal' => $subtotal,
                ];

                $product->decrement('stock', $itemData['quantity']);
            }

            $sale = Sale::create([
                'user_id' => auth()->id(),
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'total' => $total,
                'payment_method' => $validated['payment_method'],
                'cash_amount' => $validated['payment_method'] === 'cash' ? $total : ($validated['payment_method'] === 'transfer' ? 0 : $validated['cash_amount']),
                'transfer_amount' => $validated['payment_method'] === 'transfer' ? $total : ($validated['payment_method'] === 'cash' ? 0 : $validated['transfer_amount']),
            ]);

            foreach ($saleItemsData as $data) {
                $data['sale_id'] = $sale->id;
                SaleItem::create($data);
            }

            return response()->json([
                'success' => true,
                'message' => 'Venta realizada con éxito',
                'sale_id' => $sale->id
            ]);
        });
    }

    public function index()
    {
        $posSales = Sale::with('items.product')->latest()->paginate(15, ['*'], 'pos_page');
        
        $appointmentSales = \App\Models\Appointment::where('status', 'completed')
            ->whereHas('products')
            ->with(['products', 'service'])
            ->latest()
            ->paginate(15, ['*'], 'app_page');

        return view('admin.sales.index', compact('posSales', 'appointmentSales'));
    }
}
