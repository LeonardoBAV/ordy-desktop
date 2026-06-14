<?php

namespace App\Filament\Resources\FailedJobs;

use App\Filament\Resources\FailedJobs\Pages\ListFailedJobs;
use App\Models\FailedJob;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FailedJobResource extends Resource
{
    protected static ?string $model = FailedJob::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedExclamationTriangle;

    protected static ?int $navigationSort = 31;

    public static function getModelLabel(): string
    {
        return __('filamentphp-resources.resources.failed_jobs.labels.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filamentphp-resources.resources.failed_jobs.labels.plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filamentphp-resources.resources.printing.navigation.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('filamentphp-resources.resources.failed_jobs.navigation.label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label(__('filamentphp-resources.resources.failed_jobs.table.columns.id.label'))
                    ->sortable(),
                TextColumn::make('display_name')
                    ->label(__('filamentphp-resources.resources.failed_jobs.table.columns.display_name.label'))
                    ->state(fn (FailedJob $record): string => $record->displayName())
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query->where('payload', 'like', "%{$search}%")),
                TextColumn::make('queue')
                    ->label(__('filamentphp-resources.resources.failed_jobs.table.columns.queue.label'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('exception_summary')
                    ->label(__('filamentphp-resources.resources.failed_jobs.table.columns.exception.label'))
                    ->state(fn (FailedJob $record): string => $record->exceptionSummary())
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query->where('exception', 'like', "%{$search}%")),
                TextColumn::make('failed_at')
                    ->label(__('filamentphp-resources.resources.failed_jobs.table.columns.failed_at.label'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([])
            ->recordActions([])
            ->toolbarActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFailedJobs::route('/'),
        ];
    }
}
