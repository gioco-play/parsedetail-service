<?php
declare(strict_types=1);

namespace App\VendorService;

use App\Helper\Constant\ParseMode;
use App\Helper\Constant\ParseType;
use App\VendorService\CardParseTraits\KYLCCardParse;
use App\VendorService\GameTraits\KYLCGame;
use GiocoPlus\Mongodb\MongoDb;
use Hyperf\Di\Annotation\Inject;

class KYLC implements VendorServiceInterface
{
    use KYLCGame, KYLCCardParse;

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
            'parse_mode' => ParseMode::STRING,
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
}