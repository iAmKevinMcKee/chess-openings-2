<?php

namespace App\Http\Livewire;

use App\Models\PossibleMove;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

class PossibleMovesTable extends Component implements HasTable
{
    use InteractsWithTable;

    public $openingId;
    public $currentFen;

    protected $listeners = ['setFen' => 'setFen'];

    public function mount($openingId, $currentFen)
    {
        $this->openingId = $openingId;
        $this->currentFen = $currentFen;
    }

    public function setFen($fen)
    {
        $this->currentFen = $fen;
    }

    public function table(Table $table): Table
    {
        if($this->openingId) {
            $query = PossibleMove::where('opening_id', $this->openingId)->where('from_fen', $this->currentFen);
        } else {
            $query = PossibleMove::query();
        }
        return $table->query(function() use ($query) { return $query; })
            ->defaultSort('probability', 'desc')
            ->columns([
                TextColumn::make('notation')->label('Move'),
                TextInputColumn::make('probability')->sortable(),
            ]);
    }

    public function render(): View
    {
        return view('livewire.possible-moves-table');
    }
}
