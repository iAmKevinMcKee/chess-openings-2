<?php

namespace App\Http\Livewire;

use App\Models\CorrectMove;
use App\Models\LichessPossibleMoves;
use App\Models\Opening;
use App\Models\PossibleMove;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;

class Openings extends Component implements HasForms, HasTable
{
    use InteractsWithForms, InteractsWithTable;

    public $playAsWhite = false;
    public $opening = null;

    public $possibleMoves = null;

    public $newOpeningName = '';

    public $correctMove;
    public $showLichess = false;

    public $formData = [];
    public $hintsFormData = [];
    public $currentFen = 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1';

    public $lichessPossibleMoves;

    public function mount()
    {
        if ($this->playAsWhite) {
            $correctMove = CorrectMove::query()->where('from_fen', 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1')
                ->where('is_white', $this->playAsWhite)->where('user_id', auth()->id())->get()->first();

            if ($correctMove) {
                $this->correctMove = $correctMove;
            } else {
                $this->correctMove = null;
            }
        }
        $this->openingsForm->fill();
        $this->hintsForm->fill();
    }

    protected function getForms(): array
    {
        return [
            'openingsForm',
            'hintsForm',
        ];
    }

    public function openingsForm(Form $form): Form
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
                            ->label('Play as')
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

                        if ($opening->is_white) {
                            $this->playAsWhite = true;
                        } else {
                            $this->playAsWhite = false;
                        }

                        $this->opening = $opening;

                        Notification::make()->title('Opening Created. Start Training')->success()->send();
                    })
            ])
            ->statePath('formData');
    }

    public function hintsForm(Form $form): Form
    {
        return $form
            ->schema([
                RichEditor::make('hint_one')
                    ->toolbarButtons(['bold', 'italic', 'bulletList'])
                    ->label('First Hint'),
                RichEditor::make('hint_two')
                    ->toolbarButtons(['bold', 'italic', 'bulletList'])
                    ->label('Second Hint'),
            ])
            ->statePath('hintsFormData');
    }

    public function saveHints()
    {
        $data = $this->hintsForm->getState();
        $this->correctMove->update([
            'hint_one' => $data['hint_one'],
            'hint_two' => $data['hint_two'],
        ]);

        Notification::make()->title('Hints Saved')->success()->send();
    }

    public function updateProbability($id, $probability)
    {
        $possibleMove = PossibleMove::find($id);
        $possibleMove->probability = $probability;
        $possibleMove->save();
    }

    public function setOpening()
    {
        $this->opening = Opening::find($this->formData['openings']);
        if ($this->opening->is_white) {
            $this->playAsWhite = true;
        } else {
            $this->playAsWhite = false;
        }

        if($this->playAsWhite) {
            $correctMove = CorrectMove::query()->where('from_fen', 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1')
                ->where('is_white', $this->playAsWhite)->where('user_id', auth()->id())->get()->first();

            if ($correctMove) {
                $this->correctMove = $correctMove;
            } else {
                $this->correctMove = null;
            }
        } else {
            $this->currentFen = 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1';
            $this->possibleMoves = $this->opening->possibleMoves()->where('from_fen', $this->currentFen)->get();
        }

        Notification::make()->title('Opening Set. Start Training')->success()->send();
    }

    public function createAndSetOpening()
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

    public function move($fromFen, $toFen, $moveFrom, $moveTo, $color, $notation)
    {
        $this->showLichess = false;
        $this->currentFen = $toFen;
        // if playing as white and this is a white move, save as correct move
        if ($this->playAsWhite && $color === 'white') {
            $correctMove = CorrectMove::updateOrCreate([
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

            $this->possibleMoves = $correctMove->possibleMoves;
            $this->correctMove = null;
            $this->hintsForm->fill();
        }
        // if playing as white and this is a black move, save as possible move
        if ($this->playAsWhite && $color === 'black') {
            $possibleMove = PossibleMove::updateOrCreate([
                'is_white' => 1,
                'from_fen' => $fromFen,
                'to_fen' => $toFen,
                'move_from' => $moveFrom,
                'move_to' => $moveTo,
                'notation' => $notation,
                'opening_id' => $this->opening->id,
                'user_id' => auth()->id(),
            ]);

            $this->possibleMoves = PossibleMove::where('is_white', 1)
                ->where('from_fen', $fromFen)
                ->where('opening_id', $this->opening->id)
                ->where('user_id', auth()->id())
                ->get();

            $this->correctMove = $possibleMove->correctMove;
            $this->hintsForm->fill([
                'hint_one' => $this->correctMove?->hint_one,
                'hint_two' => $this->correctMove?->hint_two,
            ]);
        }
        // if playing as black and this is a black move, save as correct move
        if ($this->playAsWhite == false && $color === 'black') {
            CorrectMove::updateOrCreate([
                'is_white' => 0,
                'from_fen' => $fromFen,
                'opening_id' => $this->opening->id,
                'user_id' => auth()->id(),
            ], [
                'to_fen' => $toFen,
                'move_from' => $moveFrom,
                'move_to' => $moveTo,
                'notation' => $notation,
            ]);

            $this->possibleMoves = PossibleMove::where('is_white', 0)
                ->where('from_fen', $toFen)
                ->where('opening_id', $this->opening->id)
                ->where('user_id', auth()->id())
                ->get();

            $this->correctMove = null;
            $this->hintsForm->fill();
        }
        // if playing as black and this is a white move, save as possible move
        if ($this->playAsWhite == false && $color !== 'black') {

            $possibleMove = PossibleMove::updateOrCreate([
                'is_white' => $this->playAsWhite,
                'from_fen' => $fromFen,
                'to_fen' => $toFen,
                'move_from' => $moveFrom,
                'move_to' => $moveTo,
                'notation' => $notation,
                'opening_id' => $this->opening->id,
                'user_id' => auth()->id(),
            ]);

            $this->possibleMoves = PossibleMove::where('is_white', $this->playAsWhite)
                ->where('from_fen', $fromFen)
                ->where('opening_id', $this->opening->id)
                ->where('user_id', auth()->id())
                ->get();

            $this->correctMove = $possibleMove->correctMove;
            $this->hintsForm->fill([
                'hint_one' => $this->correctMove?->hint_one,
                'hint_two' => $this->correctMove?->hint_two,
            ]);
        }
    }

    public function goBack($fen, $turn)
    {
        $this->showLichess = false;
        $this->currentFen = $fen;
        if($turn === 'b' && $this->playAsWhite === true) {
            $this->possiblemoves = $this->opening->possibleMoves()->where('from_fen', $fen)->get();
        } elseif ($turn === 'w' && $this->playAsWhite === true) {
            $this->correctMove = $this->opening->correctMoves()->where('from_fen', $fen)->get()->first();
            $this->hintsForm->fill([
                'hint_one' => $this->correctMove->hint_one,
                'hint_two' => $this->correctMove->hint_two,
            ]);
        } elseif ($turn === 'b' && $this->playAsWhite === false) {
            $this->correctMove = $this->opening->correctMoves()->where('from_fen', $fen)->get()->first();
            $this->hintsForm->fill([
                'hint_one' => $this->correctMove->hint_one,
                'hint_two' => $this->correctMove->hint_two,
            ]);
        } elseif ($turn === 'w' && $this->playAsWhite === false) {
            $this->possibleMoves = $this->opening->possibleMoves()->where('from_fen', $fen)->get();
        }
    }

    public function showLichess()
    {
        $this->showLichess = true;
    }

//    @var Table
    public function table(Table $table): Table
    {
        return $table
            ->query(function() {
                $currentFen = $this->currentFen;
                if($this->showLichess == false) {

                    $currentFen = '';
                }
                return LichessPossibleMoves::setFen($currentFen)->query();
            })
            ->columns([
                TextColumn::make('notation'),
                TextColumn::make('percent')->sortable(),
                TextColumn::make('white_wins')
            ])->defaultSort('percent', 'desc');
    }

    public function render()
    {
        return view('livewire.openings');
    }
}
