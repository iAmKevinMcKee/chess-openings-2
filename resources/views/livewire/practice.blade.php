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
        <div id="chess-board"
             wire:ignore
             x-on:reset.window="
             chessjs = new Chess();
             selectedSquare = null;
            previouslySelectedSquare = null;
            possibleMoves = [];
             chessjs.load('rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1');
             updateBoard();
            "
             x-on:next.window="
                chessjs.move($event.detail.notation);
                updateBoard();
                "
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

            if(chessjs.get(previouslySelectedSquare).type === 'k'
                && chessjs.get(previouslySelectedSquare).color === 'b'
                && selectedSquare === 'g8') {
                console.log('black king side castling');
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

            if(chessjs.get(previouslySelectedSquare).type === 'k'
                && chessjs.get(previouslySelectedSquare).color === 'b'
                && selectedSquare === 'c8') {
                console.log('black queen side castling');
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
             x-ref="board" id="chess-board"
             class="w-[640px] h-[640px]" :class="{ 'rotate-180' : ! playAsWhite}">
            <div class="grid grid-cols-8 h-[80px] w-full">
                <div x-ref="a8" class="bg-gray-300 odd:bg-white border border-gray-800"
                     :class="{'rotate-180' : ! playAsWhite}"></div>
                <div x-ref="b8" class="bg-gray-300 odd:bg-white border border-gray-800"
                     :class="{'rotate-180' : ! playAsWhite}"></div>
                <div x-ref="c8" class="bg-gray-300 odd:bg-white border border-gray-800"
                     :class="{'rotate-180' : ! playAsWhite}"></div>
                <div x-ref="d8" class="bg-gray-300 odd:bg-white border border-gray-800"
                     :class="{'rotate-180' : ! playAsWhite}"></div>
                <div x-ref="e8" class="bg-gray-300 odd:bg-white border border-gray-800"
                     :class="{'rotate-180' : ! playAsWhite}"></div>
                <div x-ref="f8" class="bg-gray-300 odd:bg-white border border-gray-800"
                     :class="{'rotate-180' : ! playAsWhite}"></div>
                <div x-ref="g8" class="bg-gray-300 odd:bg-white border border-gray-800"
                     :class="{'rotate-180' : ! playAsWhite}"></div>
                <div x-ref="h8" class="bg-gray-300 odd:bg-white border border-gray-800"
                     :class="{'rotate-180' : ! playAsWhite}"></div>
            </div>
            <div class="grid grid-cols-8 h-[80px] w-full">
                <div x-ref="a7" class="bg-gray-300 even:bg-white border border-gray-800"
                     :class="{'rotate-180' : ! playAsWhite}"></div>
                <div x-ref="b7" class="bg-gray-300 even:bg-white border border-gray-800"
                     :class="{'rotate-180' : ! playAsWhite}"></div>
                <div x-ref="c7" class="bg-gray-300 even:bg-white border border-gray-800"
                     :class="{'rotate-180' : ! playAsWhite}"></div>
                <div x-ref="d7" class="bg-gray-300 even:bg-white border border-gray-800"
                     :class="{'rotate-180' : ! playAsWhite}"></div>
                <div x-ref="e7" class="bg-gray-300 even:bg-white border border-gray-800"
                     :class="{'rotate-180' : ! playAsWhite}"></div>
                <div x-ref="f7" class="bg-gray-300 even:bg-white border border-gray-800"
                     :class="{'rotate-180' : ! playAsWhite}"></div>
                <div x-ref="g7" class="bg-gray-300 even:bg-white border border-gray-800"
                     :class="{'rotate-180' : ! playAsWhite}"></div>
                <div x-ref="h7" class="bg-gray-300 even:bg-white border border-gray-800"
                     :class="{'rotate-180' : ! playAsWhite}"></div>
            </div>
            <div class="grid grid-cols-8 h-[80px] w-full">
                <div x-ref="a6" class="bg-gray-300 odd:bg-white border border-gray-800"
                     :class="{'rotate-180' : ! playAsWhite}"></div>
                <div x-ref="b6" class="bg-gray-300 odd:bg-white border border-gray-800"
                     :class="{'rotate-180' : ! playAsWhite}"></div>
                <div x-ref="c6" class="bg-gray-300 odd:bg-white border border-gray-800"
                     :class="{'rotate-180' : ! playAsWhite}"></div>
                <div x-ref="d6" class="bg-gray-300 odd:bg-white border border-gray-800"
                     :class="{'rotate-180' : ! playAsWhite}"></div>
                <div x-ref="e6" class="bg-gray-300 odd:bg-white border border-gray-800"
                     :class="{'rotate-180' : ! playAsWhite}"></div>
                <div x-ref="f6" class="bg-gray-300 odd:bg-white border border-gray-800"
                     :class="{'rotate-180' : ! playAsWhite}"></div>
                <div x-ref="g6" class="bg-gray-300 odd:bg-white border border-gray-800"
                     :class="{'rotate-180' : ! playAsWhite}"></div>
                <div x-ref="h6" class="bg-gray-300 odd:bg-white border border-gray-800"
                     :class="{'rotate-180' : ! playAsWhite}"></div>
            </div>
            <div class="grid grid-cols-8 h-[80px] w-full">
                <div x-ref="a5" class="bg-gray-300 even:bg-white border border-gray-800"
                     :class="{'rotate-180' : ! playAsWhite}"></div>
                <div x-ref="b5" class="bg-gray-300 even:bg-white border border-gray-800"
                     :class="{'rotate-180' : ! playAsWhite}"></div>
                <div x-ref="c5" class="bg-gray-300 even:bg-white border border-gray-800"
                     :class="{'rotate-180' : ! playAsWhite}"></div>
                <div x-ref="d5" class="bg-gray-300 even:bg-white border border-gray-800"
                     :class="{'rotate-180' : ! playAsWhite}"></div>
                <div x-ref="e5" class="bg-gray-300 even:bg-white border border-gray-800"
                     :class="{'rotate-180' : ! playAsWhite}"></div>
                <div x-ref="f5" class="bg-gray-300 even:bg-white border border-gray-800"
                     :class="{'rotate-180' : ! playAsWhite}"></div>
                <div x-ref="g5" class="bg-gray-300 even:bg-white border border-gray-800"
                     :class="{'rotate-180' : ! playAsWhite}"></div>
                <div x-ref="h5" class="bg-gray-300 even:bg-white border border-gray-800"
                     :class="{'rotate-180' : ! playAsWhite}"></div>
            </div>
            <div class="grid grid-cols-8 h-[80px] w-full">
                <div x-ref="a4" class="bg-gray-300 odd:bg-white border border-gray-800"
                     :class="{'rotate-180' : ! playAsWhite}"></div>
                <div x-ref="b4" class="bg-gray-300 odd:bg-white border border-gray-800"
                     :class="{'rotate-180' : ! playAsWhite}"></div>
                <div x-ref="c4" class="bg-gray-300 odd:bg-white border border-gray-800"
                     :class="{'rotate-180' : ! playAsWhite}"></div>
                <div x-ref="d4" class="bg-gray-300 odd:bg-white border border-gray-800"
                     :class="{'rotate-180' : ! playAsWhite}"></div>
                <div x-ref="e4" class="bg-gray-300 odd:bg-white border border-gray-800"
                     :class="{'rotate-180' : ! playAsWhite}"></div>
                <div x-ref="f4" class="bg-gray-300 odd:bg-white border border-gray-800"
                     :class="{'rotate-180' : ! playAsWhite}"></div>
                <div x-ref="g4" class="bg-gray-300 odd:bg-white border border-gray-800"
                     :class="{'rotate-180' : ! playAsWhite}"></div>
                <div x-ref="h4" class="bg-gray-300 odd:bg-white border border-gray-800"
                     :class="{'rotate-180' : ! playAsWhite}"></div>
            </div>
            <div class="grid grid-cols-8 h-[80px] w-full">
                <div x-ref="a3" class="bg-gray-300 even:bg-white border border-gray-800"
                     :class="{'rotate-180' : ! playAsWhite}"></div>
                <div x-ref="b3" class="bg-gray-300 even:bg-white border border-gray-800"
                     :class="{'rotate-180' : ! playAsWhite}"></div>
                <div x-ref="c3" class="bg-gray-300 even:bg-white border border-gray-800"
                     :class="{'rotate-180' : ! playAsWhite}"></div>
                <div x-ref="d3" class="bg-gray-300 even:bg-white border border-gray-800"
                     :class="{'rotate-180' : ! playAsWhite}"></div>
                <div x-ref="e3" class="bg-gray-300 even:bg-white border border-gray-800"
                     :class="{'rotate-180' : ! playAsWhite}"></div>
                <div x-ref="f3" class="bg-gray-300 even:bg-white border border-gray-800"
                     :class="{'rotate-180' : ! playAsWhite}"></div>
                <div x-ref="g3" class="bg-gray-300 even:bg-white border border-gray-800"
                     :class="{'rotate-180' : ! playAsWhite}"></div>
                <div x-ref="h3" class="bg-gray-300 even:bg-white border border-gray-800"
                     :class="{'rotate-180' : ! playAsWhite}"></div>
            </div>
            <div class="grid grid-cols-8 h-[80px] w-full">
                <div x-ref="a2" class="bg-gray-300 odd:bg-white border border-gray-800"
                     :class="{'rotate-180' : ! playAsWhite}"></div>
                <div x-ref="b2" class="bg-gray-300 odd:bg-white border border-gray-800"
                     :class="{'rotate-180' : ! playAsWhite}"></div>
                <div x-ref="c2" class="bg-gray-300 odd:bg-white border border-gray-800"
                     :class="{'rotate-180' : ! playAsWhite}"></div>
                <div x-ref="d2" class="bg-gray-300 odd:bg-white border border-gray-800"
                     :class="{'rotate-180' : ! playAsWhite}"></div>
                <div x-ref="e2" class="bg-gray-300 odd:bg-white border border-gray-800"
                     :class="{'rotate-180' : ! playAsWhite}"></div>
                <div x-ref="f2" class="bg-gray-300 odd:bg-white border border-gray-800"
                     :class="{'rotate-180' : ! playAsWhite}"></div>
                <div x-ref="g2" class="bg-gray-300 odd:bg-white border border-gray-800"
                     :class="{'rotate-180' : ! playAsWhite}"></div>
                <div x-ref="h2" class="bg-gray-300 odd:bg-white border border-gray-800"
                     :class="{'rotate-180' : ! playAsWhite}"></div>
            </div>
            <div class="grid grid-cols-8 h-[80px] w-full">
                <div x-ref="a1" class="bg-gray-300 even:bg-white border border-gray-800"
                     :class="{'rotate-180' : ! playAsWhite}"></div>
                <div x-ref="b1" class="bg-gray-300 even:bg-white border border-gray-800"
                     :class="{'rotate-180' : ! playAsWhite}"></div>
                <div x-ref="c1" class="bg-gray-300 even:bg-white border border-gray-800"
                     :class="{'rotate-180' : ! playAsWhite}"></div>
                <div x-ref="d1" class="bg-gray-300 even:bg-white border border-gray-800"
                     :class="{'rotate-180' : ! playAsWhite}"></div>
                <div x-ref="e1" class="bg-gray-300 even:bg-white border border-gray-800"
                     :class="{'rotate-180' : ! playAsWhite}"></div>
                <div x-ref="f1" class="bg-gray-300 even:bg-white border border-gray-800"
                     :class="{'rotate-180' : ! playAsWhite}"></div>
                <div x-ref="g1" class="bg-gray-300 even:bg-white border border-gray-800"
                     :class="{'rotate-180' : ! playAsWhite}"></div>
                <div x-ref="h1" class="bg-gray-300 even:bg-white border border-gray-800"
                     :class="{'rotate-180' : ! playAsWhite}"></div>
            </div>
        </div>
        <div class="flex flex-col justify-between items-center flex-1">
            @if($trainingSession)
                <div id="training-stats" class="w-full px-4">
                    <dl class="mt-5 grid grid-cols-1 divide-y divide-gray-200 overflow-hidden rounded-lg bg-white shadow md:grid-cols-3 md:divide-x md:divide-y-0">
                        <div class="px-4 py-5 sm:p-6">
                            <dt class="text-base font-normal text-gray-900">Correct</dt>
                            <dd class="mt-1 flex items-baseline justify-between md:block lg:flex">
                                <div class="flex items-baseline text-2xl font-semibold text-indigo-600">
                                    {{ $trainingSession->correct ?? 0 }}
                                </div>
                            </dd>
                        </div>

                        <div class="px-4 py-5 sm:p-6">
                            <dt class="text-base font-normal text-gray-900">Incorrect</dt>
                            <dd class="mt-1 flex items-baseline justify-between md:block lg:flex">
                                <div class="flex items-baseline text-2xl font-semibold text-indigo-600">
                                    {{ $trainingSession->incorrect ?? 0 }}
                                </div>
                            </dd>
                        </div>

                        <div class="px-4 py-5 sm:p-6">
                            <dt class="text-base font-normal text-gray-900">Percent</dt>
                            <dd class="mt-1 flex items-baseline justify-between md:block lg:flex">
                                <div class="flex items-baseline text-2xl font-semibold text-indigo-600">
                                    {{ number_format($trainingSession->percentCorrect(), 2) }}%
                                </div>
                            </dd>
                        </div>
                    </dl>o
                    @if($attempt)
                        <h3 class="text-2xl text-center mt-2 font-semibold leading-6 text-gray-900">{{$attempt->opening->name}}</h3>
                    @endif
                    @if($wrongMove)
                        <div class="w-full text-center mt-3 mb-2">
                            <div class="text-red-500">Wrong!</div>
                        </div>
                        <div class="text-center w-full">Correct Move</div>
                        @if($correctMoveNotation)
                            <div class="text-center w-full">{{ $correctMoveNotation }}</div>
                        @endif
                    @endif
                </div>
            @endif
            @if(count($this->openings) == 0)
                <form wire:submit.prevent="setOpenings"
                      class="w-full px-3 flex flex-col h-full pb-3 justify-between">
                    <div>
                        {{ $this->form }}
                    </div>

                    <x-filament::button type="submit">
                        Set Openings
                    </x-filament::button>
                </form>
            @endif

            <div>
                @if($this->openings && is_null($this->attempt))
                    <x-filament::button wire:click="startAttempt">Start</x-filament::button>
                @endif
            </div>

            <div>
                @if($wrongMove)
                    <x-filament::button wire:click="startAttempt">Play Again</x-filament::button>
                @endif
            </div>
        </div>
    </div>
    <x-filament-actions::modals/>

</div>
