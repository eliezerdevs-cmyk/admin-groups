<?php

namespace App\Filament\Dashboard\Resources\Reports\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use App\Models\Group;
use Livewire\Component as Livewire;

class ReportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->headerActions([
                Action::make('generalExcelReport')
                    ->label('Generar Reporte Excel')
                    ->icon('heroicon-o-document-text')
                    ->color('success')
                    ->schema([
                        Select::make('group_id')
                            ->label('Grupo (Opcional)')
                            ->options(Group::pluck('name', 'id'))
                            ->searchable()
                            ->placeholder('Todos los grupos')
                            ->nullable(),

                        DatePicker::make('start_date')
                            ->label('Fecha de Inicio')
                            ->required()
                            ->default(now()->startOfMonth()),

                        DatePicker::make('end_date')
                            ->label('Fecha de Fin')
                            ->required()
                            ->default(now()->endOfMonth()),
                    ])
                    ->action(function (array $data) {
                        return redirect()->route('reports.general.excel', array_filter([
                            'group_id' => $data['group_id'] ?? null,
                            'start_date' => $data['start_date'],
                            'end_date' => $data['end_date'],
                        ]));
                    })
                    ->openUrlInNewTab(),
                Action::make('generalPdfReport')
                    ->label('Generar Reporte General (PDF)')
                    ->icon('heroicon-o-document-chart-bar')
                    ->color('warning')
                    ->schema([
                        Select::make('group_id')
                            ->label('Grupo (Opcional)')
                            ->options(Group::pluck('name', 'id'))
                            ->searchable()
                            ->placeholder('Todos los grupos')
                            ->nullable(),

                        DatePicker::make('start_date')
                            ->label('Fecha de Inicio')
                            ->required()
                            ->default(now()->startOfMonth()),

                        DatePicker::make('end_date')
                            ->label('Fecha de Fin')
                            ->required()
                            ->default(now()->endOfMonth()),
                    ])
                    ->action(function (array $data, Livewire $livewire) {
                        $url = route('reports.general.pdf', array_filter([
                            'group_id' => $data['group_id'] ?? null,
                            'start_date' => $data['start_date'],
                            'end_date' => $data['end_date'],
                        ]));
                        return $livewire->js("window.open('{$url}', '_blank')");
                    }),
            ])
            ->columns([
                TextColumn::make('group.name')
                    ->label('Grupo')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Creador')
                    ->sortable(),
                TextColumn::make('date')
                    ->label('Fecha del Reporte')
                    ->date()
                    ->formatStateUsing(fn(string $state): string => Carbon::parse($state)->format('d-M-Y'))
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Fecha de creación')
                    ->date()
                    ->formatStateUsing(fn(string $state): string => Carbon::parse($state)->format('d-M-Y'))
                    ->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('downloadPdf')
                    ->label('Descargar')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->url(fn($record): string => route('reports.pdf', $record))
                    ->openUrlInNewTab(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
