<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\RelationManagers;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Date;
use Override;

class TokensRelationManager extends RelationManager
{
    /** The plain-text token to display after creation (one-time). */
    public ?string $newTokenValue = null;

    #[Override]
    protected static string $relationship = 'tokens';

    #[Override]
    protected static ?string $title = 'API Tokens';

    #[Override]
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    #[Override]
    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('abilities')
                    ->badge()
                    ->separator(','),
                TextColumn::make('last_used_at')
                    ->dateTime()
                    ->since()
                    ->placeholder('Never'),
                TextColumn::make('expires_at')
                    ->dateTime()
                    ->since()
                    ->placeholder('Never'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id', 'desc')
            ->filters([])
            ->headerActions([
                Action::make('createToken')
                    ->label('Create token')
                    ->icon(Heroicon::OutlinedPlus)
                    ->modalHeading(fn (): string => $this->newTokenValue ? 'Token created' : 'Create token')
                    ->modalDescription(fn (): ?string => $this->newTokenValue
                        ? "Copy your new token now. You won't be able to see it again."
                        : null)
                    ->beforeFormFilled(fn (): null => $this->newTokenValue = null)
                    ->form([
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
                        $expiresAt = filled($data['expiresAt']) ? Date::parse($data['expiresAt'])->endOfDay() : null;

                        /** @var User $user */
                        $user = $this->getOwnerRecord();

                        $token = $user->createToken($data['name'], $data['abilities'], $expiresAt);

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
            ])
            ->recordActions([
                DeleteAction::make()
                    ->label('Revoke')
                    ->modalHeading('Revoke token')
                    ->modalDescription('This will permanently revoke the token. Any application using it will lose access immediately.')
                    ->successNotificationTitle('Token revoked'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Revoke selected')
                        ->modalHeading('Revoke selected tokens')
                        ->successNotificationTitle('Tokens revoked'),
                ]),
            ])
            ->emptyStateHeading('No tokens')
            ->emptyStateDescription('Create a token to grant API access for this user.')
            ->emptyStateIcon(Heroicon::OutlinedKey);
    }

    /** @return list<string> */
    private function availableAbilities(): array
    {
        /** @var array<string> $abilities */
        $abilities = config()->array('sanctum.abilities.default', []);

        return array_values($abilities);
    }
}
