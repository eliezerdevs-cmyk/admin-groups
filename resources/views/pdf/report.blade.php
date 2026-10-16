<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte-{{ $report->group->name }}-{{ \Carbon\Carbon::parse($report->date)->format('d-M-Y') }}</title>
    <style>
    @page {
        margin-top: 155px;    /* Espacio superior reservado para el membrete */
        margin-bottom: 60px;
    }

    body {
        margin: 0px;
        padding: 0px;
        font-family: sans-serif;
        font-size: 11pt;
        color: #333;
    }

    /* Imagen de fondo abarcando toda la hoja */
    .bg-membrete {
        position: fixed;
        top: -155px;   /* Compensación del margin-top de @page */
        left: -45px;   /* Compensación del margin-left de @page */
        
        /* Dimensiones exactas del papel Carta (Letter: 816px x 1056px) */
        width: 816px;
        height: 1056px;
        z-index: -1000;
    }

    /* Contenedor central simétrico */
    .content {
        /* Margen izquierdo y derecho iguales para centrar perfectamente */
        padding-left: 118px;
        padding-right: 118px;
        
        padding-bottom: 50px;
    }
    /* Evitar que una sección de reporte o tabla se rompa feo entre páginas */
    .report-section {
        page-break-inside: avoid;
        margin-bottom: 25px;
        border-bottom: 1px solid #ddd;
        padding-bottom: 15px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
    }

    th, td {
        border: 1px solid #ccc;
        padding: 6px 8px;
        text-align: left;
    }

    th {
        background-color: #f2f2f2;
    }
</style>
</head>
<body>

    <!-- Imagen en Base64 -->
    @if(!empty($imageBase64))
        <img src="{{ $imageBase64 }}" class="bg-membrete" alt="Membrete">
    @endif

    <div class="content">
        <h2>Reporte de Grupo: {{ $report->group->name ?? 'N/A' }}</h2>
        @if($report->group->name == "Grupo de Obreros")
            <h2>Día de guardia: {{ \App\Enums\GuardDay::from($report->day)->label() }}</h2>
        @endif
        <p><strong>Responsable:</strong> {{ $report->user->name ?? 'N/A' }}</p>
        <!-- Formato: 06 de octubre de 2026 -->
        <p><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($report->date)->locale('es')->isoFormat('DD [de] MMMM [de] YYYY') }}</p>
        <p><strong>Calles recorridas:</strong> {{ $report->streets_traveled }}</p>

        <table>
            <thead>
                <tr>
                    <th>Hogares Visitadas</th>
                    <th>Hogares Abiertas</th>
                    <th>No Abiertas</th>
                    <th>Candidatos Bautismo</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $report->houses_visited }}</td>
                    <td>{{ $report->houses_opened }}</td>
                    <td>{{ $report->houses_didnotopen }}</td>
                    <td>{{ $report->candidates_baptism }}</td>
                </tr>
            </tbody>
        </table>

        <h3>Aspirantes / Personas Interesadas</h3>
        @if($report->interestedPeople && count($report->interestedPeople) > 0)
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Colonia</th>
                        <th>Tema</th>
                        <th>Recibimiento</th>
                        <th>¿Es candidato?</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($report->interestedPeople as $person)
                        <tr>
                            <td>{{ $person->name ?? $person['name'] }} {{ $person->last_name ?? $person['last_name'] ?? '' }}</td>
                            <td>{{ $person->colony ?? $person['colony'] }}</td>
                            <td>{{ $person->theme ?? $person['theme'] }}</td>
                            <td>{{ ucfirst($person->status ?? $person['status'] ?? 'Regular') }}</td>
                            <td>{{ $person->is_candidate ? 'Sí' : 'No' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>No se registraron aspirantes.</p>
        @endif
    </div>

</body>
</html>