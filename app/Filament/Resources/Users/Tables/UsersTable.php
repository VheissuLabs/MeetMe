<?php

namespace App\Filament\Resources\Users\Tables;

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
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
