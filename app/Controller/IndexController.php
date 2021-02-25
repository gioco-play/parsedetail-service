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

            [
                'parse_type' => $parseType,
                'detail' => $detail,
                'parse_mode' => $parseMode,
                'player_cards' => $playerCards,
            ] = current($data);
            return $this->view($parseType, $detail, $playerCards, $parseMode);
        } catch (\Throwable $th) {
            // TODO: remove output
            var_dump($th->getMessage());
            return $this->errorView('detail error');
        }
    }

    /**
     * 渲染視圖
     * @param string $parseType
     * @param array $detail
     * @param array $playerCards
     * @param string $parseMode
     * @return ResponseInterface
     */
    private function view(string $parseType, array $detail, array $playerCards = [], string $parseMode = ParseMode::STRING): ResponseInterface
    {
        return $this->render->render($parseMode, [
            'detail' => $detail,
            'parse_type' => $parseType,
            'player_cards' => $playerCards,
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
