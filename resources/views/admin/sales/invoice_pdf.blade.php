<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comprobante de Pago - {{ $sale->customer_name ?: 'Venta Directa' }}</title>
    <style>
        body {
            font-family: 'Helvetica', sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #db2777;
            padding-bottom: 15px;
        }
        .logo {
            font-size: 28px;
            font-weight: bold;
            color: #db2777;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .subtitle {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }
        .invoice-details {
            margin-bottom: 30px;
            display: table;
            width: 100%;
        }
        .column {
            display: table-cell;
            width: 50%;
        }
        .label {
            font-size: 10px;
            font-weight: bold;
            color: #999;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        .value {
            font-size: 14px;
            margin-bottom: 15px;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
        }
        th {
            background-color: #fce7f3;
            color: #db2777;
            text-align: left;
            padding: 10px;
            font-size: 12px;
            text-transform: uppercase;
        }
        td {
            padding: 12px 10px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 14px;
        }
        .total-section {
            text-align: right;
            margin-top: 20px;
        }
        .total-label {
            font-size: 16px;
            font-weight: bold;
            color: #666;
        }
        .total-amount {
            font-size: 24px;
            font-weight: bold;
            color: #db2777;
            margin-left: 10px;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #999;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        @if(file_exists(public_path('logo.jpg')))
        <img src="data:image/jpeg;base64,{{ base64_encode(file_get_contents(public_path('logo.jpg'))) }}" alt="Stefy Nails Logo" style="height: 120px; width: 120px; object-fit: cover; border-radius: 50%; margin-bottom: 10px;">
        @endif
        <div class="logo">Stefy Nails</div>
        <div class="subtitle">Comprobante de Pago POS</div>
    </div>

    <div class="invoice-details">
        <div class="column">
            <div class="label">Cliente</div>
            <div class="value">{{ $sale->customer_name ?: 'Venta Directa' }}</div>
            
            @if($sale->customer_phone)
            <div class="label">Teléfono</div>
            <div class="value">{{ $sale->customer_phone }}</div>
            @endif
        </div>
        <div class="column" style="text-align: right;">
            <div class="label">Folio Venta</div>
            <div class="value">#POS-{{ str_pad($sale->id, 5, '0', STR_PAD_LEFT) }}</div>
            
            <div class="label">Fecha</div>
            <div class="value">{{ $sale->created_at->format('d/m/Y h:i A') }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Cant.</th>
                <th>Producto</th>
                <th style="text-align: right;">Unit.</th>
                <th style="text-align: right;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $item)
            <tr>
                <td style="width: 40px;">{{ $item->quantity }}</td>
                <td>{{ $item->product->name }}</td>
                <td style="text-align: right;">${{ number_format($item->unit_price, 0, ',', '.') }}</td>
                <td style="text-align: right;">${{ number_format($item->subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-section">
        <div style="margin-bottom: 20px;">
            <div style="margin-bottom: 5px;">
                <span class="label" style="display: inline-block; width: 140px; text-align: right;">Método de Pago:</span>
                <span class="value" style="display: inline-block; width: 100px; text-align: right; margin-bottom: 0;">
                    @php
                        $methodMap = ['cash' => 'Efectivo', 'transfer' => 'Transferencia', 'hybrid' => 'Híbrido'];
                        echo $methodMap[$sale->payment_method] ?? $sale->payment_method;
                    @endphp
                </span>
            </div>
            @if($sale->payment_method === 'hybrid')
            <div style="margin-bottom: 5px; font-size: 11px; color: #666;">
                <span style="display: inline-block; width: 140px; text-align: right;">Efectivo:</span>
                <span style="display: inline-block; width: 100px; text-align: right;">${{ number_format($sale->cash_amount, 0, ',', '.') }}</span>
            </div>
            <div style="margin-bottom: 5px; font-size: 11px; color: #666;">
                <span style="display: inline-block; width: 140px; text-align: right;">Transferencia:</span>
                <span style="display: inline-block; width: 100px; text-align: right;">${{ number_format($sale->transfer_amount, 0, ',', '.') }}</span>
            </div>
            @endif
        </div>
        
        <span class="total-label">Total Pagado:</span>
        <span class="total-amount">${{ number_format($sale->total, 0, ',', '.') }}</span>
    </div>

    <div class="footer">
        <p>¡Gracias por su compra en Stefy Nails!</p>
        <p style="font-size: 10px;">Comprobante digital sin efectos fiscales. Generado automáticamente por el POS.</p>
    </div>
</body>
</html>
