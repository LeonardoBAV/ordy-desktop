<?php

namespace App\Filament\Pages;

use App\Enums\PrintMethodEnum;
use App\Models\PrintSetting;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class PrintSettings extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?int $navigationSort = 32;

    protected string $view = 'filament.pages.print-settings';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $setting = PrintSetting::current();

        $this->form->fill([
            'method' => $setting->method->value,
            'copies' => $setting->copies,
        ]);
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filamentphp-resources.resources.printing.navigation.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('filamentphp-resources.resources.print_settings.navigation.label');
    }

    public function getTitle(): string
    {
        return __('filamentphp-resources.resources.print_settings.labels.plural');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('method')
                    ->label(__('filamentphp-resources.resources.print_settings.form.fields.method.label'))
                    ->options(PrintMethodEnum::options())
                    ->required(),
                TextInput::make('copies')
                    ->label(__('filamentphp-resources.resources.print_settings.form.fields.copies.label'))
                    ->required()
                    ->integer()
                    ->minValue(1)
                    ->maxValue(20),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        PrintSetting::current()->update($this->form->getState());

        Notification::make()
            ->title(__('filamentphp-resources.resources.print_settings.notifications.saved.title'))
            ->success()
            ->send();
    }
}
