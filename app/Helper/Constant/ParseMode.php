<?php

declare(strict_types=1);

namespace App\Helper\Constant;

/**
 * 解析模板
 */
class ParseMode
{
    /**
     * 字串版型
     */
    const STRING = 'game-detail.string';

    /**
     * 真人版型
     */
    const LIVE = 'game-detail.live';
    const LIVE_BANKER_PLAYER = 'live.banker-player';
    const LIVE_PLAYER_LIST = 'live.player-list';
    const LIVE_ONLY_CARD = 'live.only-card';

    /**
     * 體育電競版型
     */
    const SPORT = 'game-detail.sport';

    /**
     * 彩票版型
     */
    const KENO = 'game-detail.keno';
}
