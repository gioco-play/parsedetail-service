<?php
declare(strict_types=1);

namespace App\VendorService;

use App\Helper\Constant\CardCode\DiceCode;
use App\Helper\Constant\CardCode\MahjongCode;
use App\Helper\Constant\CardCode\PaigowCode;
use App\Helper\Constant\ParseMode;
use App\Helper\Constant\ParseType;
use App\Helper\Constant\CardCode\PokerCode;
use App\VendorService\Traits\KYLCCardParse;
use GiocoPlus\Mongodb\MongoDb;
use Hyperf\Di\Annotation\Inject;

class KYLC implements VendorServiceInterface
{
    use KYLCCardParse;

    /**
     * @Inject
     * @var MongoDb
     */
    private $mongodb;

    /**
     * 解析
     * @param string $gameId
     * @param array $rawDetail
     * @param string $vendorCode
     * @return string
     * @throws \GiocoPlus\Mongodb\Exception\MongoDBException
     */
    public function parsing(string $gameId, array $rawDetail, string $vendorCode): string
    {
        // card_value: 該局牌值結果; chair_id: 玩家座位號; raw: 原廠對局歷程
        ['card_value' => $cardValue, 'chair_id' => $chairId, 'raw' => $raw] = $rawDetail;
        $detail = $this->splitRawDetail($raw);
        switch ($gameId) {
            case '220': // 炸金花
            case '230': // 极速炸金花
            case '600': // 21 点
            case '610': // 斗地主
            case '620': // 德州扑克
            case '630': // 十三水
            case '640': // 跑得快
            case '830': // 抢庄牛牛
            case '860': // 三公
            case '870': // 通比牛牛
            case '890': // 看四张抢庄牛
            case '900': // 押庄龙虎
            case '910': // 百家乐
            case '930': // 百人牛牛
            case '950': // 红黑大战
            case '990': // 抢庄五选三
            case '1370': // 港式梭哈
            case '1810': // 单挑牛牛
            case '1850': // 押宝抢庄牛牛
            case '1860': // 赌场扑克
            case '1950': // 万人炸金花
            case '1970': // 五星宏辉
            case '1990': // 炸金牛
            case '8100': // 随机庄百变牛
            case '8130': // 跑得快
            case '8240': // 3+2炸金花
            case '8250': // 十点半
            case '8270': // 疯狂6张牛牛
            case '8300': // 抢庄21点
            case '8400': // 看牌抢庄三公
            case '8500': // 欢乐斗牛
                $parseType = ParseType::POKER;
                $detail = $this->parsePoker($detail);
                break;
            case '650': // 血流成河
            case '720': // 二八杠
            case '740': // 二人麻将
            case '1660': // 血戰到底
                $parseType = ParseType::MAHJONG;
                $detail = $this->parseMahjong($detail);
                break;
            case '1690': // 血战骰宝
            case '1980': // 百人骰宝
            case '8200': // 百人骰宝
                $parseType = ParseType::DICE;
                $detail = $this->parseDice($detail);
                break;
            case '730': // 抢庄牌九
                $parseType = ParseType::PAIGOW;
                $playerCards = $this->game730($cardValue, $chairId);
                $detail = $this->parseDefault($detail);
                break;
            case '510': // 红包捕鱼
            case '920': // 森林舞会
            case '1350': // 幸运转盘
            case '1355': // 搏一搏
            case '1610': // 幸运夺宝
            case '1890': // 水果机
            case '1930': // 鱼虾蟹
            case '1940': // 金鲨银鲨
            case '1960': // 奔驰宝马
            case '8260': // 踩雷红包(尚未分類)
            case '8280': // 幸运水果机(尚未分類)
            case '970': // 吹牛(尚未分類)
            case '980': // 对战牛牛(尚未分類)
            default:
                $parseType = ParseType::DEFAULT;
                $detail = $this->parseDefault($detail);
                break;
        }

        return $this->mongodb->insert('game_detail', [
            'game_result' => $playerCards ?? [],
            'game_detail' => $detail,
            'parse_type' => $parseType,
            'parse_mode' => ParseMode::STRING_DEFAULT,
            'vendor_code' => $vendorCode,
        ]);
    }

    /**
     * 拆分字串
     * @param string $raw
     * @return array
     */
    private function splitRawDetail(string $raw): array
    {
        $split = explode('\r\n', trim($raw));
        foreach ($split as $key => $row) {
            // 移除不需要的部分
            if ($row == '' || strpos($row, '[{') !== false) {
                unset($split[$key]);
                continue;
            }

            // 標點符號整理
            $row = $this->parseSymbol(',', $row);
            $row = str_replace('：', ':', $row);
            $row = $this->parseSymbol(':', $row);
            $row = $this->parseSymbol(';', $row);

            $split[$key] = trim($row);
        }
        return array_values($split);
    }

