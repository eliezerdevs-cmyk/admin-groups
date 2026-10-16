<?php

namespace App\Exports;

use App\Models\Report;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Carbon\Carbon;

class ReportsExport implements FromView, ShouldAutoSize
{
    protected string $startDate;
    protected string $endDate;
    protected ?string $groupId;

    public function __construct(string $startDate, string $endDate, ?string $groupId = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->groupId = $groupId;
    }

    public function view(): View
    {
        $query = Report::with(['group', 'user', 'interestedPeople'])
            ->whereBetween('date', [$this->startDate, $this->endDate]);

        if ($this->groupId) {
            $query->where('group_id', $this->groupId);
        }

        $reports = $query->orderBy('date', 'asc')->get();

        return view('exports.reports-excel', [
            'reports' => $reports,
            'startDate' => Carbon::parse($this->startDate)->locale('es')->isoFormat('DD [de] MMMM [de] YYYY'),
            'endDate' => Carbon::parse($this->endDate)->locale('es')->isoFormat('DD [de] MMMM [de] YYYY'),
        ]);
    }
}
