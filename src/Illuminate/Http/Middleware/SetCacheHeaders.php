<?php

namespace Illuminate\Http\Middleware;

use Closure;
use Illuminate\Support\Carbon;

class setCacheHeaders
{
    /**
     * Add cache related HTTP headers.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param array $options
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \InvalidArgumentException
     */
    public function handle($request, Closure $next, $options = [])
    {
        $response = $next($request);

        if (!$request->isMethodCacheable() || !$request->getContent()) {
            return $response;
        }

        if (is_string($options)) {
            $options = $this->parseOptions($options);
        }

        if (isset($options['etag']) && $options['etag'] === true) {
            $options['etag'] = md5($response->getContent());
        }

        if (isset($options['last_modified'])) {
            if (is_numeric($options['last_modified'])) {
                $options['last_modified'] = Carbon::createFromTimestamp($options['last_modified']);
            } else {
                $options['last_modified'] = Carbon::parse($options['last_modified']);
            }
        }

        $response->setCache($options);
        $response->isNotModified($request);

        return $response;
    }

    /**
     * Parse the given header options.
     *
     * @param string $options
     * @return array
     */
    protected function parseOptions($options)
    {
        return collect(explode(';', $options))->mapWithKeys(function ($option) {
            $data = explode('=', $option, 2);

            return [$data[0] => $data[1] ?? true];
        })->all();
    }
}
