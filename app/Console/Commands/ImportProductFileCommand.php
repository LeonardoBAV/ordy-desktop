<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use SplFileObject;

#[Signature('app:import-product-file {--path= : Path where the CSV file should be generated}')]
#[Description('Generate a sample CSV file compatible with the product importer')]
class ImportProductFileCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $path = $this->getOutputPath();
        $directory = dirname($path);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $file = new SplFileObject($path, 'w');
        $file->fputcsv(['sku', 'name', 'stock_limit', 'unlimited']);

        foreach ($this->products() as $product) {
            $file->fputcsv($product);
        }

        $this->components->info("Product import CSV generated at [{$path}].");

        return self::SUCCESS;
    }

    private function getOutputPath(): string
    {
        $path = $this->option('path') ?: storage_path('app/imports/products.csv');

        if (str_starts_with($path, DIRECTORY_SEPARATOR)) {
            return $path;
        }

        return base_path($path);
    }

    /**
     * @return array<int, array{sku: string, name: string, stock_limit: int, unlimited: int}>
     */
    private function products(): array
    {
        return collect(range(1, 30))
            ->map(function (int $index): array {
                $stockLimit = $index % 5 === 0 ? 0 : $index * 2;

                return [
                    'sku' => sprintf('ORDY%03d', $index),
                    'name' => sprintf('Produto Ordy %02d', $index),
                    'stock_limit' => $stockLimit,
                    'unlimited' => $stockLimit === 0 ? 1 : 0,
                ];
            })
            ->all();
    }
}
