<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Financiero - Stefy Nails</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Helvetica', sans-serif;
            color: #1f2937;
            font-size: 11px;
            padding: 30px;
        }
        /* Header */
        .header {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 18px;
            border-bottom: 3px solid #db2777;
        }
        .logo-text {
            color: #db2777;
            font-size: 28px;
            font-weight: 900;
            letter-spacing: 3px;
            margin-bottom: 3px;
        }
        .report-subtitle {
            font-size: 10px;
            letter-spacing: 4px;
            text-transform: uppercase;
            color: #6b7280;
        }
        .report-period {
            margin-top: 8px;
            display: inline-block;
            background: #fdf2f8;
            border: 1px solid #fbcfe8;
            color: #9d174d;
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: bold;
            letter-spacing: 1px;
        }
        /* Summary Cards */
        .summary {
            width: 100%;
            margin-bottom: 25px;
            border-collapse: separate;
            border-spacing: 10px;
        }
        .card {
            text-align: center;
            padding: 14px 10px;
            border-radius: 10px;
        }
        .card-label {
            font-size: 9px;
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }
        .card-value {
            font-size: 20px;
            font-weight: 900;
        }
        .card-green  { background: #f0fdf4; border: 1px solid #bbf7d0; }
        .card-green .card-label { color: #15803d; }
        .card-green .card-value { color: #15803d; }
        .card-red    { background: #fff1f2; border: 1px solid #fecdd3; }
        .card-red .card-label { color: #be123c; }
        .card-red .card-value { color: #be123c; }
        .card-pink   { background: #db2777; }
        .card-pink .card-label { color: rgba(255,255,255,0.85); }
        .card-pink .card-value { color: #fff; }
        /* Section Headers */
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #1f2937;
            border-left: 4px solid #db2777;
            padding-left: 10px;
            margin: 20px 0 10px;
        }
        .section-subtitle {
            font-size: 9px;
            color: #9ca3af;
            padding-left: 14px;
            margin-top: -8px;
            margin-bottom: 10px;
        }
        /* Tables */
        table.detail {
            width: 100%;
            border-collapse: collapse;
        }
        table.detail thead tr {
            background: #f9fafb;
        }
        table.detail th {
            padding: 9px 10px;
            border-bottom: 2px solid #e5e7eb;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6b7280;
            font-weight: bold;
            text-align: left;
        }
        table.detail td {
            padding: 9px 10px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 10px;
        }
        table.detail tbody tr:nth-child(even) { background: #fafafa; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .amount-green { color: #16a34a; font-weight: bold; }
        .amount-red   { color: #dc2626; font-weight: bold; }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 9px;
            font-weight: bold;
        }
        .badge-gray { background: #f3f4f6; color: #6b7280; }
        /* Totals row */
        .totals-row td {
            border-top: 2px solid #e5e7eb;
            border-bottom: none;
            font-weight: bold;
            font-size: 11px;
            padding-top: 11px;
        }
        /* Footer */
        .footer {
            position: fixed;
            bottom: 15px;
            left: 0; right: 0;
            text-align: center;
            font-size: 9px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            padding-top: 8px;
        }
        /* Divider */
        .divider {
            border: none;
            border-top: 1px solid #e5e7eb;
            margin: 20px 0;
        }
        .empty-row td {
            text-align: center;
            color: #d1d5db;
            padding: 18px;
            font-style: italic;
        }
    </style>
</head>
<body>
    {{-- HEADER --}}
    <div class="header">
        <div class="logo-text">✦ STEFY NAILS ✦</div>
        <div class="report-subtitle">Reporte de Movimientos Financieros</div>
        <div class="report-period">
            Periodo: {{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d/m/Y') : 'Inicio' }}
            —
            {{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d/m/Y') : 'Hoy (' . now()->format('d/m/Y') . ')' }}
        </div>
    </div>

    {{-- SUMMARY CARDS --}}
    @php
        $margin = $grossRevenue > 0 ? round(($netProfit / $grossRevenue) * 100, 1) : 0;
        $totalServices = $completedAppointments->count();
        $avgTicket = $totalServices > 0 ? $grossRevenue / $totalServices : 0;
    @endphp
    <table class="summary">
        <tr>
            <td style="width:25%; border:none; padding:5px;">
                <div class="card card-green">
                    <div class="card-label">💰 Ingresos Brutos</div>
                    <div class="card-value">${{ number_format($grossRevenue, 0, ',', '.') }}</div>
                </div>
            </td>
            <td style="width:25%; border:none; padding:5px;">
                <div class="card card-red">
                    <div class="card-label">📤 Gastos Totales</div>
                    <div class="card-value">${{ number_format($totalExpenses, 0, ',', '.') }}</div>
                </div>
            </td>
            <td style="width:25%; border:none; padding:5px;">
                <div class="card card-pink">
                    <div class="card-label">📈 Utilidad Neta</div>
                    <div class="card-value">${{ number_format($netProfit, 0, ',', '.') }}</div>
                </div>
            </td>
            <td style="width:25%; border:none; padding:5px;">
                <div class="card" style="background:#f8fafc; border:1px solid #e2e8f0;">
                    <div class="card-label" style="color:#475569;">🎯 Margen Neto</div>
                    <div class="card-value" style="color:#0f172a;">{{ $margin }}%</div>
                </div>
            </td>
        </tr>
    </table>

    {{-- QUICK STATS --}}
    <table style="width:100%; border-collapse:collapse; margin-bottom:20px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px;">
        <tr>
            <td style="border:none; padding:10px 16px; text-align:center; border-right:1px solid #e2e8f0;">
                <div style="font-size:9px; color:#6b7280; text-transform:uppercase; font-weight:bold;">Servicios Realizados</div>
                <div style="font-size:18px; font-weight:900; color:#1f2937;">{{ $totalServices }}</div>
            </td>
            <td style="border:none; padding:10px 16px; text-align:center; border-right:1px solid #e2e8f0;">
                <div style="font-size:9px; color:#6b7280; text-transform:uppercase; font-weight:bold;">Ticket Promedio</div>
                <div style="font-size:18px; font-weight:900; color:#1f2937;">${{ number_format($avgTicket, 0, ',', '.') }}</div>
            </td>
            <td style="border:none; padding:10px 16px; text-align:center; border-right:1px solid #e2e8f0;">
                <div style="font-size:9px; color:#6b7280; text-transform:uppercase; font-weight:bold;">N° de Gastos</div>
                <div style="font-size:18px; font-weight:900; color:#1f2937;">{{ $expenses->count() }}</div>
            </td>
            <td style="border:none; padding:10px 16px; text-align:center;">
                <div style="font-size:9px; color:#6b7280; text-transform:uppercase; font-weight:bold;">Generado</div>
                <div style="font-size:13px; font-weight:700; color:#1f2937;">{{ now()->format('d/m/Y H:i') }}</div>
            </td>
        </tr>
    </table>

    {{-- INCOME TABLE --}}
    <div class="section-title">INGRESOS — Servicios Completados</div>
    <div class="section-subtitle">Citas con estado Completado en el periodo seleccionado</div>
    <table class="detail">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Servicio</th>
                <th>Profesional</th>
                <th class="text-right">Monto</th>
            </tr>
        </thead>
        <tbody>
            @forelse($completedAppointments as $appt)
            <tr>
                <td>{{ $appt->appointment_date->format('d/m/Y') }}</td>
                <td><strong>{{ $appt->customer_name }}</strong></td>
                <td>{{ $appt->service?->name ?? 'Servicio personalizado' }}</td>
                <td><span class="badge badge-gray">{{ $appt->professional?->name ?? 'Admin' }}</span></td>
                <td class="text-right amount-green">+${{ number_format($appt->final_price, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr class="empty-row">
                <td colspan="5">No hay servicios completados en este periodo.</td>
            </tr>
            @endforelse
        </tbody>
        @if($completedAppointments->count() > 0)
        <tfoot>
            <tr class="totals-row">
                <td colspan="4">TOTAL INGRESOS ({{ $completedAppointments->count() }} servicios)</td>
                <td class="text-right amount-green">+${{ number_format($grossRevenue, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
        @endif
    </table>

    <hr class="divider">

    {{-- EXPENSE TABLE --}}
    <div class="section-title">EGRESOS — Gastos Registrados</div>
    <div class="section-subtitle">Gastos del periodo seleccionado que impactan la utilidad neta</div>
    <table class="detail">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Descripción del Gasto</th>
                <th class="text-right">Monto</th>
            </tr>
        </thead>
        <tbody>
            @forelse($expenses as $exp)
            <tr>
                <td>{{ $exp->date->format('d/m/Y') }}</td>
                <td>{{ $exp->description }}</td>
                <td class="text-right amount-red">-${{ number_format($exp->amount, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr class="empty-row">
                <td colspan="3">No hay gastos registrados en este periodo.</td>
            </tr>
            @endforelse
        </tbody>
        @if($expenses->count() > 0)
        <tfoot>
            <tr class="totals-row">
                <td colspan="2">TOTAL GASTOS ({{ $expenses->count() }} registros)</td>
                <td class="text-right amount-red">-${{ number_format($totalExpenses, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
        @endif
    </table>

    <hr class="divider">

    {{-- FINAL SUMMARY --}}
    <table style="width:100%; border-collapse:collapse; margin-top:10px;">
        <tr>
            <td style="border:none; padding:6px 0; font-size:11px; color:#6b7280;">Ingresos Brutos</td>
            <td style="border:none; padding:6px 0; font-size:11px; text-align:right; color:#16a34a; font-weight:bold;">+${{ number_format($grossRevenue, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td style="border:none; padding:6px 0; font-size:11px; color:#6b7280;">(-) Gastos Totales</td>
            <td style="border:none; padding:6px 0; font-size:11px; text-align:right; color:#dc2626; font-weight:bold;">-${{ number_format($totalExpenses, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td style="border-top:2px solid #1f2937; padding:10px 0 6px; font-size:14px; font-weight:900; color:#1f2937;">= UTILIDAD NETA</td>
            <td style="border-top:2px solid #1f2937; padding:10px 0 6px; font-size:14px; font-weight:900; text-align:right; color:#db2777;">${{ number_format($netProfit, 0, ',', '.') }}</td>
        </tr>
    </table>

    {{-- FOOTER --}}
    <div class="footer">
        Documento generado por el sistema Stefy Nails CRM &nbsp;|&nbsp; {{ now()->format('d/m/Y h:i A') }} &nbsp;|&nbsp; Confidencial
    </div>
</body>
</html>
