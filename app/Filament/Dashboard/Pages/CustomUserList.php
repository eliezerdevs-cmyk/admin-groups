<?php

namespace App\Filament\Dashboard\Pages;

use App\Models\User;
use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;    
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Support\Icons\Heroicon;
use BackedEnum;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use App\Enums\GuardDay;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Columns\IconColumn;
class CustomUserList extends Page implements HasTable
{
    use InteractsWithTable;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUser;
    protected static ?string $navigationLabel = 'Listas'; // Nombre en el menú
    protected static ?string $title = 'Listas';
    protected string $view = 'filament.dashboard.pages.custom-user-list';
    public ?string $activeTab = 'todos';

    public function table(Table $table): Table
    {
        return $table
            ->query(User::query())
            // 2. Modificamos la query según la pestaña seleccionada
            ->modifyQueryUsing(function (Builder $query) {
                if ($this->activeTab === 'todos' || !$this->activeTab) {
                    return $query;
                }
                return $query->where('guard_day', $this->activeTab);
            })
            ->columns([
                TextColumn::make('name')->label('Nombre Completo')->searchable()->state(fn ($record) => trim(
                                            ($record->name ?? '') . ' ' . 
                                            ($record->last_name ?? '') . ' ' . 
                                            ($record->second_last_name ?? '')
                                        )),
                TextColumn::make('marital_status')->label('Estado Civil')->formatStateUsing(fn($state): string => match ($state) {
                                'child' => 'Niño/a',
                                'young' => 'Joven',
                                'single'   => 'Soltero/a',
                                'married_young'  => 'Casado/a Joven',
                                'married_adult' => 'Casado/a Adulto',
                                'married_old'  => 'Casado/a Mayor',
                                default    => $state ?? '-',
                            })->badge(),
                TextColumn::make('guard_day')->label('Día de Guardia')->formatStateUsing(fn (string $state): string => GuardDay::tryFrom($state)?->label() ?? $state)->badge()->searchable(),
                IconColumn::make('is_active')
                    ->label('Estado')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
            ])
            ->filters([
                // Aquí puedes añadir filtros tradicionales si los necesitas
                SelectFilter::make('guard_day')
                    ->label('Día de Guardia')
                    ->options(GuardDay::options()) // Usa el enum directamente
                    ->placeholder('Todos los días') // Sin filtro por defecto
                    ->native(false), // Opcional: usa un select estilizado de Filament
                TernaryFilter::make('is_active')
                    ->label('Estado de Usuario')
                    ->placeholder('Todos')
                    ->trueLabel('Usuarios Activos')
                    ->falseLabel('Usuarios Inactivos (Baja)'),
                ])
            ->actions([
                // El botón de "Lectura" que abre el Infolist en un modal
                ViewAction::make()
                    ->label('Ver detalle')
                    ->modalHeading('Detalles del Usuario')
                    ->infolist([
                        // ── Cabecera: Foto y resúmen ──────────────────────────────────────
                Section::make()
                    ->schema([
                        Grid::make(['default' => 1, 'sm' => 12])
                            ->schema([
                                Group::make([
                                    ImageEntry::make('photo_url')
                                        ->hiddenLabel()
                                        ->state(fn ($record) => $record->getFilamentAvatarUrl())
                                        // Esquinas redondeadas vía CSS inline para asegurar su aplicación (útil si Tailwind JIT omite la clase)
                                        ->extraImgAttributes([
                                            'style' => 'border-radius: 16px; width: 200px; height: 200px; object-fit: cover; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border: 2px solid white;'
                                        ])
                                ])->columnSpan(['default' => 1, 'sm' => 6, 'md' => 2]),
                                
                                Group::make([
                                    TextEntry::make('full_name')
                                        ->hiddenLabel()
                                        ->state(fn ($record) => trim(
                                            ($record->name ?? '') . ' ' . 
                                            ($record->last_name ?? '') . ' ' . 
                                            ($record->second_last_name ?? '')
                                        ))
                                        ->extraAttributes([
                                            'style' => 'font-size: 1.5rem; font-weight: 700; line-height: 1.2; margin-bottom: 0.5rem; display: block; text-align:right;'
                                        ]),

                                    TextEntry::make('contact_summary')
                                        ->hiddenLabel()
                                        ->state(function ($record) {
                                            $parts = array_filter([$record->email, $record->phone]);
                                            return implode('  •  ', $parts) ?: 'Datos de contacto no disponibles';
                                        })
                                        ->color('gray')
                                        ->extraAttributes([
                                            'style' => 'font-size: 0.95rem; font-weight: 500; text-align:right;'
                                        ]),
                                        TextEntry::make('age')
                                        ->hiddenLabel()
                                        ->state(function ($record) {
                                            return $record->birth_date ? $record->birth_date->age . ' años' : '-';
                                        })
                                        ->color('gray')
                                        ->extraAttributes([
                                            'style' => 'font-size: 0.95rem; font-weight: 500; text-align:right;'
                                        ]),
                                ])->columnSpan(['default' => 1, 'sm' => 6, 'md' => 10])
                                  ->extraAttributes([
                                      'style' => 'display: flex; flex-direction: column; justify-content: center;'
                                  ])
                            ])->extraAttributes(['style' => 'align-items: center;']) // Asegura que el contenido del grid se centre verticalmente
                    ]),

                // ── Personal Information ──────────────────────────────────────────
                Section::make('Información Personal')
                    ->description('Datos básicos del usuario.')
                    ->icon('heroicon-o-user')
                    ->columns(['sm' => 2, 'md' => 3])
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nombre'),

                        TextEntry::make('last_name')
                            ->label('Apellido paterno')
                            ->placeholder('-'),

                        TextEntry::make('second_last_name')
                            ->label('Apellido materno')
                            ->placeholder('-'),

                        TextEntry::make('email')
                            ->label('Correo electrónico')
                            ->icon('heroicon-m-envelope'),

                        TextEntry::make('phone')
                            ->label('Teléfono')
                            ->placeholder('-')
                            ->icon('heroicon-m-phone'),

                        TextEntry::make('birth_date')
                            ->label('Fecha de nacimiento')
                            ->date('d/m/Y')
                            ->placeholder('-')
                            ->icon('heroicon-m-cake'),
                    ]),

                // ── Detalle Adicional ─────────────────────────────────────────────
                Section::make('Detalles Adicionales')
                    ->description('Información complementaria legal y de grupo.')
                    ->icon('heroicon-o-document-text')
                    ->columns(['sm' => 2, 'md' => 3])
                    ->schema([
                        TextEntry::make('gender')
                            ->label('Género')
                            ->badge()
                            ->placeholder('-')
                            ->formatStateUsing(fn($state): string => match ($state) {
                                'male'   => 'Masculino',
                                'female' => 'Femenino',
                                'other'  => 'Otro',
                                default  => $state ?? '-',
                            })
                            ->color(fn($state): string => match ($state) {
                                'male'   => 'info',
                                'female' => 'pink',
                                default  => 'gray',
                            }),

                        TextEntry::make('marital_status')
                            ->label('Estado civil')
                            ->badge()
                            ->placeholder('-')
                            ->formatStateUsing(fn($state): string => match ($state) {
                                'single'   => 'Soltero/a',
                                'married'  => 'Casado/a',
                                'divorced' => 'Divorciado/a',
                                'widowed'  => 'Viudo/a',
                                'other'    => 'Otro',
                                default    => $state ?? '-',
                            })
                            ->color('gray'),

                        TextEntry::make('join_date')
                            ->label('Fecha de ingreso')
                            ->date('d/m/Y')
                            ->placeholder('-')
                            ->icon('heroicon-m-calendar-days'),

                        TextEntry::make('guard_day')
                            ->label('Día de guardia')
                            ->placeholder('-')
                            ->badge()
                            ->color('success')
                            ->formatStateUsing(
                                fn($state): string => $state instanceof GuardDay
                                    ? $state->label()
                                    : (GuardDay::tryFrom((string) $state)?->label() ?? $state ?? '-')
                            ),

                        IconEntry::make('recommendation_letter')
                            ->label('Carta de recomendación')
                            ->boolean(),
                    ]),

                // ── Ubicación y Notas ─────────────────────────────────────────────
                Section::make('Ubicación y Notas')
                    ->description('Dirección del usuario y observaciones registradas.')
                    ->icon('heroicon-o-map-pin')
                    ->columns(1)
                    ->schema([
                        TextEntry::make('address')
                            ->label('Dirección completa')
                            ->placeholder('-'),

                        TextEntry::make('observations')
                            ->label('Observaciones')
                            ->placeholder('Sin observaciones'),
                    ]),
                    ]),
            ]);
    }

    public function getTabs(): array
    {
        $tabs = [
            'todos' => Tab::make('Todos')
                ->badge(User::query()->count()),
        ];

        // Añadimos los días del Enum dinámicamente
        foreach (GuardDay::cases() as $day) {
            $tabs[$day->value] = Tab::make($day->label())
                ->badge(User::query()->where('guard_day', $day->value)->count());
        }

        return $tabs;
    }
}
