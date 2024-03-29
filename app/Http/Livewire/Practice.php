<?php

namespace App\Http\Livewire;

use App\Models\Attempt;
use App\Models\CorrectMove;
use App\Models\Opening;
use App\Models\PossibleMove;
use App\Models\TrainingSession;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Livewire\Component;

class Practice extends Component implements HasForms
{
    use InteractsWithForms;

    public $formData;
    public $attempt = null;
    public $trainingSession = null;
    public $correctMoveNotation;
    public $playAsWhite = false;
    public $wrongMove = null;
    public $openings = [];
    public $correctMove = null;
    public $computerMoves = [];
    public $retryComputerMoves = [];
    public $tryAgain = false;
    public $hintOne = null;
    public $hintTwo = null;

    public $movesCount = null;


    public function mount(): void
    {
//        $this->openings = session()->get('openings') ?? [];
        $this->form->fill([
            'openings' => $this->openings,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('is_white')
                    ->label('Play as')
                    ->required()
                    ->reactive()
                    ->options([
                        1 => 'White',
                        0 => 'Black',
                    ]),
                Select::make('openings')
                    ->searchable()
                    ->multiple()
                    ->required()
                    ->reactive()
                    ->options(function ($get) {
                        if (!is_null($get('is_white'))) {
                            return Opening::where('user_id', auth()->user()->id)
                                ->where('is_white', $get('is_white'))
                                ->pluck('name', 'id');
                        }
                        return [];
                    }),
                Select::make('moves_count')
                    ->label('Number of moves')
                    ->helperText('If you want an opening to be correct after a certain number of moves, change the value here')
                    ->options([
                        1 => '1',
                        2 => '2',
                        3 => '3',
                        4 => '4',
                        5 => '5',
                        6 => '6',
                        7 => '7',
                        8 => '8',
                        9 => '9',
                        10 => '10',
                    ]),
            ])
            ->statePath('formData');
    }

    public function setOpenings()
    {
        $form = $this->form->getState();
        if ($form['is_white'] == 1) {
            $this->playAsWhite = true;
        } else {
            $this->playAsWhite = false;
        }
        $this->openings = $form['openings'];
        session()->put('openings', $this->openings);

        $this->trainingSession = TrainingSession::create([
            'user_id' => auth()->user()->id,
        ]);

        $this->movesCount = $form['moves_count'];

        Notification::make()->success()->title('openings are set')->send();
        $this->startAttempt();
    }

    public function endSession()
    {
        $this->trainingSession = null;
        $this->openings = [];
    }

    public function newAttempt()
    {
        $this->tryAgain = false;
        $this->startAttempt();
    }

    public function retry()
    {
        $this->tryAgain = true;
        $this->startAttempt(true);
    }

    public function startAttempt($retry = false)
    {
        if(! $retry) {
            $this->tryAgain = false;
        } else {
            $this->retryComputerMoves = $this->computerMoves;
        }

        $this->computerMoves = [];

        $this->dispatchBrowserEvent('reset');
        $this->wrongMove = false;
        $this->correctMoveNotation = null;
        $openingId = collect($this->openings)->random();
        $this->attempt = Attempt::create([
            'user_id' => auth()->user()->id,
            'opening_id' => (int)$openingId,
            'training_session_id' => $this->trainingSession->id,
        ]);

        if ($this->playAsWhite == false) {
            $this->setPossibleMoves('rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1');
            $move = $this->randomlyPickAPossibleMoveBasedOnProbability();
            if ($move) {
                $this->computerMoves[] = $move->id;
                $this->correctMove = $move->correctMove;
                $this->dispatchBrowserEvent('next', ['notation' => $move->notation]);
            }
        } else {
            $this->correctMove = CorrectMove::where('opening_id', $openingId)
                ->where('from_fen', 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1')
                ->get()->first();
        }
    }

    public function showHintOne($fromFen)
    {
        $correctMove = CorrectMove::where('from_fen', $fromFen)
            ->where('opening_id', $this->attempt->opening_id)
            ->where('user_id', auth()->id())
            ->get()->first();

        $this->hintOne = $correctMove->hint_one;
    }

    public function move($fromFen, $toFen, $moveFrom, $moveTo, $color, $notation)
    {
        if ($this->correctMove->to_fen === $toFen) {
            // if the move is correct, check if there are any possible moves for the new position
            $this->attempt->attempt_moves()->create([
                'move_number' => 1, // TODO fix this once I can get online to check the chessjs docs
                'notation' => $notation,
                'correct' => 1,
            ]);
            $this->wrongMove = false;
            // if you've hit the moves count limit, then the round is over and the user has won
            if ($this->movesCount && $this->attempt->attempt_moves()->count() >= $this->movesCount) {
                $this->attempt->update([
                    'correct' => 1,
                ]);
                $this->trainingSession->update([
                    'correct' => $this->trainingSession->correct + 1,
                ]);

                $this->startAttempt();
                return;
            }
            $this->setPossibleMoves($toFen);
            if ($this->possibleMoves->count() === 0) {
                // if no possible moves, then the round is over and the user has won
                $this->attempt->update([
                    'correct' => 1,
                ]);
                $this->trainingSession->update([
                    'correct' => $this->trainingSession->correct + 1,
                ]);

                $this->startAttempt();
            } else {
                // if there are possible moves, then randomly pick one based on probability
                $move = $this->randomlyPickAPossibleMoveBasedOnProbability();
                if ($move->correctMove) {
                    $this->computerMoves[] = $move->id;
                    $this->correctMove = $move->correctMove;
                    $this->dispatchBrowserEvent('next', ['notation' => $move->notation]);
                } else {
                    $this->attempt->update([
                        'correct' => 1,
                    ]);
                    $this->trainingSession->update([
                        'correct' => $this->trainingSession->correct + 1,
                    ]);

                    $this->startAttempt();
                }
            }
        } else {
            $this->wrongMove = true;
            $this->correctMoveNotation = $this->correctMove->notation;
            // move is incorrect, show a message that it was not correct and show the correct move
            $this->attempt->attempt_moves()->create([
                'move_number' => 1, // fix this once I can get online to check the chessjs docs
                'notation' => $notation,
                'correct' => 0,
            ]);

            $this->attempt->update([
                'correct' => 0,
            ]);
            $this->trainingSession->update([
                'incorrect' => $this->trainingSession->incorrect + 1,
            ]);

        }
    }

    public function render()
    {
        return view('livewire.practice');
    }

    private function randomlyPickAPossibleMoveBasedOnProbability(): PossibleMove|null
    {
        ray($this->retryComputerMoves);
        if(count($this->retryComputerMoves) > 0 && $this->tryAgain) {
            // get the id of the first move from the computer moves array
            $id = $this->retryComputerMoves[0];
            // remove the first element from the array
            array_shift($this->retryComputerMoves);
            // return the moves
            $possibleMove = PossibleMove::find($id);
            return $possibleMove;
        }
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

    private function setPossibleMoves($toFen): void
    {
        $this->possibleMoves = PossibleMove::where('is_white', $this->playAsWhite)
            ->where('opening_id', $this->attempt->opening_id)
            ->where('from_fen', $toFen)
            ->where('user_id', auth()->id())
            ->orderBy('probability', 'desc')
            ->get();
    }
}
