<?php

namespace App\Filament\Resources\Movements;

use App\Filament\Resources\Movements\Pages\ListMovements;
use App\Models\Movement;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class MovementResource extends Resource
{
    protected static ?string $model = Movement::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    protected static ?int $navigationSort = 40;

    protected static ?string $recordTitleAttribute = 'movement_uuid';

    public static function getModelLabel(): string
    {
        return __('filamentphp-resources.resources.movements.labels.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filamentphp-resources.resources.movements.labels.plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filamentphp-resources.resources.stock.navigation.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('filamentphp-resources.resources.movements.navigation.label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_id')
                    ->label(__('filamentphp-resources.resources.movements.form.fields.product_id.label'))
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('movement_uuid')
                    ->label(__('filamentphp-resources.resources.movements.form.fields.movement_uuid.label'))
                    ->default(fn (): string => (string) Str::uuid())
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(36),
                TextInput::make('qty')
                    ->label(__('filamentphp-resources.resources.movements.form.fields.qty.label'))
                    ->required()
                    ->integer(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('movement_uuid')
            ->columns([
                TextColumn::make('product.name')
                    ->label(__('filamentphp-resources.resources.movements.table.columns.product.label'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('movement_uuid')
                    ->label(__('filamentphp-resources.resources.movements.table.columns.movement_uuid.label'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('qty')
                    ->label(__('filamentphp-resources.resources.movements.table.columns.qty.label'))
                    ->numeric()
                    ->alignCenter()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('filamentphp-resources.resources.movements.table.columns.created_at.label'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('filamentphp-resources.resources.movements.table.columns.updated_at.label'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([])
            ->toolbarActions([]);
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
            'index' => ListMovements::route('/'),
        ];
    }
}
