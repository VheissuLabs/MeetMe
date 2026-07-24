<?php

namespace App\Filament\Resources\IcebreakerQuestions\Pages;

use App\Filament\Resources\IcebreakerQuestions\IcebreakerQuestionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditIcebreakerQuestion extends EditRecord
{
    protected static string $resource = IcebreakerQuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
