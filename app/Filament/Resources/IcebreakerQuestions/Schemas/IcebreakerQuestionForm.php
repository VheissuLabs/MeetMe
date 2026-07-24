<?php

namespace App\Filament\Resources\IcebreakerQuestions\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class IcebreakerQuestionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('question')
                    ->label('Question')
                    ->helperText('Shown verbatim to the scanner. Keep it generic — it never references a person.')
                    ->required()
                    ->rows(3)
                    ->maxLength(500)
                    ->columnSpanFull(),
            ]);
    }
}
