<?php

namespace Mix\Zipkin\Middleware\JsonRpc;

use Mix\JsonRpc\Message\Request;
use Mix\JsonRpc\Message\Response;
use Mix\JsonRpc\Middleware\MiddlewareInterface;
use Mix\JsonRpc\Middleware\RequestHandler;
use const OpenTracing\Formats\TEXT_MAP;

/**
 * Class TracingServerMiddleware
 * @package Mix\Zipkin\Middleware\JsonRpc
 */
abstract class TracingServerMiddleware implements MiddlewareInterface
{

    /**
     * Get tracer
     * @return \OpenTracing\Tracer
     */
    abstract public function tracer();

    /**
     * Process
     * @param Request[] $requests
     * @param RequestHandler $handler
     * @return Response[] $responses
     */
    public function process(array $requests, RequestHandler $handler): array
    {
        $tracer = $this->tracer();
        $tags   = [];
        foreach ($requests as $key => $request) {
            $request->context['tracer']       = $tracer;
            $tags[sprintf('method-%d', $key)] = $request->method;
        }

        // 在第一个请求的最后一个参数提取trace信息
        $request      = current($requests);
        $params       = $request->params;
        $traceHeaders = [];
        if (is_array($params)) {
            $traceHeaders = array_pop($params);
            $traceHeaders = is_object($traceHeaders) ? (array)$traceHeaders : [];
        }

        $spanContext   = $tracer->extract(TEXT_MAP, $traceHeaders);
        $operationName = 'jsonrpc:server';
        $span          = $tracer->startSpan($operationName, [
            'child_of' => $spanContext,
            'tags'     => $tags,
        ]);

        try {
            $result = $handler->handle($requests);
        } catch (\Throwable $exception) {
            throw $exception;
        } finally {
            $span->finish();
            $tracer->flush();
        }

        return $result;
    }

}