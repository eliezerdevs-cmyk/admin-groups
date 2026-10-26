<?php

namespace App\Filament\Dashboard\Resources\Activities;

use App\Filament\Dashboard\Resources\Activities\Pages\CreateActivity;
use App\Filament\Dashboard\Resources\Activities\Pages\EditActivity;
use App\Filament\Dashboard\Resources\Activities\Pages\ListActivities;
use App\Filament\Dashboard\Resources\Activities\Pages\ViewActivity;
use App\Filament\Dashboard\Resources\Activities\Schemas\ActivityForm;
use App\Filament\Dashboard\Resources\Activities\Schemas\ActivityInfolist;
use App\Filament\Dashboard\Resources\Activities\Tables\ActivitiesTable;
use App\Models\Activity;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Actividades';

    protected static ?string $modelLabel = 'Actividad';

    protected static ?string $pluralModelLabel = 'Actividades';

    protected static ?string $slug = 'actividades';

    protected static ?string $recordTitleAttribute = 'Activity';

    public static function form(Schema $schema): Schema
    {
        return ActivityForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ActivityInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ActivitiesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActivities::route('/'),
            'create' => CreateActivity::route('/create'),
            'view' => ViewActivity::route('/{record}'),
            'edit' => EditActivity::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
