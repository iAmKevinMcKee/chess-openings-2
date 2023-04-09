<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Sushi\Sushi;

class LichessPossibleMoves extends Model
{
    use Sushi;
    protected static $fen;

//    public function __construct($fen)
//    {
//        $this->fen = urlencode($fen);
//    }

    public static function setFen($fen)
    {
        self::$fen = urlencode($fen);
        return new static();
    }

    public function getRows()
    {
        if(self::$fen == '') {
            return [];
        }
        ray(self::$fen);
        $moves = \Illuminate\Support\Facades\Http::get('https://explorer.lichess.ovh/lichess?variant=standard&speeds=blitz,rapid,classical&ratings=1000,2000&fen=' . self::$fen);
        $total = $moves->json()['white'] + $moves->json()['black'] + $moves->json()['draws'];
        $moves = Arr::map($moves->json()['moves'], function ($move) use ($total) {
            return [
                'notation' => $move['san'],
                'total' => $move['white'] + $move['black'] + $move['draws'],
                'percent' => number_format((($move['white'] + $move['black'] + $move['draws']) / $total) * 100, 2),
                'white_wins' => number_format($move['white'] / ($move['white'] + $move['black'] + $move['draws']) * 100, 2),
                'black_wins' => number_format($move['black'] / ($move['white'] + $move['black'] + $move['draws']) * 100, 2),
                'draws' => number_format($move['draws'] / ($move['white'] + $move['black'] + $move['draws']) * 100, 2),
            ];
        });

        return $moves;
    }
}
