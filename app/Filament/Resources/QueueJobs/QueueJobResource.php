<?php

namespace App\Filament\Resources\QueueJobs;

use App\Filament\Resources\QueueJobs\Pages\ListQueueJobs;
use App\Models\QueueJob;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class QueueJobResource extends Resource
{
    protected static ?string $model = QueueJob::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPrinter;

    protected static ?int $navigationSort = 30;

    public static function getModelLabel(): string
    {
        return __('filamentphp-resources.resources.queue_jobs.labels.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filamentphp-resources.resources.queue_jobs.labels.plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filamentphp-resources.resources.printing.navigation.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('filamentphp-resources.resources.queue_jobs.navigation.label');
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
                    ->label(__('filamentphp-resources.resources.queue_jobs.table.columns.id.label'))
                    ->sortable(),
                TextColumn::make('display_name')
                    ->label(__('filamentphp-resources.resources.queue_jobs.table.columns.display_name.label'))
                    ->state(fn (QueueJob $record): string => $record->displayName())
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query->where('payload', 'like', "%{$search}%")),
                TextColumn::make('queue')
                    ->label(__('filamentphp-resources.resources.queue_jobs.table.columns.queue.label'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('attempts')
                    ->label(__('filamentphp-resources.resources.queue_jobs.table.columns.attempts.label'))
                    ->numeric()
                    ->alignCenter()
                    ->sortable(),
                TextColumn::make('available_at')
                    ->label(__('filamentphp-resources.resources.queue_jobs.table.columns.available_at.label'))
                    ->state(fn (QueueJob $record): ?Carbon => $record->available_at ? Carbon::createFromTimestamp($record->available_at) : null)
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('filamentphp-resources.resources.queue_jobs.table.columns.created_at.label'))
                    ->state(fn (QueueJob $record): ?Carbon => $record->created_at ? Carbon::createFromTimestamp($record->created_at) : null)
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
            'index' => ListQueueJobs::route('/'),
        ];
    }
}
