<?php

namespace App\Filament\Resources\Products;

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Models\Product;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getModelLabel(): string
    {
        return __('filamentphp-resources.resources.products.labels.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filamentphp-resources.resources.products.labels.plural');
    }

    public static function getNavigationLabel(): string
    {
        return __('filamentphp-resources.resources.products.navigation.label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('sku')
                    ->label(__('filamentphp-resources.resources.products.form.fields.sku.label'))
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('name')
                    ->label(__('filamentphp-resources.resources.products.form.fields.name.label'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('stock_limit')
                    ->label(__('filamentphp-resources.resources.products.form.fields.stock_limit.label'))
                    ->required()
                    ->integer()
                    ->minValue(0),
                Checkbox::make('unlimited')
                    ->label(__('filamentphp-resources.resources.products.form.fields.unlimited.label'))
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('sku')
                    ->label(__('filamentphp-resources.resources.products.table.columns.sku.label'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label(__('filamentphp-resources.resources.products.table.columns.name.label'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('stock_limit')
                    ->label(__('filamentphp-resources.resources.products.table.columns.stock_limit.label'))
                    ->numeric()
                    ->sortable(),
                IconColumn::make('unlimited')
                    ->label(__('filamentphp-resources.resources.products.table.columns.unlimited.label'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('filamentphp-resources.resources.products.table.columns.created_at.label'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('filamentphp-resources.resources.products.table.columns.updated_at.label'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->label(__('filamentphp-resources.resources.products.actions.edit.label')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label(__('filamentphp-resources.resources.products.actions.delete_selected.label')),
                ]),
            ]);
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
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }
}
