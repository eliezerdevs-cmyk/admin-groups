<?php

namespace App\Filament\Dashboard\Resources\Groups;

use App\Filament\Dashboard\Resources\Groups\Pages\CreateGroup;
use App\Filament\Dashboard\Resources\Groups\Pages\EditGroup;
use App\Filament\Dashboard\Resources\Groups\Pages\ListGroups;
use App\Filament\Dashboard\Resources\Groups\Pages\ViewGroup;
use App\Filament\Dashboard\Resources\Groups\Schemas\GroupForm;
use App\Filament\Dashboard\Resources\Groups\Schemas\GroupInfolist;
use App\Filament\Dashboard\Resources\Groups\Tables\GroupsTable;
use App\Models\Group;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class GroupResource extends Resource
{
    protected static ?string $model = Group::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $modelLabel = 'Grupo';

    protected static ?string $pluralModelLabel = 'Grupos';

    protected static ?string $navigationLabel = 'Grupos';

    protected static ?string $slug = 'grupos';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return GroupForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return GroupInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GroupsTable::configure($table);
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
            'index' => ListGroups::route('/'),
            'create' => CreateGroup::route('/create'),
            'view' => ViewGroup::route('/{record}'),
            'edit' => EditGroup::route('/{record}/edit'),
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
