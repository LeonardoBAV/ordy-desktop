<?php

namespace App\Imports;

use App\Models\Product;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class ProductImporter extends Importer
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('sku')
                ->label(__('filamentphp-resources.resources.products.import.columns.sku.label'))
                ->requiredMapping()
                ->guess(['sku', 'SKU', 'codigo'])
                ->rules(['required', 'string', 'max:255']),
            ImportColumn::make('name')
                ->label(__('filamentphp-resources.resources.products.import.columns.name.label'))
                ->requiredMapping()
                ->guess(['name', 'nome', 'produto', 'product'])
                ->rules(['required', 'string', 'max:255']),
            ImportColumn::make('stock_limit')
                ->label(__('filamentphp-resources.resources.products.import.columns.stock_limit.label'))
                ->requiredMapping()
                ->integer()
                ->guess(['stock_limit', 'limite_estoque', 'limite de estoque'])
                ->rules(['required', 'integer', 'min:0']),
            ImportColumn::make('unlimited')
                ->label(__('filamentphp-resources.resources.products.import.columns.unlimited.label'))
                ->boolean()
                ->ignoreBlankState()
                ->guess(['unlimited', 'ilimitado'])
                ->rules(['nullable', 'boolean']),
        ];
    }

    public function resolveRecord(): Product
    {
        return Product::firstOrNew([
            'sku' => $this->data['sku'],
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = trans_choice(
            'filamentphp-resources.resources.products.import.notifications.completed.successful_rows',
            $import->successful_rows,
            ['count' => Number::format($import->successful_rows)],
        );

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.trans_choice(
                'filamentphp-resources.resources.products.import.notifications.completed.failed_rows',
                $failedRowsCount,
                ['count' => Number::format($failedRowsCount)],
            );
        }

        return $body;
    }
}
