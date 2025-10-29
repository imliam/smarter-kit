<?php

declare(strict_types=1);

namespace App\Filament\Actions;

use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Override;

class FactoryFormAction extends Action
{
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Randomise Form');
        $this->icon(Heroicon::Sparkles);
        $this->color('info');
        $this->action(function (CreateRecord|EditRecord $livewire): void {
            $model = $livewire::getResource()::getModel();

            if (! $model || ! class_exists($model) || ! method_exists($model, 'factory')) {
                return;
            }

            $data = $model::factory()->make()->toArray();

            $livewire->form->fill($data);
        });

        $this->visible(function (CreateRecord|EditRecord $livewire) {
            $model = $livewire::getResource()::getModel();

            if (! $model || ! class_exists($model) || ! method_exists($model, 'factory')) {
                return false;
            }

            return app()->isLocal();
        });
    }

    public static function getDefaultName(): ?string
    {
        return 'factoryForm';
    }
}
