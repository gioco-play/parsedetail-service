<?php
declare(strict_types=1);

namespace App\VendorService\GameTraits;

trait KYLCGame
{
    /**
     * 抢庄牌九
     * @param string $cardValue
     * @param string $chairId
     * @return array
     */
    private function game730(string $cardValue, string $chairId): array
    {
        /* 字段规则两位數一組表示一张牌值，每个玩家2张手牌，共4位玩家，最后一位數表示庄家位置 */
        $cardLength = 2;
        $handCount = 2;

        // 庄家位置
        $bankerSeat = substr($cardValue, -1);

        // 切出手牌
        $allCards = str_split(substr($cardValue, 0, -1), $cardLength);
        $cardCount = count($allCards);
        $seatCount = $cardCount / $handCount;

        $cards = [];
        for ($playerSeat = 1, $cardIndex = 0; $playerSeat <= $seatCount; $playerSeat++) {
            $row = [];
            $role = [];
            if ($playerSeat == $chairId) {
                $role[] = '玩家';
            }
            if ($playerSeat == $bankerSeat) {
                $role[] = '庄家';
            }
            $role = (empty($role)) ? '' : implode('、', $role) . ' ';

            $row['string_0'] = $role . $playerSeat . '号位牌值:';
            for ($j = 0; $j < $handCount; $j++, $cardIndex++) {
                $row['card_' . $j] = $this->parsePaigowCode($allCards[$cardIndex]);
            }
            $cards[] = $row;
        }
        return $cards;
    }
}