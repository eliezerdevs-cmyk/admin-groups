<table>
    <thead>
    <tr>
        <th colspan="7" style="font-weight: bold; font-size: 14pt; text-align: center;">REPORTE GENERAL DE CAMPO</th>
    </tr>
    <tr>
        <th colspan="7" style="text-align: center;">Período del {{ $startDate }} al {{ $endDate }}</th>
    </tr>
    <tr></tr>
    </thead>
    <tbody>
    @foreach($reports as $report)
        <tr style="background-color: #e2e8f0;">
            <td colspan="7" style="font-weight: bold;">
                Grupo: {{ $report->group->name ?? 'N/A' }} |
                Fecha: {{ \Carbon\Carbon::parse($report->date)->format('d-m-Y') }} |
                Responsable: {{ $report->user->name ?? 'N/A' }}
            </td>
        </tr>
        <tr>
            <th style="font-weight: bold; background-color: #f1f5f9;">Calle Inicial</th>
            <th style="font-weight: bold; background-color: #f1f5f9;">Calle Final</th>
            <th style="font-weight: bold; background-color: #f1f5f9;">Visitadas</th>
            <th style="font-weight: bold; background-color: #f1f5f9;">Abiertas</th>
            <th style="font-weight: bold; background-color: #f1f5f9;">No Abiertas</th>
            <th style="font-weight: bold; background-color: #f1f5f9;">Cand. Bautismo</th>
            <th style="font-weight: bold; background-color: #f1f5f9;">Calles Recorridas</th>
        </tr>
        <tr>
            <td>{{ $report->street_start }}</td>
            <td>{{ $report->street_end }}</td>
            <td>{{ $report->houses_visited }}</td>
            <td>{{ $report->houses_opened }}</td>
            <td>{{ $report->houses_didnotopen }}</td>
            <td>{{ $report->candidates_baptism }}</td>
            <td>{{ $report->streets_traveled }}</td>
        </tr>

        @if($report->interestedPeople && $report->interestedPeople->count() > 0)
            <tr>
                <td colspan="7" style="font-weight: bold; color: #1e3a8a;">Aspirantes / Personas Interesadas:</td>
            </tr>
            <tr>
                <th style="font-weight: bold; background-color: #cbd5e1;">Nombre</th>
                <th style="font-weight: bold; background-color: #cbd5e1;">Apellido</th>
                <th style="font-weight: bold; background-color: #cbd5e1;">Colonia</th>
                <th style="font-weight: bold; background-color: #cbd5e1;">Dirección</th>
                <th style="font-weight: bold; background-color: #cbd5e1;">Tema</th>
                <th style="font-weight: bold; background-color: #cbd5e1;">Recibimiento</th>
                <th style="font-weight: bold; background-color: #cbd5e1;">Notas</th>
            </tr>
            @foreach($report->interestedPeople as $person)
                <tr>
                    <td>{{ $person->name }}</td>
                    <td>{{ $person->last_name ?? '' }}</td>
                    <td>{{ $person->colony }}</td>
                    <td>{{ $person->address ?? '' }}</td>
                    <td>{{ $person->theme }}</td>
                    <td>{{ ucfirst($person->status) }}</td>
                    <td>{{ $person->notes ?? '' }}</td>
                </tr>
            @endforeach
        @endif
        <tr></tr>
    @endforeach
    </tbody>
</table>
