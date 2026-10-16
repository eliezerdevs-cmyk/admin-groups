<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Reporte General Consolidado</title>
<style>
    @page {
        size: letter;          /* 816 x 1056 px @96dpi */
        margin: 0;             /* sin margen: la imagen cubre TODA la hoja */
    }

    html, body {
        margin: 0;
        padding: 0;
        font-family: sans-serif;
        font-size: 10pt;
        color: #333;
    }

    /* -------- Membrete: capa de fondo de TODA la hoja -------- */
    .bg-membrete {
        position: fixed;
        top: 0;
        left: 0;
        width: 816px;          /* ancho exacto de Letter */
        height: 1056px;        /* alto exacto de Letter */
        z-index: 0;            /* NO usar negativo */
    }

    /* -------- Contenido: se pinta por encima -------- */
    .content {
        position: relative;
        z-index: 1;
        /* top | right | bottom | left
           Ajusta 155/100/80/100 a lo que mida tu diseño real */
        padding: 155px 100px 80px 100px;
    }

    .report-section {
        page-break-inside: avoid;
        margin-bottom: 25px;
        border-bottom: 1px solid #ddd;
        padding-bottom: 15px;
    }

    h2 {
        text-align: center;
        color: #1a365d;
        margin-bottom: 5px;
    }

    .subtitle {
        text-align: center;
        font-size: 9pt;
        color: #555;
        margin-bottom: 20px;
    }

    .report-header {
        background-color: #1c4587;
        padding: 6px 10px;
        font-weight: bold;
        font-size: 10pt;
        margin-bottom: 8px;
        color: #fff;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 8px;
        margin-bottom: 10px;
    }

    th, td {
        border: 1px solid #cbd5e1;
        padding: 5px 7px;
        text-align: left;
        font-size: 9pt;
    }

    th {
        background-color: #1c4587;
        color: #fff;
    }
</style>
</head>

<body>

    @if(!empty($imageBase64))
        {{-- Fondo que se repite en cada página --}}
        <img src="{{ $imageBase64 }}" class="bg-membrete" alt="">
    @endif

    <div class="content">
        <h2>Reporte General</h2>
        <div class="subtitle">
            <strong>Grupo:</strong> {{ $selectedGroupName }} |
            <strong>Período:</strong> {{ $startDate }} al {{ $endDate }}
        </div>

        @forelse($reports as $report)
            <div class="report-section">
                <div class="report-header">
                    Grupo: {{ $report->group->name ?? 'N/A' }} |
                    Fecha: {{ \Carbon\Carbon::parse($report->date)->format('d-m-Y') }} |
                    Responsable: {{ $report->user->name ?? 'N/A' }}
                </div>

                <table>
                    <tr>
                        <th>Calle inicio</th>
                        <th>Calle fin</th>
                        <th>Hogares Visitadas</th>
                        <th>Hogares Abiertas</th>
                        <th>No Abiertas</th>
                        <th>Aspirantes al Bautismo</th>
                    </tr>
                    <tr>
                        <td>{{ $report->street_start }}</td>
                        <td>{{ $report->street_end }}</td>
                        <td>{{ $report->houses_visited }}</td>
                        <td>{{ $report->houses_opened }}</td>
                        <td>{{ $report->houses_didnotopen }}</td>
                        <td>{{ $report->candidates_baptism }}</td>
                    </tr>
                </table>

                <table>
                    <tr><th>Calles recorridas</th></tr>
                    <tr><td>{{ $report->streets_traveled }}</td></tr>
                </table>

                @if($report->interestedPeople && $report->interestedPeople->count() > 0)
                    <div style="font-weight:bold;font-size:9pt;margin-top:5px;">
                        Aspirantes / Personas Interesadas:
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Colonia</th>
                                <th>Tema</th>
                                <th>Recibimiento</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($report->interestedPeople as $person)
                                <tr>
                                    <td>{{ $person->name }} {{ $person->last_name ?? '' }}</td>
                                    <td>{{ $person->colony }}</td>
                                    <td>{{ $person->theme }}</td>
                                    <td>{{ ucfirst($person->status) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div style="font-size:8.5pt;color:#666;font-style:italic;">
                        No se registraron aspirantes en este reporte.
                    </div>
                @endif
            </div>
        @empty
            <p style="text-align:center;color:#666;">
                No se encontraron reportes en el rango de fechas seleccionado.
            </p>
        @endforelse
    </div>

</body>
</html>