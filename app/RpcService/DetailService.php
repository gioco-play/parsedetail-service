<?php
declare(strict_types=1);

namespace App\RpcService;

use App\VendorService\KYLC;
use App\VendorService\WMLIVE;
use GiocoPlus\ParseDetail\Contract\ParseDetailServiceInterface;
use Hyperf\RpcServer\Annotation\RpcService;

/**
 * 解析遊戲詳情
 * @package App\RpcService
 * @RpcService(name="ParseDetailService", protocol="jsonrpc-http", server="parsedetail-service", publishTo="consul")
 */
class DetailService implements ParseDetailServiceInterface
{
    public function genUrl(string $gameCode, array $rawDetail, string $lang = 'zh'): string
    {
        try {
            $vendorCode = strstr($gameCode, '_', true);
            $gameId = substr($gameCode, strpos($gameCode, '_') + 1);

            switch ($vendorCode) {
                case 'ky':
                case 'gflc':
                    $vendorService = new KYLC();
                    break;
                case 'wmlive':
                    $vendorService = new WMLIVE();
                    break;
                default:
                    throw new \Exception('vendor not exist');
            }

            $key = $vendorService->parsing($gameId, $rawDetail, $vendorCode);
            return $this->urlResponse($lang, $key);
        } catch (\Throwable $th) {
            // TODO: remove output
            var_dump($th->getMessage());
            return $this->urlResponse($lang);
        }
    }

    /**
     * 回傳格式
     * @param string $lang
     * @param string $key
     * @return string
     */
    private function urlResponse(string $lang, string $key = ''): string
    {
        return env('DETAIL_HOST') . ':' . env('APP_PORT') . '/api/v1/detail?key=' . base64url_encode($key) . '&lang=' . $lang;
    }
}