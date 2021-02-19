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

use App\Helper\DrawDetail;
use Hyperf\Di\Annotation\Inject;
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
     * @Inject
     * @var DrawDetail
     */
    private $drawDetail;

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
            return $this->view('default', ['key must be required']);
        }

        try {
            $key = base64url_decode($inputs['key']);
            $data = $this->mongodb->fetchAll('game_detail', ['_id' => $key]);
            if (empty($data)) {
                return $this->view('default', ['detail not found']);
            }

            ['parse_type' => $parseType, 'detail' => $detail] = current($data);
            return $this->view($parseType, $detail);
        } catch (\Throwable $th) {
            // TODO: remove output
            var_dump($th->getMessage());
            return $this->view('default', ['detail error']);
        }
    }

    /**
     * 渲染視圖
     * @param string $parseType
     * @param array $detail
     * @return ResponseInterface
     */
    private function view(string $parseType, array $detail): ResponseInterface
    {
        return $this->render->render($parseType, [
            'detail' => $this->drawDetail->{$parseType}($detail),
        ]);
    }
}
