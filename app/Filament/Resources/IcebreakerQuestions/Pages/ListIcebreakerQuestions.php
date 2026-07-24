<?php

namespace App\Filament\Resources\IcebreakerQuestions\Pages;

use App\Filament\Resources\IcebreakerQuestions\IcebreakerQuestionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListIcebreakerQuestions extends ListRecords
{
    protected static string $resource = IcebreakerQuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
