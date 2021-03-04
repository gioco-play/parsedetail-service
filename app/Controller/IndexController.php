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

        try {
            $key = base64url_decode($inputs['key']);
            $data = $this->mongodb->fetchAll('game_detail', ['_id' => $key]);
            if (empty($data)) {
                return $this->errorView('detail not found');
            }

            ['game_result' => $gameResult, 'game_detail' => $gameDetail, 'parse_type' => $parseType, 'parse_mode' => $parseMode] = current($data);
            return $this->view($parseType, $gameDetail, json_decode(json_encode($gameResult), true), $parseMode);
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
     * @param array $gameResult 遊戲結果
     * @param string $parseMode 解析模式
     * @return ResponseInterface
     */
    private function view(string $parseType, array $gameDetail, array $gameResult = [], string $parseMode = ParseMode::STRING): ResponseInterface
    {
        return $this->render->render($parseMode, [
            'game_result' => $gameResult,
            'game_detail' => $gameDetail,
            'parse_type' => $parseType,
        ]);
    }

    /**
     * 渲染視圖
     * @param string $msg
     * @return ResponseInterface
     */
    private function errorView(string $msg): ResponseInterface
    {
        return $this->view('', [['string_0' => $msg]]);
    }
}
