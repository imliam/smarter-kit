<?php

declare(strict_types=1);

namespace App\Providers;

use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\ServiceProvider;

class FilamentServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Select::configureUsing(fn (Select $field): Select => $field
            ->searchable()
            ->preload());

        // Add sensible min and max values to prevent dates like 01/01/0000 or 01/01/3000
        DatePicker::configureUsing(fn (DatePicker $datePicker): DatePicker => $datePicker
            ->minDate(Date::createFromDate(1500, 1, 1))
            ->maxDate(now()->addYears(30)));

        Table::configureUsing(fn (Table $table): Table => $table
            ->striped()
            ->deferLoading()
            ->reorderableColumns()
            ->columnManagerColumns(2)
            ->columnManagerTriggerAction(fn (Action $action): Action => $action->button()->label('Columns'))
            ->filtersTriggerAction(fn (Action $action): Action => $action->button()->label('Filters')->slideOver()->closeModalByClickingAway(true))
            ->filtersFormWidth(Width::Small)
            ->paginationPageOptions([10, 25, 50, 100]));

        Column::configureUsing(fn (Column $column): Column => $column->toggleable());

        TextColumn::configureUsing(fn (TextColumn $textColumn): TextColumn => $textColumn
            ->searchable()
            ->sortable());

        Notification::configureUsing(fn (Notification $notification): Notification => $notification->duration(10_000));
    }
}
