<?php

declare(strict_types=1);

namespace App\Providers;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;
use Illuminate\Support\ServiceProvider;

class FilamentServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Select::configureUsing(function (Select $field) {
            return $field
                ->searchable()
                ->preload();
        });

        // Add sensible min and max values to prevent dates like 01/01/0000 or 01/01/3000
        DatePicker::configureUsing(function (DatePicker $datePicker) {
            return $datePicker
                ->minDate(Carbon::createFromDate(1500, 1, 1))
                ->maxDate(now()->addYears(30));
        });

        Table::configureUsing(function (Table $table) {
            return $table
                ->striped()
                ->deferLoading()
                ->reorderableColumns()
                ->columnManagerColumns(2)
                ->columnManagerTriggerAction(fn(Action $action) => $action->button()->label('Columns'))
                ->filtersTriggerAction(fn(Action $action) => $action->button()->label('Filters')->slideOver()->closeModalByClickingAway(true))
                ->filtersFormWidth(Width::Small)
                ->paginationPageOptions([10, 25, 50, 100]);
        });

        Column::configureUsing(function (Column $column) {
            return $column->toggleable();
        });

        TextColumn::configureUsing(function (TextColumn $textColumn) {
            return $textColumn
                ->searchable()
                ->sortable();
        });

        Notification::configureUsing(function (Notification $notification) {
            return $notification->duration(10_000);
        });
    }
}
