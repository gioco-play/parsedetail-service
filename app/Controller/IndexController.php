<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace App\Controller;

use App\Helper\Constant\ParseMode;
use App\Helper\Constant\ParseType;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Psr\Http\Message\ResponseInterface;

/**
 * Class IndexController.
 * @Controller(prefix="api/v1")
 */
class IndexController extends AbstractController
{
    /**
     * @return ResponseInterface
     * @GetMapping(path="detail")
     */
    public function index()
    {
        $inputs = $this->request->all();
        $validator = $this->validation->make(
            $inputs,
            [
                'key' => 'required',
            ]
        );

        if ($validator->fails()) {
            return $this->errorView('key must be required');
        }

        $lang = $inputs['lang'] ?? 'zh';

        try {
            $key = base64url_decode($inputs['key']);
            $data = $this->mongodb->fetchAll('game_detail', ['_id' => $key]);
            if (empty($data)) {
                return $this->errorView('detail not found');
            }

            [
                'game_result' => $gameResult,
                'game_detail' => $gameDetail,
                'parse_type' => $parseType,
                'parse_mode' => $parseMode,
                'vendor_code' => $vendorCode,
            ] = current($data);

            // 格式轉換
            $gameResult = json_decode(json_encode($gameResult), true);
            $gameDetail = json_decode(json_encode($gameDetail), true);
            return $this->view($parseType, $gameDetail, $vendorCode, $gameResult, $parseMode, $lang);
        } catch (\Throwable $th) {
            // TODO: remove output
            var_dump($th->getMessage());
            return $this->errorView('detail error');
        }
    }

    /**
     * 渲染視圖
     * @param string $parseType 牌圖類型
     * @param array $gameDetail 遊戲詳情
     * @param string $vendorCode 遊戲商代碼
     * @param array $gameResult 遊戲結果
     * @param string $parseMode 解析模式
     * @param string $lang 語言
     * @return ResponseInterface
     */
    private function view(string $parseType, array $gameDetail, string $vendorCode = '', array $gameResult = [], string $parseMode = ParseMode::STRING, $lang = 'zh'): ResponseInterface
    {
        return $this->render->render($parseMode, [
            'game_result' => $gameResult,
            'game_detail' => $gameDetail,
            'parse_type' => $parseType,
            'lang' => $lang,
            'vendor_code' => $vendorCode,
        ]);
    }

    /**
     * 渲染視圖
     * @param string $msg
     * @return ResponseInterface
     */
    private function errorView(string $msg): ResponseInterface
    {
        return $this->view(ParseType::DEFAULT, [['string_0' => $msg]]);
    }
}
