<?php

namespace App\Filament\Resources\IcebreakerQuestions;

use App\Filament\Resources\IcebreakerQuestions\Pages\CreateIcebreakerQuestion;
use App\Filament\Resources\IcebreakerQuestions\Pages\EditIcebreakerQuestion;
use App\Filament\Resources\IcebreakerQuestions\Pages\ListIcebreakerQuestions;
use App\Filament\Resources\IcebreakerQuestions\Schemas\IcebreakerQuestionForm;
use App\Filament\Resources\IcebreakerQuestions\Tables\IcebreakerQuestionsTable;
use App\Models\IcebreakerQuestion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class IcebreakerQuestionResource extends Resource
{
    protected static ?string $model = IcebreakerQuestion::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQuestionMarkCircle;

    protected static ?string $navigationLabel = 'Questions';

    protected static ?string $modelLabel = 'question';

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    public static function form(Schema $schema): Schema
    {
        return IcebreakerQuestionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return IcebreakerQuestionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListIcebreakerQuestions::route('/'),
            'create' => CreateIcebreakerQuestion::route('/create'),
            'edit' => EditIcebreakerQuestion::route('/{record}/edit'),
        ];
    }
}
