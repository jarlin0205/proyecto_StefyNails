<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura - {{ $appointment->customer_name }}</title>
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
        <div class="logo">Stefy Nails</div>
        <div class="subtitle">Especialistas en Belleza y Cuidado</div>
    </div>

    <div class="invoice-details">
        <div class="column">
            <div class="label">Facturado a</div>
            <div class="value">{{ $appointment->customer_name }}</div>
            
            <div class="label">Teléfono</div>
            <div class="value">{{ $appointment->customer_phone }}</div>
        </div>
        <div class="column" style="text-align: right;">
            <div class="label">Folio de Cita</div>
            <div class="value">#{{ str_pad($appointment->id, 5, '0', STR_PAD_LEFT) }}</div>
            
            <div class="label">Fecha del Servicio</div>
            <div class="value">{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d/m/Y') }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Servicio</th>
                <th style="text-align: right;">Precio</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $appointment->service->name }} ({{ $appointment->professional->name }})</td>
                <td style="text-align: right;">${{ number_format($appointment->final_price, 0) }}</td>
            </tr>
        </tbody>
    </table>

    @if($appointment->products->count() > 0)
    <div style="margin-top: -20px; font-size: 10px; color: #db2777; font-weight: bold; text-transform: uppercase;">Productos Adicionales</div>
    <table>
        <thead>
            <tr>
                <th>Cant.</th>
                <th>Producto</th>
                <th style="text-align: right;">Unit.</th>
                <th style="text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($appointment->products as $product)
            <tr>
                <td style="width: 40px;">{{ $product->pivot->quantity }}</td>
                <td>{{ $product->name }}</td>
                <td style="text-align: right;">${{ number_format($product->pivot->unit_price, 0) }}</td>
                <td style="text-align: right;">${{ number_format($product->pivot->quantity * $product->pivot->unit_price, 0) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <div class="total-section">
        <div style="margin-bottom: 20px;">
            <div style="margin-bottom: 5px;">
                <span class="label" style="display: inline-block; width: 140px; text-align: right;">Subtotal Servicio:</span>
                <span style="font-size: 14px; display: inline-block; width: 100px; text-align: right; font-weight: bold;">${{ number_format($appointment->final_price, 0) }}</span>
            </div>
            @if($appointment->products->count() > 0)
            <div style="margin-bottom: 5px;">
                <span class="label" style="display: inline-block; width: 140px; text-align: right;">Subtotal Productos:</span>
                <span style="font-size: 14px; display: inline-block; width: 100px; text-align: right; font-weight: bold;">${{ number_format($appointment->products_total, 0) }}</span>
            </div>
            @endif
            <div style="margin-top: 5px; border-top: 1px solid #eee; padding-top: 5px;">
                <span class="label" style="display: inline-block; width: 140px; text-align: right;">Método de Pago:</span>
                <span class="value" style="display: inline-block; width: 100px; text-align: right; margin-bottom: 0;">
                    @php
                        $methodMap = ['cash' => 'Efectivo', 'transfer' => 'Cuenta', 'hybrid' => 'Híbrido'];
                        echo $methodMap[$appointment->payment_method] ?? 'No especificado';
                    @endphp
                </span>
            </div>
        </div>
        
        <span class="total-label">Total a Pagar:</span>
        <span class="total-amount">${{ number_format($appointment->grand_total, 0) }}</span>
    </div>

    <div class="footer">
        <p>¡Gracias por confiar en Stefy Nails! Su belleza es nuestra pasión.</p>
        <p style="font-size: 10px;">Comprobante digital sin efectos fiscales. Generado automáticamente por el sistema.</p>
    </div>
</body>
</html>
