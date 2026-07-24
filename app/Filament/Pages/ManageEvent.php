<?php

namespace App\Filament\Pages;

use App\Models\Event;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Artisan;

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

    /** @return array<int, Action> */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('generateQuestions')
                ->label('Generate questions')
                ->icon(Heroicon::Sparkles)
                ->requiresConfirmation()
                ->modalDescription('Generate the AI icebreaker pool. Needs an Anthropic API key.')
                ->schema([
                    Toggle::make('fresh')->label('Wipe existing questions and regenerate'),
                ])
                ->action(function (array $data): void {
                    Artisan::call('meetme:generate-questions', $data['fresh'] ?? false ? ['--fresh' => true] : []);
                    $this->notifyResult('Questions');
                }),
            Action::make('sendRecaps')
                ->label('Send recap emails')
                ->icon(Heroicon::Envelope)
                ->requiresConfirmation()
                ->modalDescription('Email every attendee with at least one confirmed meeting their recap.')
                ->action(function (): void {
                    Artisan::call('meetme:send-recaps');
                    $this->notifyResult('Recaps');
                }),
            Action::make('purgeAnswers')
                ->label('Purge answers')
                ->icon(Heroicon::Trash)
                ->color('danger')
                ->requiresConfirmation()
                ->modalDescription('Hard-delete recorded answer text once the retention window has passed. This cannot be undone.')
                ->action(function (): void {
                    Artisan::call('meetme:purge-answers');
                    $this->notifyResult('Answer purge');
                }),
        ];
    }

    private function notifyResult(string $title): void
    {
        Notification::make()
            ->title($title)
            ->body(trim(Artisan::output()))
            ->success()
            ->send();
    }
}
