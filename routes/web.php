<?php

use App\Models\Report;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Group;
use App\Exports\ReportsExport;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/private-file/{path}', function (string $path) {
    if (! Storage::disk('private')->exists($path)) {
        abort(404);
    }
    return Storage::disk('private')->response($path);
})->where('path', '.*')->middleware(Authenticate::class)->name('private.file');

// 1. RUTA GENERAL PRIMERO (para evitar que se confunda con {report})
Route::get('/reports/general/pdf', function (Request $request) {
    $startDate = $request->input('start_date');
    $endDate = $request->input('end_date');
    $groupId = $request->input('group_id');

    // 1. Cargar la imagen del membrete
    $imagePath = storage_path('app/private/images/hoja_membretada.jpg');
    $imageBase64 = '';

    if (file_exists($imagePath)) {
        $imageType = pathinfo($imagePath, PATHINFO_EXTENSION);
        $imageContent = file_get_contents($imagePath);
        $imageBase64 = 'data:image/' . $imageType . ';base64,' . base64_encode($imageContent);
    }

    // 2. Consulta con filtro opcional de grupo
    $query = Report::with(['group', 'user', 'interestedPeople'])
        ->whereBetween('date', [$startDate, $endDate]);

    $selectedGroupName = 'Todos los Grupos';

    if ($groupId) {
        $query->where('group_id', $groupId);
        $group = Group::find($groupId);
        if ($group) {
            $selectedGroupName = $group->name;
        }
    }

    $reports = $query->orderBy('date', 'asc')->get();

    // 3. Renderizar vista PDF
    $pdf = Pdf::loadView('pdf.general-report', [
        'reports' => $reports,
        'selectedGroupName' => $selectedGroupName,
        'startDate' => Carbon::parse($startDate)->locale('es')->isoFormat('DD [de] MMMM [de] YYYY'),
        'endDate' => Carbon::parse($endDate)->locale('es')->isoFormat('DD [de] MMMM [de] YYYY'),
        'imageBase64' => $imageBase64,
    ])->setOption(['isRemoteEnabled' => true]);

    $groupSlug = $groupId ? Str::slug($selectedGroupName) : 'Todos_los_Grupos';
    $filename = "Reporte_General_{$groupSlug}_{$startDate}_al_{$endDate}.pdf";

    return $pdf->stream($filename);

})->name('reports.general.pdf')->middleware(['auth']);

// 2. RUTA INDIVIDUAL DESPUÉS
Route::get('/reports/{report}/pdf', function (Report $report) {
    $imagePath = storage_path('app/private/images/hoja_membretada.jpg');
    $imageBase64 = '';

    if (file_exists($imagePath)) {
        $imageType = pathinfo($imagePath, PATHINFO_EXTENSION);
        $imageContent = file_get_contents($imagePath);
        $imageBase64 = 'data:image/' . $imageType . ';base64,' . base64_encode($imageContent);
    }

    $report->load(['group', 'user', 'interestedPeople']);

    $pdf = Pdf::loadView('pdf.report', [
        'report' => $report,
        'imageBase64' => $imageBase64,
    ])->setOption(['isRemoteEnabled' => true]);

    $groupName = Str::slug($report->group->name ?? 'Sin-Grupo');
    $formattedDate = Carbon::parse($report->date)->format('d-M-Y');
    $fileName = "Reporte_{$groupName}_{$formattedDate}.pdf";

    return $pdf->stream($fileName);
})->name('reports.pdf')->middleware(['auth']);

Route::get('/reports/general/excel', function (Request $request) {
    $startDate = $request->input('start_date');
    $endDate = $request->input('end_date');
    $groupId = $request->input('group_id');

    $groupName = 'Todos_los_Grupos';
    if ($groupId) {
        $group = Group::find($groupId);
        if ($group) {
            $groupName = Str::slug($group->name);
        }
    }

    $filename = "Reporte_General_{$groupName}_{$startDate}_al_{$endDate}.xlsx";

    return Excel::download(new ReportsExport($startDate, $endDate, $groupId), $filename);
})->name('reports.general.excel')->middleware(['auth']);
