<?php

namespace App\Http\Livewire;

use App\Models\LichessPossibleMoves;
use Filament\Support\Contracts\TranslatableContentDriver;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

class LichessPossibleMovesTable extends Component implements HasTable
{
    use InteractsWithTable;

    public $currentFen;

    public function mount($currentFen)
    {
        $this->currentFen = $currentFen;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(LichessPossibleMoves::setFen($this->currentFen)->query())
            ->columns([
                TextColumn::make('notation'),
                TextColumn::make('percent')->sortable(),
                TextColumn::make('white_wins')
            ])->defaultSort('percent', 'desc');
    }

    public function render(): View
    {
        return view('livewire.lichess-possible-moves-table');
    }
}
