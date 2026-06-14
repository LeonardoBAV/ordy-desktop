<?php

namespace App\Enums;

enum PrintMethodEnum: string
{
    case Electron = 'electron';
    case NativeWindows = 'native_windows';
    case SystemCommand = 'system_command';

    public function label(): string
    {
        return match ($this) {
            self::Electron => __('filamentphp-resources.resources.print_settings.options.methods.electron'),
            self::NativeWindows => __('filamentphp-resources.resources.print_settings.options.methods.native_windows'),
            self::SystemCommand => __('filamentphp-resources.resources.print_settings.options.methods.system_command'),
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $method): array => [$method->value => $method->label()])
            ->all();
    }
}
