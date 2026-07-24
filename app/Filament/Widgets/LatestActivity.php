<?php

namespace App\Filament\Widgets;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use Spatie\Activitylog\Models\Activity;

class LatestActivity extends TableWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = -1;

    #[On('echo:admin-activity,ActivityLogged')]
    public function refresh(): void {}

    public function table(Table $table): Table
    {
        return $table
            ->heading('Activity log')
            ->query(fn (): Builder => Activity::query()->with('causer')->latest())
            ->columns([
                TextColumn::make('created_at')
                    ->label('When')
                    ->since()
                    ->sortable(),
                TextColumn::make('log_name')
                    ->label('Type')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'meeting' => 'success',
                        'user' => 'info',
                        'question' => 'warning',
                        'admin' => 'danger',
                        default => 'gray',
                    })
                    ->placeholder('—'),
                TextColumn::make('description')
                    ->label('Event')
                    ->wrap(),
                TextColumn::make('subject_type')
                    ->label('Subject')
                    ->formatStateUsing(fn (?string $state): string => $state ? class_basename($state) : '—')
                    ->placeholder('—'),
                TextColumn::make('causer.name')
                    ->label('By')
                    ->placeholder('System'),
            ])
            ->filters([
                SelectFilter::make('log_name')
                    ->label('Type')
                    ->options([
                        'meeting' => 'Meeting',
                        'user' => 'User',
                        'question' => 'Question',
                        'admin' => 'Admin',
                    ]),
            ])
            ->paginated([10, 25, 50]);
    }
}
