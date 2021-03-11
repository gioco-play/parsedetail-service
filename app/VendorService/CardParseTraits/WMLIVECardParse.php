<?php
declare(strict_types=1);

namespace App\VendorService\CardParseTraits;

use App\Helper\Constant\CardCode\MahjongCode;
use App\Helper\Constant\CardCode\PokerCode;

trait WMLIVECardParse
{
    private function parsePokerCode(string $suit, string $number): string
    {
        switch ($suit) {
            case '♠': $suit = PokerCode::SPADE; break;
            case '♥': $suit = PokerCode::HEART; break;
            case '♦': $suit = PokerCode::DIAMOND; break;
            case '♣': $suit = PokerCode::CLUB; break;
            case '▲': $suit = PokerCode::JOKER; break;
            default: break;
        }

        switch ($number) {
            case '2': $number = PokerCode::TWO; break;
            case '3': $number = PokerCode::THREE; break;
            case '4': $number = PokerCode::FOUR; break;
            case '5': $number = PokerCode::FIVE; break;
            case '6': $number = PokerCode::SIX; break;
            case '7': $number = PokerCode::SEVEN; break;
            case '8': $number = PokerCode::EIGHT; break;
            case '9': $number = PokerCode::NINE; break;
            case '10': $number = PokerCode::TEN; break;
            case 'J':
            case '11': $number = PokerCode::ELEVEN; break;
            case 'Q':
            case '12': $number = PokerCode::TWELVE; break;
            case 'K':
            case '13': $number = PokerCode::THIRTEEN; break;
            case 'A':
            case '1': $number = PokerCode::ONE; break;
            default: break;
        }
        return $suit . $number;
    }

    private function parseMahjongCode(string $suit, string $number): string
    {
        switch ($suit) {
            case '万': $suit = MahjongCode::WAN; break;
            case '条': $suit = MahjongCode::BAMS; break;
            case '筒': $suit = MahjongCode::DOT; break;
            case '东': $suit = MahjongCode::HONOUR . MahjongCode::EAST; break;
            case '南': $suit = MahjongCode::HONOUR . MahjongCode::SOUTH; break;
            case '西': $suit = MahjongCode::HONOUR . MahjongCode::WEST; break;
            case '北': $suit = MahjongCode::HONOUR . MahjongCode::NORTH; break;
            case '中': $suit = MahjongCode::HONOUR . MahjongCode::RED; break;
            case '發': $suit = MahjongCode::HONOUR . MahjongCode::GREEN; break;
            case '白': $suit = MahjongCode::HONOUR . MahjongCode::WHITE; break;
            case '梅': $suit = MahjongCode::FLOWER . MahjongCode::PLUM; break;
            case '兰': $suit = MahjongCode::FLOWER . MahjongCode::ORCHID; break;
            case '竹': $suit = MahjongCode::FLOWER . MahjongCode::BAMBOO; break;
            case '菊': $suit = MahjongCode::FLOWER . MahjongCode::CHRYSANTHEMUM; break;
            case '春': $suit = MahjongCode::FLOWER . MahjongCode::SPRING; break;
            case '夏': $suit = MahjongCode::FLOWER . MahjongCode::SUMMER; break;
            case '秋': $suit = MahjongCode::FLOWER . MahjongCode::AUTUMN; break;
            case '冬': $suit = MahjongCode::FLOWER . MahjongCode::WINTER; break;
            default: $suit = ''; break;
        }

        switch ($number) {
            case '1': $number = MahjongCode::ONE; break;
            case '2': $number = MahjongCode::TWO; break;
            case '3': $number = MahjongCode::THREE; break;
            case '4': $number = MahjongCode::FOUR; break;
            case '5': $number = MahjongCode::FIVE; break;
            case '6': $number = MahjongCode::SIX; break;
            case '7': $number = MahjongCode::SEVEN; break;
            case '8': $number = MahjongCode::EIGHT; break;
            case '9': $number = MahjongCode::NINE; break;
            default: $number = ''; break;
        }
        return $suit . $number;
    }
}