<?php
declare(strict_types=1);

namespace App\VendorService\CardParseTraits;

use App\Helper\Constant\CardCode\DiceCode;
use App\Helper\Constant\CardCode\MahjongCode;
use App\Helper\Constant\CardCode\PaigowCode;
use App\Helper\Constant\CardCode\PokerCode;

trait KYLCCardParse
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

    private function parseMahjongCode($word): string
    {
        $length = mb_strlen($word);
        if ($length == 1) {
            switch ($word) {
                case '东': return MahjongCode::HONOUR . MahjongCode::EAST;
                case '南': return MahjongCode::HONOUR . MahjongCode::SOUTH;
                case '西': return MahjongCode::HONOUR . MahjongCode::WEST;
                case '北': return MahjongCode::HONOUR . MahjongCode::NORTH;
                case '中': return MahjongCode::HONOUR . MahjongCode::RED;
                case '發': return MahjongCode::HONOUR . MahjongCode::GREEN;
                case '白': return MahjongCode::HONOUR . MahjongCode::WHITE;
                case '梅': return MahjongCode::FLOWER . MahjongCode::PLUM;
                case '兰': return MahjongCode::FLOWER . MahjongCode::ORCHID;
                case '竹': return MahjongCode::FLOWER . MahjongCode::BAMBOO;
                case '菊': return MahjongCode::FLOWER . MahjongCode::CHRYSANTHEMUM;
                case '春': return MahjongCode::FLOWER . MahjongCode::SPRING;
                case '夏': return MahjongCode::FLOWER . MahjongCode::SUMMER;
                case '秋': return MahjongCode::FLOWER . MahjongCode::AUTUMN;
                case '冬': return MahjongCode::FLOWER . MahjongCode::WINTER;
                default: break;
            }
        } else if ($length == 2) {
            $words = mb_str_split($word);
            $number = current($words);
            $suit = end($words);
            switch ($suit) {
                case '万': $suit = MahjongCode::WAN; break;
                case '条': $suit = MahjongCode::BAMS; break;
                case '筒': $suit = MahjongCode::DOT; break;
                case '风': $suit = MahjongCode::HONOUR; break;
                default: break;
            }

            switch ($number) {
                case '一': $number = MahjongCode::ONE; break;
                case '二': $number = MahjongCode::TWO; break;
                case '三': $number = MahjongCode::THREE; break;
                case '四': $number = MahjongCode::FOUR; break;
                case '五': $number = MahjongCode::FIVE; break;
                case '六': $number = MahjongCode::SIX; break;
                case '七': $number = MahjongCode::SEVEN; break;
                case '八': $number = MahjongCode::EIGHT; break;
                case '九': $number = MahjongCode::NINE; break;
                case '东': $number = MahjongCode::EAST; break;
                case '南': $number = MahjongCode::SOUTH; break;
                case '西': $number = MahjongCode::WEST; break;
                case '北': $number = MahjongCode::NORTH; break;
                default: break;
            }
            return $suit . $number;
        } else {
            return '';
        }
    }

    private function parseDiceCode(string $number): string
    {
        switch ($number) {
            case '1': $number = DiceCode::ONE; break;
            case '2': $number = DiceCode::TWO; break;
            case '3': $number = DiceCode::THREE; break;
            case '4': $number = DiceCode::FOUR; break;
            case '5': $number = DiceCode::FIVE; break;
            case '6': $number = DiceCode::SIX; break;
            default: break;
        }
        return $number;
    }

    private function parsePaigowCode(string $number): string
    {
        switch ($number) {
            case '11': $number = PaigowCode::ONE_ONE; break;
            case '12': $number = PaigowCode::ONE_TWO; break;
            case '13': $number = PaigowCode::ONE_THREE; break;
            case '14': $number = PaigowCode::ONE_FOUR; break;
            case '15': $number = PaigowCode::ONE_FIVE; break;
            case '16': $number = PaigowCode::ONE_SIX; break;
            case '22': $number = PaigowCode::TWO_TWO; break;
            case '23': $number = PaigowCode::TWO_THREE; break;
            case '24': $number = PaigowCode::TWO_FOUR; break;
            case '25': $number = PaigowCode::TWO_FIVE; break;
            case '26': $number = PaigowCode::TWO_SIX; break;
            case '33': $number = PaigowCode::THREE_THREE; break;
            case '34': $number = PaigowCode::THREE_FOUR; break;
            case '35': $number = PaigowCode::THREE_FIVE; break;
            case '36': $number = PaigowCode::THREE_SIX; break;
            case '44': $number = PaigowCode::FOUR_FOUR; break;
            case '45': $number = PaigowCode::FOUR_FIVE; break;
            case '46': $number = PaigowCode::FOUR_SIX; break;
            case '55': $number = PaigowCode::FIVE_FIVE; break;
            case '56': $number = PaigowCode::FIVE_SIX; break;
            case '66': $number = PaigowCode::SIX_SIX; break;
            default: break;
        }
        return $number;
    }
}