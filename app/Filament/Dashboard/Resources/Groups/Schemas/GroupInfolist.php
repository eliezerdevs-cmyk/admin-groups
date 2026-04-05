<?php

namespace App\Filament\Dashboard\Resources\Groups\Schemas;

use App\Models\Group;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class GroupInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('description')
                    ->placeholder('-'),
                IconEntry::make('active')
                    ->boolean(),
                IconEntry::make('requires_guard_day')
                    ->boolean(),
                TextEntry::make('admin_user_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('aux_user_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Group $record): bool => $record->trashed()),
            ]);
    }
}
