<div
    x-data="{
        playAsWhite: @entangle('playAsWhite'),
            chessjs: null,
            selectedSquare: null,
            previouslySelectedSquare: null,

            possibleMoves: [],
            loadFen(fen) {
                this.chessjs.load(fen);
                this.updateBoard();
            },
            resetBoard() {
                chessjs.load('rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1');
                updateBoard();
            },
            updateBoard() {
                this.selectedSquare = null;
                let squares = document.querySelectorAll('#chess-board > div > div');
                squares.forEach((square) => {
                    square.classList.remove('possible');
                    square.classList.remove('white-king');
                    square.classList.remove('white-queen');
                    square.classList.remove('white-rook');
                    square.classList.remove('white-bishop');
                    square.classList.remove('white-knight');
                    square.classList.remove('white-pawn');
                    square.classList.remove('black-king');
                    square.classList.remove('black-queen');
                    square.classList.remove('black-rook');
                    square.classList.remove('black-bishop');
                    square.classList.remove('black-knight');
                    square.classList.remove('black-pawn');
                });
                let board = this.chessjs.board();
                board.forEach((row) => row.forEach((item) => {
                if(item !== null) {
                    let className = item.color === 'w' ? 'white' : 'black';
                    if(item.type == 'q') {
                        className += '-queen';
                    } else if(item.type == 'k') {
                        className += '-king';
                    } else if(item.type == 'r') {
                        className += '-rook';
                    } else if(item.type == 'b') {
                        className += '-bishop';
                    } else if(item.type == 'n') {
                        className += '-knight';
                    } else if(item.type == 'p') {
                        className += '-pawn';
                    }
                    this.$refs[item.square].classList.add(className);
                    }
                }))
            }
        }"
    x-init="$nextTick(() => {
            chessjs = new Chess();
            chessjs.load('rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1');
            updateBoard();
        })"
    class="mt-8 text-2xl">
    <div class="flex">
        <div wire:ignore
             x-on:reset.window="
             chessjs = new Chess();
             selectedSquare = null;
            previouslySelectedSquare = null;
            possibleMoves = [];
             chessjs.load('rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1');
             updateBoard();
            "
             x-on:next.window="chessjs.move($event.detail.notation); updateBoard();"
             x-on:click="
        selectedSquare = $event.target.getAttribute('x-ref')
        console.log('selectedSquare: ' + selectedSquare);
        console.log('previouslySelectedSquare: ' + previouslySelectedSquare);
        if(selectedSquare === previouslySelectedSquare) {
            previouslySelectedSquare = selectedSquare;
            possibleMoves = chessjs.moves({square: selectedSquare});
            return;
        }

        console.log(possibleMoves);

        if(possibleMoves.length && Object.values(possibleMoves).includes('OO') === true) {
            if(chessjs.get(previouslySelectedSquare).type === 'k'
                && chessjs.get(previouslySelectedSquare).color === 'w'
                && selectedSquare === 'g1') {
                console.log('white king side castling');
                let previousFen = chessjs.fen();
                let color = chessjs.turn() === 'w' ? 'white' : 'black';
                chessjs.move('O-O');
                $wire.call('move', previousFen, chessjs.fen(), previouslySelectedSquare, selectedSquare, color, chessjs.history().slice(-1)[0] );
                console.log(chessjs.fen());
            }
        }

        if(possibleMoves.length && Object.values(possibleMoves).includes('OO') === true) {
            if(chessjs.get(previouslySelectedSquare).type === 'k'
                && chessjs.get(previouslySelectedSquare).color === 'w'
                && selectedSquare === 'c1') {
                console.log('white queen side castling');
                let previousFen = chessjs.fen();
                let color = chessjs.turn() === 'w' ? 'white' : 'black';
                chessjs.move('O-O-O');
                $wire.call('move', previousFen, chessjs.fen(), previouslySelectedSquare, selectedSquare, color, chessjs.history().slice(-1)[0] );
                console.log(chessjs.fen());
            }
        }

        if(possibleMoves.length && possibleMoves.includes(selectedSquare)) {
            let previousFen = chessjs.fen();
            let color = chessjs.turn() === 'w' ? 'white' : 'black';
            chessjs.move({from: previouslySelectedSquare, to: selectedSquare});
            $wire.call('move', previousFen, chessjs.fen(), previouslySelectedSquare, selectedSquare, color, chessjs.history().slice(-1)[0] );
            console.log(chessjs.fen());
        } else {
            possibleMoves = chessjs.moves({square: selectedSquare});
            if(possibleMoves.length > 0) {
                possibleMoves = possibleMoves.map((move) => {
                    return move.replace(/[^a-zA-Z0-9 ]/g, '').slice(-2);
                });
            }
        }
        previouslySelectedSquare = selectedSquare;
        updateBoard();
    "
             x-ref="board" id="chess-board" class="w-[640px] h-[640px]">
            <div class="grid grid-cols-8 h-[80px] w-full">
                <div x-ref="a8" class="bg-gray-300 odd:bg-white border border-gray-800"></div>
                <div x-ref="b8" class="bg-gray-300 odd:bg-white border border-gray-800"></div>
                <div x-ref="c8" class="bg-gray-300 odd:bg-white border border-gray-800"></div>
                <div x-ref="d8" class="bg-gray-300 odd:bg-white border border-gray-800"></div>
                <div x-ref="e8" class="bg-gray-300 odd:bg-white border border-gray-800"></div>
                <div x-ref="f8" class="bg-gray-300 odd:bg-white border border-gray-800"></div>
                <div x-ref="g8" class="bg-gray-300 odd:bg-white border border-gray-800"></div>
                <div x-ref="h8" class="bg-gray-300 odd:bg-white border border-gray-800"></div>
            </div>
            <div class="grid grid-cols-8 h-[80px] w-full">
                <div x-ref="a7" class="bg-gray-300 even:bg-white border border-gray-800"></div>
                <div x-ref="b7" class="bg-gray-300 even:bg-white border border-gray-800"></div>
                <div x-ref="c7" class="bg-gray-300 even:bg-white border border-gray-800"></div>
                <div x-ref="d7" class="bg-gray-300 even:bg-white border border-gray-800"></div>
                <div x-ref="e7" class="bg-gray-300 even:bg-white border border-gray-800"></div>
                <div x-ref="f7" class="bg-gray-300 even:bg-white border border-gray-800"></div>
                <div x-ref="g7" class="bg-gray-300 even:bg-white border border-gray-800"></div>
                <div x-ref="h7" class="bg-gray-300 even:bg-white border border-gray-800 white-queen"></div>
            </div>
            <div class="grid grid-cols-8 h-[80px] w-full">
                <div x-ref="a6" class="bg-gray-300 odd:bg-white border border-gray-800"></div>
                <div x-ref="b6" class="bg-gray-300 odd:bg-white border border-gray-800"></div>
                <div x-ref="c6" class="bg-gray-300 odd:bg-white border border-gray-800"></div>
                <div x-ref="d6" class="bg-gray-300 odd:bg-white border border-gray-800"></div>
                <div x-ref="e6" class="bg-gray-300 odd:bg-white border border-gray-800"></div>
                <div x-ref="f6" class="bg-gray-300 odd:bg-white border border-gray-800"></div>
                <div x-ref="g6" class="bg-gray-300 odd:bg-white border border-gray-800"></div>
                <div x-ref="h6" class="bg-gray-300 odd:bg-white border border-gray-800"></div>
            </div>
            <div class="grid grid-cols-8 h-[80px] w-full">
                <div x-ref="a5" class="bg-gray-300 even:bg-white border border-gray-800"></div>
                <div x-ref="b5" class="bg-gray-300 even:bg-white border border-gray-800"></div>
                <div x-ref="c5" class="bg-gray-300 even:bg-white border border-gray-800"></div>
                <div x-ref="d5" class="bg-gray-300 even:bg-white border border-gray-800"></div>
                <div x-ref="e5" class="bg-gray-300 even:bg-white border border-gray-800"></div>
                <div x-ref="f5" class="bg-gray-300 even:bg-white border border-gray-800"></div>
                <div x-ref="g5" class="bg-gray-300 even:bg-white border border-gray-800"></div>
                <div x-ref="h5" class="bg-gray-300 even:bg-white border border-gray-800"></div>
            </div>
            <div class="grid grid-cols-8 h-[80px] w-full">
                <div x-ref="a4" class="bg-gray-300 odd:bg-white border border-gray-800"></div>
                <div x-ref="b4" class="bg-gray-300 odd:bg-white border border-gray-800"></div>
                <div x-ref="c4" class="bg-gray-300 odd:bg-white border border-gray-800"></div>
                <div x-ref="d4" class="bg-gray-300 odd:bg-white border border-gray-800"></div>
                <div x-ref="e4" class="bg-gray-300 odd:bg-white border border-gray-800"></div>
                <div x-ref="f4" class="bg-gray-300 odd:bg-white border border-gray-800"></div>
                <div x-ref="g4" class="bg-gray-300 odd:bg-white border border-gray-800"></div>
                <div x-ref="h4" class="bg-gray-300 odd:bg-white border border-gray-800"></div>
            </div>
            <div class="grid grid-cols-8 h-[80px] w-full">
                <div x-ref="a3" class="bg-gray-300 even:bg-white border border-gray-800"></div>
                <div x-ref="b3" class="bg-gray-300 even:bg-white border border-gray-800"></div>
                <div x-ref="c3" class="bg-gray-300 even:bg-white border border-gray-800"></div>
                <div x-ref="d3" class="bg-gray-300 even:bg-white border border-gray-800"></div>
                <div x-ref="e3" class="bg-gray-300 even:bg-white border border-gray-800"></div>
                <div x-ref="f3" class="bg-gray-300 even:bg-white border border-gray-800"></div>
                <div x-ref="g3" class="bg-gray-300 even:bg-white border border-gray-800"></div>
                <div x-ref="h3" class="bg-gray-300 even:bg-white border border-gray-800"></div>
            </div>
            <div class="grid grid-cols-8 h-[80px] w-full">
                <div x-ref="a2" class="bg-gray-300 odd:bg-white border border-gray-800"></div>
                <div x-ref="b2" class="bg-gray-300 odd:bg-white border border-gray-800"></div>
                <div x-ref="c2" class="bg-gray-300 odd:bg-white border border-gray-800"></div>
                <div x-ref="d2" class="bg-gray-300 odd:bg-white border border-gray-800"></div>
                <div x-ref="e2" class="bg-gray-300 odd:bg-white border border-gray-800"></div>
                <div x-ref="f2" class="bg-gray-300 odd:bg-white border border-gray-800"></div>
                <div x-ref="g2" class="bg-gray-300 odd:bg-white border border-gray-800"></div>
                <div x-ref="h2" class="bg-gray-300 odd:bg-white border border-gray-800"></div>
            </div>
            <div class="grid grid-cols-8 h-[80px] w-full">
                <div x-ref="a1" class="bg-gray-300 even:bg-white border border-gray-800"></div>
                <div x-ref="b1" class="bg-gray-300 even:bg-white border border-gray-800"></div>
                <div x-ref="c1" class="bg-gray-300 even:bg-white border border-gray-800"></div>
                <div x-ref="d1" class="bg-gray-300 even:bg-white border border-gray-800"></div>
                <div x-ref="e1" class="bg-gray-300 even:bg-white border border-gray-800"></div>
                <div x-ref="f1" class="bg-gray-300 even:bg-white border border-gray-800"></div>
                <div x-ref="g1" class="bg-gray-300 even:bg-white border border-gray-800"></div>
                <div x-ref="h1" class="bg-gray-300 even:bg-white border border-gray-800"></div>
            </div>
        </div>
        <div class="justify-center flex-1">
            <form wire:submit.prevent="setOpenings">
                {{ $this->form }}

                <x-filament::button type="submit">
                    Set Openings
                </x-filament::button>
            </form>

            @if($this->openings)
                <x-filament::button wire:click="startAttempt">Start Attempt!!!</x-filament::button>
            @endif

            <div class="text-center w-full">Your Move</div>
            <button x-on:click="chessjs.undo(); updateBoard();">Go Back</button>
            <div x-text="JSON.stringify(selectedSquare)"></div>
            <div>
                @if($wrongMove)
                    <div>
                        <div class="text-red-500">Wrong!</div>
                    </div>
                    <div class="text-center w-full">Correct Move</div>
                    @if($correctMoveNotation)
                        <div class="text-center w-full">{{ $correctMoveNotation }}</div>
                    @endif
                @endif
            </div>
        </div>
    </div>
    <x-filament-actions::modals/>

</div>
