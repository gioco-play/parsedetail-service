<?php
declare(strict_types=1);

namespace App\VendorService;

use App\Helper\Constant\ParseMode;
use App\Helper\Constant\ParseType;
use App\Helper\DetailStructs;
use App\Helper\DetailTools;
use App\VendorService\CardParseTraits\WMLIVECardParse;
use App\VendorService\GameTraits\WMLIVEGame;
use GiocoPlus\Mongodb\MongoDb;
use GiocoPlus\PrismConst\Constant\TransactionConst;
use Hyperf\Di\Annotation\Inject;

class WMLIVE implements VendorServiceInterface
{
    use WMLIVEGame, WMLIVECardParse, DetailStructs, DetailTools;

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
        // TODO: 測完刪掉
        $rawDetail = json_decode($rawDetail[0], true);

        $lastLog = end($rawDetail);
        $rawData = $lastLog['raw_data'];
        $rawResult = $rawData['gameResult'];
        $roundId = $lastLog['parent_bet_id'];

        $gameName = $this->gameName[$gameId] ?? '';
        $gameFunc = 'game' . $gameId;

        switch ($gameId) {
            case '101': // 百家乐
            case '102': // 龙虎
            case '105': // 牛牛
            case '106': // 三公
            case '111': // 炸金花
                $gameResult = $this->{$gameFunc}($gameName, $rawResult, $roundId);
                $parseType = ParseType::POKER;
                $parseMode = ParseMode::LIVE_BANKER_PLAYER;
                break;
            case '128': // 安达巴哈
                $gameResult = $this->{$gameFunc}($gameName, $rawResult, $roundId);
                $parseType = ParseType::POKER;
                $parseMode = ParseMode::LIVE_PLAYER_LIST;
                break;
            case '104': // 骰宝
                $gameResult = $this->{$gameFunc}($gameName, $rawResult, $roundId);
                $parseType = ParseType::DICE;
                $parseMode = ParseMode::LIVE_ONLY_CARD;
                break;
            case '113': // 二八杠
                $gameResult = $this->{$gameFunc}($gameName, $rawResult, $roundId);
                $parseType = ParseType::MAHJONG;
                $parseMode = ParseMode::LIVE_BANKER_PLAYER;
                break;
            case '103': // 轮盘
            case '107': // 番摊
            case '108': // 色碟
            case '110': // 鱼虾蟹
                $result = ['string' => $rawResult];
                $gameResult = $this->noCardResultStruct($gameName, $roundId, $result);
                $parseType = ParseType::DEFAULT;
                $parseMode = ParseMode::LIVE;
                break;
            default: // 預設版面
                $result = [];
                $gameResult = $this->noCardResultStruct($gameName, $roundId, $result);
                $parseType = ParseType::DEFAULT;
                $parseMode = ParseMode::LIVE;
                break;
        }

        // 下注玩法: 下注時間、下注單號、下注點
        $playType = collect($rawDetail)->where('trans_type', TransactionConst::STAKE)->pluck('raw_data')->map(function ($raw) use ($vendorCode) {
            return [
                'bet_time' => $raw['betTime'],
                'bet_id' => $raw['betId'],
//                'play_type' => $vendorCode . '.' . $raw['betCode'], // 有使用各家下注玩法語系包的寫法，TODO: 詢問原廠是否可提供
//                'play_type' => $raw['betCode'], // 沒有使用各家下注玩法語系包的寫法(wmlive 英文)
                'play_type' => $raw['betResult'], // 沒有使用各家下注玩法語系包的寫法(wmlive 中文)
            ];
        })->toArray();

        return $this->mongodb->insert('game_detail', [
            'game_result' => $gameResult,
            'game_detail' => $playType,
            'parse_type' => $parseType,
            'parse_mode' => $parseMode,
            'vendor_code' => $vendorCode,
        ]);
    }
}