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
                <th>Profesional</th>
                <th style="text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $appointment->service->name }}</td>
                <td>{{ $appointment->professional->name }}</td>
                <td style="text-align: right;">${{ number_format($appointment->offered_price ?? $appointment->service->price, 0) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="total-section">
        <span class="total-label">Total Pago:</span>
        <span class="total-amount">${{ number_format($appointment->offered_price ?? $appointment->service->price, 0) }}</span>
    </div>

    <div class="footer">
        <p>¡Gracias por confiar en Stefy Nails! Su belleza es nuestra pasión.</p>
        <p style="font-size: 10px;">Comprobante digital sin efectos fiscales. Generado automáticamente por el sistema.</p>
    </div>
</body>
</html>
