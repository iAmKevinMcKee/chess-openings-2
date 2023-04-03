<?php

namespace App\Http\Livewire;

use App\Models\Attempt;
use App\Models\CorrectMove;
use App\Models\Opening;
use App\Models\PossibleMove;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Livewire\Component;

class Training extends Component implements HasForms
{
    use InteractsWithForms;

    public $formData;
    public $attempt = null;
    public $correctMoveNotation;
    public $playAsWhite = true;
    public $wrongMove = null;
    public $openings = [];

    public function mount(): void
    {
        $this->openings = session()->get('openings') ?? [];
        $this->form->fill([
            'openings' => $this->openings,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('openings')
                    ->searchable()
                    ->multiple()
                    ->required()
                    ->options(Opening::where('user_id', auth()->user()->id)->pluck('name', 'id'))
            ])
            ->statePath('formData');
    }

    public function setOpenings()
    {
        $form = $this->form->getState();
        $this->openings = $form['openings'];
        session()->put('openings', $this->openings);
        Notification::make()->success()->title('openings are set')->send();
    }

    public function startAttempt()
    {
        $this->dispatchBrowserEvent('reset');
        $this->wrongMove = false;
        $this->correctMoveNotation = null;
        $openingId = collect($this->openings)->random();
        $this->attempt = Attempt::create([
            'user_id' => auth()->user()->id,
            'opening_id' => (int) $openingId,
        ]);
    }

    public function move($fromFen, $toFen, $moveFrom, $moveTo, $color, $notation)
    {
        // find the correct move based on the $fromFen
        $correctMove = CorrectMove::where('from_fen', $fromFen)
            ->where('opening_id', $this->attempt->opening_id)
            ->where('is_white', $this->playAsWhite)
            ->where('user_id', auth()->id())
            ->get()->first();
        if ($correctMove->to_fen === $toFen) {
            // if the move is correct, check if there are any possible moves for the new position
            $this->attempt->attempt_moves()->create([
                'move_number' => 1, // fix this once I can get online to check the chessjs docs
                'notation' => $notation,
                'correct' => 1,
            ]);
            $this->wrongMove = false;
            $this->possibleMoves = PossibleMove::where('is_white', $this->playAsWhite)
                ->where('opening_id', $this->attempt->opening_id)
                ->where('fen', $toFen)
                ->where('user_id', auth()->id())
                ->orderBy('probability', 'desc')
                ->get();
            if ($this->possibleMoves->count() === 0) {
                // if no possible moves, then the round is over and the user has won
                $this->attempt->update([
                    'correct' => 1,
                ]);
                Notification::make()->success()->title('You Won!')->send();
            } else {
                Notification::make()->success()->title('Correct!')->send();

                // if there are possible moves, then randomly pick one based on probability
                $move = $this->randomlyPickAPossibleMoveBasedOnProbability();
                $this->dispatchBrowserEvent('next', ['notation' => $move->notation]);
            }
        } else {
            $this->wrongMove = true;
            $this->correctMoveNotation = $correctMove->notation;
            // move is incorrect, show a message that it was not correct and show the correct move
            $this->attempt->attempt_moves()->create([
                'move_number' => 1, // fix this once I can get online to check the chessjs docs
                'notation' => $notation,
                'correct' => 0,
            ]);

            $this->attempt->update([
                'correct' => 0,
            ]);
        }
    }

    public function render()
    {
        return view('livewire.training');
    }

    private function randomlyPickAPossibleMoveBasedOnProbability(): PossibleMove|null
    {
        $totalProbability = $this->possibleMoves->sum('probability');
        $randomNumber = rand(0, $totalProbability);
        foreach ($this->possibleMoves as $move) {
            $randomNumber -= $move->probability;
            if ($randomNumber <= 0) {
                return $move;
            }
        }
        return null;
    }
}
