<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Schema;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Laravel\Sanctum\PersonalAccessToken;
use Override;

class ApiTokens extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    /** The plain-text token value to display after creation (one-time). */
    public ?string $newTokenValue = null;

    #[Override]
    protected static ?string $title = 'API Tokens';

    #[Override]
    protected static ?string $navigationLabel = 'API Tokens';

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;

    #[Override]
    public function content(Schema $schema): Schema
    {
        return $schema->components([EmbeddedTable::make()]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => PersonalAccessToken::query()
                ->where('tokenable_type', (new User)->getMorphClass())
                ->where('tokenable_id', Auth::id())
                ->latest('id'))
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable(),
                TextColumn::make('abilities')
                    ->label('Abilities')
                    ->badge()
                    ->separator(','),
                TextColumn::make('last_used_at')
                    ->label('Last used')
                    ->dateTime()
                    ->since()
                    ->placeholder('Never'),
                TextColumn::make('expires_at')
                    ->label('Expires')
                    ->dateTime()
                    ->since()
                    ->placeholder('Never'),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->since(),
            ])
            ->recordActions([
                Action::make('revoke')
                    ->label('Revoke')
                    ->color('danger')
                    ->icon(Heroicon::OutlinedTrash)
                    ->requiresConfirmation()
                    ->action(function (PersonalAccessToken $record): void {
                        $record->delete();

                        Notification::make()
                            ->title('Token revoked')
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Revoke selected'),
                ]),
            ])
            ->emptyStateHeading('No tokens yet')
            ->emptyStateDescription('Create a token to get API access.')
            ->emptyStateIcon(Heroicon::OutlinedKey);
    }

    /** @return Action[] */
    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            Action::make('docs')
                ->label('Browse API documentation')
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->iconPosition(IconPosition::After)
                ->color('gray')
                ->url(config('scribe.laravel.docs_url'))
                ->openUrlInNewTab()
                ->visible(fn (): bool => filled(config('scribe.laravel.docs_url'))),
            Action::make('createToken')
                ->label('Create token')
                ->icon(Heroicon::OutlinedPlus)
                ->modalHeading(fn (): string => $this->newTokenValue ? 'Token created' : 'Create token')
                ->modalDescription(fn (): ?string => $this->newTokenValue
                    ? "Copy your new token now. You won't be able to see it again."
                    : null)
                ->beforeFormFilled(fn (): null => $this->newTokenValue = null)
                ->schema([
                    TextInput::make('name')
                        ->label('Token name')
                        ->placeholder('e.g. my-app, ios-app')
                        ->required()
                        ->maxLength(255)
                        ->hidden(fn (): bool => (bool) $this->newTokenValue),
                    CheckboxList::make('abilities')
                        ->label('Abilities')
                        ->options(fn (): array => array_combine(
                            $this->availableAbilities(),
                            $this->availableAbilities(),
                        ))
                        ->default($this->availableAbilities())
                        ->required()
                        ->hidden(fn (): bool => (bool) $this->newTokenValue),
                    DatePicker::make('expiresAt')
                        ->label('Expires at')
                        ->helperText('Leave blank for a token that never expires.')
                        ->minDate(now()->addDay())
                        ->nullable()
                        ->hidden(fn (): bool => (bool) $this->newTokenValue),
                    TextInput::make('token')
                        ->label('Your new token')
                        ->readOnly()
                        ->copyable()
                        ->extraInputAttributes(['class' => 'font-mono text-xs'])
                        ->dehydrated(false)
                        ->hidden(fn (): bool => ! $this->newTokenValue),
                ])
                ->action(function (array $data, Action $action): void {
                    /** @var User $user */
                    $user = Auth::user();

                    $expiresAt = filled($data['expiresAt']) ? Date::parse((string) $data['expiresAt'])->endOfDay() : null;

                    $token = $user->createToken((string) $data['name'], (array) $data['abilities'], $expiresAt);

                    $this->newTokenValue = $token->plainTextToken;

                    /** @var array<int, array{data: array<string, mixed>}> $mountedActions */
                    $mountedActions = $this->mountedActions;
                    $mountedActions[0]['data']['token'] = $token->plainTextToken;
                    $this->mountedActions = $mountedActions;

                    $action->halt();
                })
                ->modalSubmitAction(fn (Action $action): Action => $this->newTokenValue
                    ? $action->hidden()
                    : $action)
                ->modalCancelAction(fn (Action $action): Action => $this->newTokenValue
                    ? $action->label('Done')
                    : $action),
        ];
    }

    /** @return list<string> */
    private function availableAbilities(): array
    {
        /** @var array<string> $abilities */
        $abilities = config()->array('sanctum.abilities.default', []);

        return array_values($abilities);
    }
}
