<?php
declare(strict_types=1);

namespace App\Helper;

trait DetailStructs
{
    public function bankerPlayerResultStruct(string $gameName, string $roundId, array $result, array $player, array $banker): array
    {
        return [
            'game_name' => $gameName,
            'round_no' => $roundId,
            'result' => $result,
            'player' => $player,
            'banker' => $banker,
        ];
    }

    public function playerListResultStruct(string $gameName, string $roundId, array $result, array $player): array
    {
        return [
            'game_name' => $gameName,
            'round_no' => $roundId,
            'result' => $result,
            'player' => $player,
        ];
    }

    public function onlyCardResultStruct(string $gameName, string $roundId, array $result, array $card): array
    {
        return [
            'game_name' => $gameName,
            'round_no' => $roundId,
            'result' => $result,
            'card' => $card,
        ];
    }

    public function noCardResultStruct(string $gameName = '', string $roundId, array $result = []): array
    {
        return [
            'game_name' => $gameName,
            'round_no' => $roundId,
            'result' => $result,
        ];
    }
}