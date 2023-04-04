<?php

namespace App\Http\Livewire;

use App\Models\CorrectMove;
use App\Models\Opening;
use App\Models\PossibleMove;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Livewire\Component;

class Openings extends Component implements HasForms
{
    use InteractsWithForms;

    public $recording = true;
    public $playAsWhite = false;
    public $opening = null;

    public $possibleMoves = null;

    public $newOpeningName = '';

    public $correctMove;
    public $wrongMove = false;

    public $formData = [];

    public $lichessPossibleMoves;

    public function mount()
    {
        $this->opening = Opening::first() ?? new Opening();

        if ($this->playAsWhite) {
            $correctMove = CorrectMove::query()->where('from_fen', 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1')
                ->where('is_white', $this->playAsWhite)->where('user_id', auth()->id())->get()->first();

            if ($correctMove) {
                $this->correctMove = $correctMove;
            } else {
                $this->correctMove = null;
            }
        }
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('openings')
                    ->searchable()
                    ->required()
                    ->options(Opening::where('user_id', auth()->user()->id)->pluck('name', 'id'))
                    ->createOptionForm([
                        TextInput::make('name')
                            ->label('Opening Name')
                            ->required(),
                        Select::make('is_white')
                            ->label('Play as...')
                            ->options([
                                1 => 'White',
                                0 => 'Black',
                            ])
                            ->required(),
                    ])->createOptionUsing(function ($data) {
                        $opening = Opening::updateOrCreate([
                            'name' => $data['name'],
                            'is_white' => $data['is_white'],
                        ], [
                            'user_id' => auth()->id(),
                        ]);

                        if($opening->is_white) {
                            $this->playAsWhite = true;
                        } else {
                            $this->playAsWhite = false;
                        }

                        Notification::make()->title('Opening Created. Start Training')->success()->send();
                    })
            ])
            ->statePath('formData');
    }

    public
    function updateProbability($id, $probability)
    {
        $possibleMove = PossibleMove::find($id);
        $possibleMove->probability = $probability;
        $possibleMove->save();
    }

    public function setOpening()
    {
        $this->opening = Opening::find($this->formData['openings']);
        if($this->opening->is_white) {
            $this->playAsWhite = true;
        } else {
            $this->playAsWhite = false;
        }
        Notification::make()->title('Opening Set. Start Training')->success()->send();
    }

    public
    function createAndSetOpening()
    {
        $this->validate([
            'newOpeningName' => 'required',
        ]);

        $opening = Opening::updateOrCreate([
            'name' => $this->newOpeningName,
        ], [
            'user_id' => auth()->id(),
        ]);

        $this->opening = $opening;
    }

    public
    function move($fromFen, $toFen, $moveFrom, $moveTo, $color, $notation)
    {
        if ($this->recording) {
            // if playing as white and this is a white move, save as correct move
            if ($this->playAsWhite && $color === 'white') {
                CorrectMove::updateOrCreate([
                    'is_white' => 1,
                    'from_fen' => $fromFen,
                    'opening_id' => $this->opening->id,
                    'user_id' => auth()->id(),
                ], [
                    'to_fen' => $toFen,
                    'move_from' => $moveFrom,
                    'move_to' => $moveTo,
                    'notation' => $notation,
                ]);

                $this->possibleMoves = PossibleMove::where('is_white', 1)
                    ->where('fen', $toFen)
                    ->where('opening_id', $this->opening->id)
                    ->where('user_id', auth()->id())
                    ->get();

                ray($this->possibleMoves);

            }
            // if playing as white and this is a black move, save as possible move
            if ($this->playAsWhite && $color === 'black') {
                PossibleMove::updateOrCreate([
                    'is_white' => 1,
                    'fen' => $fromFen,
                    'move_from' => $moveFrom,
                    'move_to' => $moveTo,
                    'notation' => $notation,
                    'opening_id' => $this->opening->id,
                    'user_id' => auth()->id(),
                ]);

                $this->possibleMoves = PossibleMove::where('is_white', 1)
                    ->where('fen', $fromFen)
                    ->where('opening_id', $this->opening->id)
                    ->where('user_id', auth()->id())
                    ->get();
            }
            // if playing as black and this is a black move, save as correct move
            // if playing as black and this is a white move, save as possible move
//            PossibleMove::firstOrCreate([
//                'fen' => $fen,
//            ], [
//
//            ]);
        } else {
            // check if this move was correct (or maybe that's already done on the front end)
            // if it was correct, make the next move or show a success message if this is the end
            $correctMove = CorrectMove::where('from_fen', $fromFen)
                ->where('is_white', $this->playAsWhite)
                ->where('user_id', auth()->id())
                ->get()->first();

            if ($correctMove) {
                $this->correctMove = $correctMove;
                if ($correctMove->to_fen === $toFen) {
                    $this->wrongMove = false;
                    $this->possibleMoves = PossibleMove::where('is_white', $this->playAsWhite)
                        ->where('fen', $toFen)
                        ->where('user_id', auth()->id())
                        ->orderBy('probability', 'desc')
                        ->get();
                    // randomly pick a possible move based on probability
                    $totalProbability = $this->possibleMoves->sum('probability');
                    $randomNumber = rand(0, $totalProbability);
                    foreach ($this->possibleMoves as $move) {
                        $randomNumber -= $move->probability;
                        if ($randomNumber <= 0) {
                            $this->dispatchBrowserEvent('next', ['notation' => $move->notation]);
//                            $this->dispatchBrowserEvent('next', ['fen' => $move->fen]);
//            $this->dispatchBrowserEvent('next', ['fen' => 'rnbqkbnr/pppp1ppp/8/4p3/4P3/8/PPPP1PPP/RNBQKBNR w KQkq e6 0 2']);

                            break;
                        }
                    }

                } else {
                    $this->wrongMove = true;
                }
            } else {
                $this->correctMove = null;
            }

//            $this->dispatchBrowserEvent('next', ['fen' => 'rnbqkbnr/pppp1ppp/8/4p3/4P3/8/PPPP1PPP/RNBQKBNR w KQkq e6 0 2']);
        }
        $correctMove = CorrectMove::query()->where('from_fen', $toFen)
            ->where('is_white', $this->playAsWhite)->where('user_id', auth()->id())->get()->first();

        if ($correctMove) {
            $this->correctMove = $correctMove;
        } else {
            $this->correctMove = null;
        }
    }

    public
    function render()
    {
        return view('livewire.openings');
    }
}
