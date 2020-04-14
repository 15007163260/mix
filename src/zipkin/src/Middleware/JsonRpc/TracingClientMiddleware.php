<?php

namespace Mix\Zipkin\Middleware\JsonRpc;

use Mix\JsonRpc\Message\Request;
use Mix\JsonRpc\Message\Response;
use Mix\JsonRpc\Middleware\MiddlewareInterface;
use Mix\JsonRpc\Middleware\RequestHandler;
use const OpenTracing\Formats\TEXT_MAP;

/**
 * Class TracingClientMiddleware
 * @package Mix\Zipkin\Middleware\JsonRpc
 */
class TracingClientMiddleware implements MiddlewareInterface
{

    /**
     * @var \OpenTracing\Tracer
     */
    public $tracer;

    /**
     * TracingClientInterceptor constructor.
     * @param \OpenTracing\Tracer $tracer
     */
    public function __construct(\OpenTracing\Tracer $tracer)
    {
        $this->tracer = $tracer;
    }

    /**
     * Process
     * @param Request $request
     * @param RequestHandler $handler
     * @return Response
     */
    public function process(Request $request, RequestHandler $handler): Response
    {
        $tracer         = $this->tracer;
        
        $tags['method'] = $request->method;
        $tags['id']     = $request->id;
        $operationName = 'jsonrpc:client';
        $scope         = $tracer->startActiveSpan($operationName, [
            'tags' => $tags,
        ]);

        $traceHeaders = [];
        $tracer->inject($scope->getSpan()->getContext(), TEXT_MAP, $traceHeaders);
        // 在请求的最后一个参数追加trace信息
        array_push($request->params, $traceHeaders);

        try {
            $result = $handler->handle($request);
        } catch (\Throwable $exception) {
            throw $exception;
        } finally {
            $scope->close();
        }

        return $result;
    }

}
