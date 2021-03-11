<?php
declare(strict_types=1);

namespace App\Helper;

use App\Helper\Constant\CardCode\PokerCode;

trait DetailTools
{
    /**
     * 計算牌點
     * @param string $game
     * @param array $cards
     * @return int
     */
    public function calculateCardPoint(string $game, array $cards): int
    {
        $point = 0;
        switch ($game) {
            case 'baccarat':
                foreach ($cards as $card) {
                    $number = substr($card, 1);
                    switch ($number) {
                        case PokerCode::ELEVEN:
                        case PokerCode::TWELVE:
                        case PokerCode::THIRTEEN:
                            $addend = 10;
                            break;
                        default:
                            $addend = intval($number);
                            break;
                    }
                    $point += $addend;
                }
                return $point % 10;
            case 'dragon_tiger':
                foreach ($cards as $card) {
                    $number = substr($card, 1);
                    switch ($number) {
                        default:
                            $addend = intval($number);
                            break;
                    }
                    $point += $addend;
                }
                return $point % 10;
            default:
                return $point;
        }
    }
}