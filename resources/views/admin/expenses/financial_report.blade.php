<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Financiero - Stefy Nails</title>
    <style>
        body {
            font-family: 'Helvetica', sans-serif;
            color: #333;
            font-size: 12px;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #db2777;
            padding-bottom: 15px;
        }
        .logo-text {
            color: #db2777;
            font-size: 24px;
            font-weight: bold;
            margin: 0;
        }
        .report-title {
            text-transform: uppercase;
            letter-spacing: 2px;
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }
        .info-section {
            margin-bottom: 25px;
        }
        .info-grid {
            width: 100%;
            margin-bottom: 20px;
        }
        .info-box {
            background: #fdf2f8;
            border: 1px solid #f9a8d4;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
        .info-label {
            font-size: 10px;
            text-transform: uppercase;
            font-weight: bold;
            color: #9d174d;
            margin-bottom: 5px;
        }
        .info-value {
            font-size: 18px;
            font-weight: bold;
            color: #111;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th {
            background-color: #f3f4f6;
            color: #374151;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10px;
            padding: 10px;
            border-bottom: 2px solid #e5e7eb;
            text-align: left;
        }
        td {
            padding: 10px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 11px;
        }
        .amount-red {
            color: #dc2626;
            font-weight: bold;
        }
        .amount-green {
            color: #16a34a;
            font-weight: bold;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 10px;
            color: #999;
            padding: 10px 0;
            border-top: 1px solid #eee;
        }
        .summary-table {
            width: 100%;
            margin-top: 10px;
        }
        .summary-box {
            padding: 15px;
            border-radius: 10px;
            color: white;
            text-align: center;
        }
        .bg-pink { background-color: #db2777; }
        .bg-gray { background-color: #374151; }
    </style>
</head>
<body>
    <div class="header">
        <p class="logo-text">STEFY NAILS</p>
        <p class="report-title">Reporte de Movimientos Financieros</p>
        <p style="font-size: 10px; color: #888;">Periodo: {{ $startDate ?? 'Inicio' }} - {{ $endDate ?? 'Hoy' }}</p>
    </div>

    <div class="info-section">
        <table class="info-grid">
            <tr>
                <td style="width: 30%; border: none;">
                    <div class="info-box">
                        <div class="info-label">Ingresos Brutos</div>
                        <div class="info-value">${{ number_format($grossRevenue, 0, ',', '.') }}</div>
                    </div>
                </td>
                <td style="width: 30%; border: none;">
                    <div class="info-box">
                        <div class="info-label">Gastos Totales</div>
                        <div class="info-value">${{ number_format($totalExpenses, 0, ',', '.') }}</div>
                    </div>
                </td>
                <td style="width: 40%; border: none;">
                    <div class="summary-box bg-pink">
                        <div style="font-size: 10px; text-transform: uppercase; font-weight: bold; opacity: 0.9;">Utilidad Neta (Ganancia)</div>
                        <div style="font-size: 24px; font-weight: 900;">${{ number_format($netProfit, 0, ',', '.') }}</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <h3 style="font-size: 12px; color: #db2777; border-left: 4px solid #db2777; padding-left: 10px;">Detalle de Egresos (Gastos)</h3>
    <table>
        <thead>
            <tr>
                <th style="width: 20%;">Fecha</th>
                <th style="width: 60%;">Descripción</th>
                <th style="width: 20%; text-align: right;">Monto</th>
            </tr>
        </thead>
        <tbody>
            @forelse($expenses as $expense)
            <tr>
                <td>{{ $expense->date->format('d/m/Y') }}</td>
                <td>{{ $expense->description }}</td>
                <td style="text-align: right;" class="amount-red">-${{ number_format($expense->amount, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="3" style="text-align: center; color: #999;">No hay gastos registrados en este periodo.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Generado el {{ now()->format('d/m/Y h:i A') }} | Stefy Nails CRM
    </div>
</body>
</html>
