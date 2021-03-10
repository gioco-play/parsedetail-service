<?php

declare(strict_types=1);

namespace App\Helper\Constant;

/**
 * 解析模板
 */
class ParseMode
{
    /**
     * 字串預設版型
     */
    const STRING_DEFAULT = 'game-detail.string';

    /**
     * 真人預設版型
     */
    const LIVE_DEFAULT = 'game-detail.live';

    /**
     * 真人版型
     */
    const BANKER_PLAYER = 'live.banker-player';
    const PLAYER_LIST = 'live.player-list';
    const ONLY_CARD = 'live.only-card';

}
