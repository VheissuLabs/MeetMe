<?php

namespace App\Filament\Resources\Users\Tables;

use App\Actions\AwardAchievements;
use App\Enums\Achievement;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable()
                    ->placeholder('— no email —')
                    ->toggleable(),
                TextColumn::make('pronouns')
                    ->toggleable()
                    ->placeholder('—'),
                IconColumn::make('email_visible')
                    ->label('Email shared')
                    ->boolean(),
                ToggleColumn::make('is_admin')
                    ->label('Admin'),
                TextColumn::make('created_at')
                    ->label('Joined')
                    ->dateTime('M j, Y g:i A')
                    ->sortable()
                    ->toggleable(),
            ])
            ->recordActions([
                Action::make('grantAchievement')
                    ->label('Grant achievement')
                    ->icon(Heroicon::OutlinedTrophy)
                    ->schema([
                        Select::make('achievement')
                            ->options(collect(Achievement::cases())
                                ->mapWithKeys(fn (Achievement $achievement): array => [
                                    $achievement->value => $achievement->icon().' '.$achievement->label(),
                                ])
                                ->all())
                            ->required(),
                    ])
                    ->action(function (User $record, array $data): void {
                        $granted = app(AwardAchievements::class)
                            ->grant($record, Achievement::from($data['achievement']));

                        Notification::make()
                            ->title($granted ? 'Achievement granted' : 'They already have that badge')
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
