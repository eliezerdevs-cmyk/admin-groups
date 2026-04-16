<?php

namespace App\Filament\Dashboard\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use App\Models\Group;
use App\Enums\GuardDay;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable(),

                TextColumn::make('last_name')
                    ->label('Apellido')
                    ->searchable(),

                TextColumn::make('second_last_name')
                    ->label('Segundo apellido')
                    ->searchable(),

                TextColumn::make('guard_day')
                    ->label('Día de Guardia')
                    ->formatStateUsing(fn (string $state): string => GuardDay::tryFrom($state)?->label() ?? $state)
                    ->searchable(),

                TextColumn::make('groups.name')
                    ->label('Grupo(s)')
                    ->badge()
                    ->separator(',')
                    ->placeholder('Sin grupo')
                    ->searchable(),

                TextColumn::make('deleted_at')
                    ->label('Eliminado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('groups')
                    ->label('Grupo')
                    ->relationship('groups', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable(),

                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
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
