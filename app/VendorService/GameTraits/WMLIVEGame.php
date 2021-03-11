<?php
declare(strict_types=1);

namespace App\VendorService\GameTraits;

trait WMLIVEGame
{
    private $gameCancel = ['该局取消', 'The council canceled'];

    private $gameName = [
        '101' => 'baccarat',
        '102' => 'dragon_tiger',
        '103' => 'roulette',
        '104' => 'sic_bo',
        '105' => 'niu_niu',
        '106' => 'sam_gong',
        '107' => 'fan_tan',
        '108' => 'se_die',
        '110' => 'fish_shrimp_crab',
        '111' => 'golden_flower',
        '112' => 'pai_gow',
        '113' => 'this_bar',
        '128' => 'andar_bahar',
    ];

    /**
     * 百家乐
     * @param string $gameName
     * @param string $gameResult
     * @param string $roundId
     * @return array
     */
    private function game101(string $gameName, string $gameResult, string $roundId): array
    {
        $position = ['player' => [], 'banker' => []];
        $result = [];

        if (in_array($gameResult, $this->gameCancel)) {
            $result['is_cancel'] = 'cancel';
        } else {
            $text = ['庄' => 'banker', '闲' => 'player'];
            if (preg_match_all('/(*UTF8)(?P<bp>[庄闲龙虎凤安达巴哈Joker123]+):(?P<card_value>[♠♥♦♣AJQK\d]+)/', $gameResult, $split, PREG_SET_ORDER)) {
                foreach ($split as $v) {
                    ['bp' => $bp, 'card_value' => $cardValue] = $v;
                    $key = $text[$bp];
                    $cards = $this->parsePoker($cardValue);
                    $position[$key][] = [
                        'name' => $text[$bp] . '_seat',
                        'seat' => '',
                        'card' => $cards,
                    ];

                    // 庄閒點數
                    $result['point'][$key] = $this->calculateCardPoint($gameName, $cards);
                }
            }
        }
        return $this->bankerPlayerResultStruct($gameName, $roundId, $result, $position['player'], $position['banker']);
    }

    /**
     * 龙虎
     * @param string $gameName
     * @param string $gameResult
     * @param string $roundId
     * @return array
     */
    private function game102(string $gameName, string $gameResult, string $roundId): array
    {
        $position = ['player' => [], 'banker' => []];
        $referBP = ['tiger' => 'player', 'dragon' => 'banker'];
        $result = [];

        if (in_array($gameResult, $this->gameCancel)) {
            $result['is_cancel'] = 'cancel';
        } else {
            $text = ['龙' => 'dragon', '虎' => 'tiger'];
            if (preg_match_all('/(*UTF8)(?P<bp>[庄闲龙虎凤安达巴哈Joker123]+):(?P<card_value>[♠♥♦♣AJQK\d]+)/', $gameResult, $split, PREG_SET_ORDER)) {
                foreach ($split as $v) {
                    ['bp' => $bp, 'card_value' => $cardValue] = $v;
                    $key = $text[$bp];
                    $cards = $this->parsePoker($cardValue);
                    $position[$referBP[$key]][] = [
                        'name' => $text[$bp],
                        'seat' => '',
                        'card' => $cards,
                    ];

                    // 庄閒點數
                    $result['point'][$key] = $this->calculateCardPoint($gameName, $cards);
                }
            }
        }
        return $this->bankerPlayerResultStruct($gameName, $roundId, $result, $position['player'], $position['banker']);
    }

    /**
     * 骰宝
     * @param string $gameName
     * @param string $gameResult
     * @param string $roundId
     * @return array
     */
    private function game104(string $gameName, string $gameResult, string $roundId): array
    {
        $dice = [];
        $result = [];

        if (in_array($gameResult, $this->gameCancel)) {
            $result['is_cancel'] = 'cancel';
        } else {
            $dice = explode(',', $gameResult);
        }
        return $this->onlyCardResultStruct($gameName, $roundId, $result, $dice);
    }

    /**
     * 牛牛
     * @param string $gameName
     * @param string $gameResult
     * @param string $roundId
     * @return array
     */
    private function game105(string $gameName, string $gameResult, string $roundId): array
    {
        $position = ['player' => [], 'banker' => []];
        $result = [];

        if (in_array($gameResult, $this->gameCancel)) {
            $result['is_cancel'] = 'cancel';
        } else {
            $text = ['庄' => 'banker', '闲' => 'player'];
            $gameResult = str_replace(',', '', $gameResult);
            if (preg_match_all('/(*UTF8)(?P<bp>[庄闲]+)(?P<seat>[\d]*):(?P<card_value>[♠♥♦♣AJQK\d]+)/', $gameResult, $split, PREG_SET_ORDER)) {
                foreach ($split as $v) {
                    ['bp' => $bp, 'seat' => $seat, 'card_value' => $cardValue] = $v;
                    $key = $text[$bp];
                    $cards = $this->parsePoker($cardValue);
                    $position[$key][] = [
                        'name' => $key . '_seat',
                        'seat' => $seat,
                        'card' => $cards,
                    ];
                }
            }
        }
        return $this->bankerPlayerResultStruct($gameName, $roundId, $result, $position['player'], $position['banker']);
    }

