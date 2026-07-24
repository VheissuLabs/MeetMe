<?php

namespace App\Filament\Pages;

use App\Models\Event;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

/** @property-read Schema $form */
class ManageEvent extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?string $title = 'Event';

    protected string $view = 'filament.pages.manage-event';

    /** @var array<string, mixed> */
    public array $data = [];

    public function mount(): void
    {
        $this->form->fill(Event::current()->only(['name', 'starts_at', 'ends_at']));
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Conference')
                    ->description('These drive branding, recap scheduling, and answer retention across the app.')
                    ->components([
                        TextInput::make('name')
                            ->label('Conference name')
                            ->required()
                            ->maxLength(255),
                        DateTimePicker::make('starts_at')
                            ->label('Starts at')
                            ->seconds(false),
                        DateTimePicker::make('ends_at')
                            ->label('Ends at')
                            ->seconds(false)
                            ->after('starts_at'),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        Event::current()->update($this->form->getState());

        Notification::make()->title('Event settings saved.')->success()->send();
    }
}
