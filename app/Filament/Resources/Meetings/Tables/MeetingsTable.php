<?php

namespace App\Filament\Resources\Meetings\Tables;

use App\Enums\MeetingStatus;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MeetingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('initiator.name')
                    ->label('Scanner')
                    ->searchable()
                    ->placeholder('—'),
                TextColumn::make('recipient.name')
                    ->label('Scanned')
                    ->searchable()
                    ->placeholder('—'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (MeetingStatus $state): string => match ($state) {
                        MeetingStatus::Pending => 'gray',
                        MeetingStatus::Answered => 'warning',
                        MeetingStatus::Confirmed => 'success',
                        MeetingStatus::Rejected => 'danger',
                    }),
                TextColumn::make('rating')
                    ->formatStateUsing(fn (?int $state): string => $state === null ? '—' : str_repeat('★', $state))
                    ->placeholder('—'),
                TextColumn::make('resolved_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Scanned at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(collect(MeetingStatus::cases())->mapWithKeys(
                        fn (MeetingStatus $status): array => [$status->value => ucfirst($status->value)]
                    )->all()),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