    /**
     * 三公
     * @param string $gameName
     * @param string $gameResult
     * @param string $roundId
     * @return array
     */
    private function game106(string $gameName, string $gameResult, string $roundId): array
    {
        $position = ['player' => [], 'banker' => []];
        $result = [];

        if (in_array($gameResult, $this->gameCancel)) {
            $result['is_cancel'] = 'cancel';
        } else {
            $text = ['庄' => 'banker', '闲' => 'player'];
            if (preg_match_all('/(*UTF8)(?P<bp>[庄闲]+)(?P<seat>[\d]*):(?P<card_value>[♠♥♦♣AJQK\d]+)/', $gameResult, $split, PREG_SET_ORDER)) {
                foreach ($split as $v) {
                    ['bp' => $bp, 'seat' => $seat, 'card_value' => $cardValue] = $v;
                    $key = $text[$bp];
                    $cards = $this->parsePoker($cardValue);
                    $position[$key][] = [
                        'name' => $key . '_seat',
                        'seat' => $seat,
                        'card' => $cards,
                    ];
                }
            }
        }
        return $this->bankerPlayerResultStruct($gameName, $roundId, $result, $position['player'], $position['banker']);
    }

    /**
     * 炸金花
     * @param string $gameName
     * @param string $gameResult
     * @param string $roundId
     * @return array
     */
    private function game111(string $gameName, string $gameResult, string $roundId): array
    {
        $position = ['player' => [], 'banker' => []];
        $referBP = ['phoenix' => 'player', 'dragon' => 'banker'];
        $result = [];

        if (in_array($gameResult, $this->gameCancel)) {
            $result['is_cancel'] = 'cancel';
        } else {
            $text = ['龙' => 'dragon', '凤' => 'phoenix'];
            if (preg_match_all('/(*UTF8)(?P<bp>[庄闲龙虎凤安达巴哈Joker123]+):(?P<card_value>[♠♥♦♣AJQK\d]+)/', $gameResult, $split, PREG_SET_ORDER)) {
                foreach ($split as $v) {
                    ['bp' => $bp, 'card_value' => $cardValue] = $v;
                    $key = $text[$bp];
                    $cards = $this->parsePoker($cardValue);
                    $position[$referBP[$key]][] = [
                        'name' => $text[$bp],
                        'seat' => '',
                        'card' => $cards,
                    ];
                }
            }
        }
        return $this->bankerPlayerResultStruct($gameName, $roundId, $result, $position['player'], $position['banker']);
    }

    /**
     * 二八杠
     * @param string $gameName
     * @param string $gameResult
     * @param string $roundId
     * @return array
     */
    private function game113(string $gameName, string $gameResult, string $roundId): array
    {
        $position = ['player' => [], 'banker' => []];
        $result = [];

        if (in_array($gameResult, $this->gameCancel)) {
            $result['is_cancel'] = 'cancel';
        } else {
            $text = ['庄' => 'banker', '闲' => 'player'];
            if (preg_match_all('/(*UTF8)(?P<bp>[庄闲]+)(?P<seat>[\d]*):(?P<card_value>[万条筒东南西北中發白春夏秋冬梅兰竹菊\d]+)/', $gameResult, $split, PREG_SET_ORDER)) {
                foreach ($split as $v) {
                    ['bp' => $bp, 'seat' => $seat, 'card_value' => $cardValue] = $v;
                    $key = $text[$bp];
                    $cards = $this->parseMahjong($cardValue);
                    $position[$key][] = [
                        'name' => $key . '_seat',
                        'seat' => $seat,
                        'card' => $cards,
                    ];
                }
            }
        }
        return $this->bankerPlayerResultStruct($gameName, $roundId, $result, $position['player'], $position['banker']);
    }

    /**
     * 安达巴哈
     * @param string $gameName
     * @param string $gameResult
     * @param string $roundId
     * @return array
     */
    private function game128(string $gameName, string $gameResult, string $roundId): array
    {
        $position = ['player' => []];
        $result = [];

        if (in_array($gameResult, $this->gameCancel)) {
            $result['is_cancel'] = 'cancel';
        } else {
            $text = ['Joker' => 'joker', '安达' => 'andar', '巴哈' => 'bahar'];
            if (preg_match_all('/(*UTF8)(?P<bp>[庄闲龙虎凤安达巴哈Joker123]+):(?P<card_value>[♠♥♦♣AJQK\d]+)/', $gameResult, $split, PREG_SET_ORDER)) {
                foreach ($split as $v) {
                    ['bp' => $bp, 'card_value' => $cardValue] = $v;
                    $key = $text[$bp];
                    $cards = $this->parsePoker($cardValue);
                    $position['player'][] = [
                        'name' => $key,
                        'seat' => '',
                        'card' => $cards,
                    ];
                }
            }
        }
        return $this->playerListResultStruct($gameName, $roundId, $result, $position['player']);
    }

    private function parsePoker(string $cardValue): array
    {
        $parse = [];
        if (preg_match_all('/(*UTF8)(?P<suit>[♠♥♦♣▲]{1})(?P<number>[AJQK\d]{1,2})/', $cardValue, $cards, PREG_SET_ORDER)) {
            foreach ($cards as $card) {
                ['suit' => $suit, 'number' => $number] = $card;
                $parse[] = $this->parsePokerCode($suit, $number);
            }
        }
        return $parse;
    }

    private function parseMahjong(string $cardValue): array
    {
        $parse = [];
        if (preg_match_all('/(*UTF8)(?P<number>[\d]{0,1})(?P<suit>[万条筒东南西北中發白春夏秋冬梅兰竹菊]{1})/', $cardValue, $cards, PREG_SET_ORDER)) {
            foreach ($cards as $card) {
                ['suit' => $suit, 'number' => $number] = $card;
                $parse[] = $this->parseMahjongCode($suit, $number);
            }
        }
        return $parse;
    }
}