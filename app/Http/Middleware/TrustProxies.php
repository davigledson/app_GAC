<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * Lista de proxies confiáveis (use "*" para confiar em todos).
     *
     * @var array|string|null
     */
    protected $proxies = '*';

    /**
     * Cabeçalhos que devem ser usados para identificar HTTPS, host, etc.
     *
     * @return int
     */
    protected function getHeaders(): int
    {
        return Request::HEADER_X_FORWARDED_ALL;
    }
}
