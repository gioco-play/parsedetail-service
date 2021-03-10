<?php

declare(strict_types=1);

namespace App\VendorService;

interface VendorServiceInterface
{
    /**
     * 解析
     * @param string $gameId
     * @param array $rawDetail
     * @param string $vendorCode
     * @return string
     */
    function parsing(string $gameId, array $rawDetail, string $vendorCode): string;
}
