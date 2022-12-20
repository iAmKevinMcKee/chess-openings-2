<?php

namespace App\Http\Livewire;

use App\Models\CorrectMove;
use App\Models\Opening;
use App\Models\PossibleMove;
use Livewire\Component;

class Openings extends Component
{
    public $recording = true;
    public $playAsWhite = true;
    public $opening = null;

    public $possibleMoves = null;

    public $newOpeningName = '';

    public $correctMove;

    public function mount()
    {
        $this->opening = Opening::first() ?? new Opening();
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
        if($this->recording) {
            // if playing as white and this is a white move, save as correct move
            if($this->playAsWhite && $color === 'white') {
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
                    ->where('user_id', auth()->id())
                    ->get();

                ray($this->possibleMoves);

            }
            // if playing as white and this is a black move, save as possible move
            if($this->playAsWhite && $color === 'black') {
                PossibleMove::updateOrCreate([
                    'is_white' => 1,
                    'fen' => $fromFen,
                    'move_from' => $moveFrom,
                    'move_to' => $moveTo,
                    'user_id' => auth()->id(),
                ]);

                $this->possibleMoves = PossibleMove::where('is_white', 1)
                    ->where('fen', $fromFen)
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
            // if it was correct, make the next move or show a success message

            $this->dispatchBrowserEvent('next', ['fen' => 'rnbqkbnr/pppp1ppp/8/4p3/4P3/8/PPPP1PPP/RNBQKBNR w KQkq e6 0 2']);
        }
        $correctMove = CorrectMove::query()->where('from_fen', $toFen)
            ->where('is_white', $this->playAsWhite)->where('user_id', auth()->id())->get()->first();

        if($correctMove) {
            $this->correctMove = $correctMove;
        } else {
            $this->correctMove = null;
        }
    }
    public function render()
    {
        return view('livewire.openings');
    }
}
