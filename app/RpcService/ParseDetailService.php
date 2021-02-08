<?php
declare(strict_types=1);

namespace App\RpcService;

use GiocoPlus\ParseDetail\Contract\ParseDetailServiceInterface;
use GiocoPlus\PrismConst\Tool\ApiResponse;
use Hyperf\RpcServer\Annotation\RpcService;

/**
 * Class ParseDetailService
 * @package App\RpcService
 * @RpcService(name="ParseDetailService", protocol="jsonrpc-http", server="parsedetail-service", publishTo="consul")
 */
class ParseDetailService implements ParseDetailServiceInterface
{
    public function genDetailUrl(string $opCode, string $gameCode, string $betId): array
    {
        $url = "http://127.0.0.1:9511/api/v1/detail?op_code={$opCode}&game_code={$gameCode}&bet_id={$betId}";
        return ApiResponse::result(['url' => $url]);
    }
}