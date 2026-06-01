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
                ->label('SKU')
                ->requiredMapping()
                ->guess(['sku', 'SKU', 'codigo'])
                ->rules(['required', 'string', 'max:255']),
            ImportColumn::make('name')
                ->label('Nome')
                ->requiredMapping()
                ->guess(['name', 'nome', 'produto', 'product'])
                ->rules(['required', 'string', 'max:255']),
            ImportColumn::make('stock_limit')
                ->label('Limite de estoque')
                ->requiredMapping()
                ->integer()
                ->guess(['stock_limit', 'limite_estoque', 'limite de estoque'])
                ->rules(['required', 'integer', 'min:0']),
            ImportColumn::make('unlimited')
                ->label('Ilimitado')
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
        $body = Number::format($import->successful_rows).' '.str('produto')->plural($import->successful_rows).' importado(s).';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('linha')->plural($failedRowsCount).' falhou/falharam.';
        }

        return $body;
    }
}