    /**
     * 標點符號整理
     * @param string $symbol
     * @param string $string
     * @return string
     */
    private function parseSymbol(string $symbol, string $string): string
    {
        $newRow = [];
        $rows = explode($symbol, $string);
        foreach ($rows as $k => $v) {
            if (trim($v) != '') {
                $newRow[] = trim($v);
            }
        }
        return implode($symbol . ' ', $newRow);
    }

    private function parseDefault(array $detail): array
    {
        foreach ($detail as $key => $row) {
            $detail[$key] = ['string_0' => $row];
        }
        return $detail;
    }

    private function parsePoker(array $detail): array
    {
        foreach ($detail as $key => $row) {
            // 牌值大小王轉換(替換順序不可變)
            $row = str_replace('▲', '▲2', $row);
            $row = str_replace('小王', '▲2', $row);
            $row = str_replace('★', '▲1', $row);
            $row = str_replace('大王', '▲1', $row);

            // 取出卡牌
            $newRow = [];
            if (preg_match_all('/(*UTF8)(?P<suit>[♠♥♦♣▲]{1})(?P<number>[AJQK\d]{1,2})/', $row, $cards, PREG_SET_ORDER)) {
                foreach ($cards as $k => $v) {
                    $card = current($v);
                    $splitRow = explode($card, $row);
                    if (current($splitRow) != '') {
                        $newRow['string_' . $k] = current($splitRow);
                    }
                    $newRow['card_' . $k] = $this->parsePokerCode($v['suit'], $v['number']);
                    $row = end($splitRow);
                }
                if ($row != '') {
                    $newRow['string_n'] = $row;
                }
            } else {
                $newRow['string_0'] = $row;
            }
            $detail[$key] = $newRow;
        }
        return $detail;
    }

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

    private function parseMahjong(array $detail): array
    {
        foreach ($detail as $key => $row) {
            // 牌值格式整理
            if (preg_match_all('/号位牌值: (?P<all_card>[一二三四五六七八九万筒条东南西北风中發白梅兰竹菊春夏秋冬, ]*)/', $row, $cards, PREG_SET_ORDER)) {
                foreach ($cards as $k => $cardRow) {
                    ['all_card' => $original] = $cardRow;
                    $newCardRow = str_replace('  ', ' ', $original);
                    $newCardRow = trim(str_replace(', ', ' ', $newCardRow)) . ', ';
                    $row = str_replace($original, $newCardRow, $row);
                }
            }

            // 取出卡牌
            $newRow = [];
            if (preg_match_all('/号位牌值: (?P<all_card>[一二三四五六七八九万筒条东南西北风中發白梅兰竹菊春夏秋冬 ]*),/', $row, $cards, PREG_SET_ORDER)) {
                foreach ($cards as $s => $cardRow) {
                    ['all_card' => $allCard] = $cardRow;
                    $splitRow = explode($allCard, $row);
                    if (current($splitRow) != '') {
                        $newRow['string_' . $s] = current($splitRow);
                    }

                    $allCards = explode(' ', $allCard);
                    foreach ($allCards as $c => $card) {
                        $newRow['card_' . $s . $c] = $this->parseMahjongCode($card);
                    }
                    $row = end($splitRow);
                }
                if ($row != '' && $row != ', ') {
                    $newRow['string_n'] = $row;
                }
            } else if (preg_match_all('/牌值: (?P<all_card>[一二三四五六七八九万筒条东南西北风中發白梅兰竹菊春夏秋冬 ]*)/', $row, $cards, PREG_SET_ORDER)) {
                foreach ($cards as $s => $cardRow) {
                    ['all_card' => $allCard] = $cardRow;
                    $splitRow = explode($allCard, $row);
                    if (current($splitRow) != '') {
                        $newRow['string_' . $s] = current($splitRow);
                    }

                    $allCards = explode(' ', $allCard);
                    foreach ($allCards as $c => $card) {
                        $newRow['card_' . $c] = $this->parseMahjongCode($card);
                    }
                    $row = end($splitRow);
                }
                if ($row != '') {
                    $newRow['string_n'] = $row;
                }
            } else {
                $newRow['string_0'] = $row;
            }
            $detail[$key] = $newRow;
        }
        return $detail;
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

    private function parseDice(array $detail): array
    {
        foreach ($detail as $key => $row) {
            $row = str_replace('  ', ' ', $row);

            // 取出卡牌
            $newRow = [];
            if (preg_match_all('/点数(?P<number>[123456 ]*),/', $row, $cards, PREG_SET_ORDER)) {
                $original = trim(current($cards)['number']);
                $splitRow = explode($original, $row);
                if (current($splitRow) != '') {
                    $newRow['string_0'] = current($splitRow);
                }

                $cards = explode(' ', $original);
                foreach ($cards as $k => $card) {
                    $newRow['card_' . $k] = $this->parseDiceCode($card);
                }
                $row = end($splitRow);
                if ($row != '') {
                    $newRow['string_n'] = $row;
                }
            } else {
                $newRow['string_0'] = $row;
            }
            $detail[$key] = $newRow;
        }
        return $detail;
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