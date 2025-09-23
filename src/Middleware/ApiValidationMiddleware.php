<?php

declare(strict_types=1);

namespace Az\Validation\Middleware;

use Az\Validation\Validation;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

abstract class ApiValidationMiddleware implements MiddlewareInterface
{
    protected Validation $validation;

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface
    {
        $this->validation = container()->get(Validation::class);
        $lang = $request->getHeaderLine('Accept-Language') ?? 'en';
        $this->validation->setLang($lang);

        $this->setRules($request);

        $data = $this->getData($request);
        $files = $request->getUploadedFiles();        
        $check = $this->validation->check($data, $files, true);

        return $this->getResponse($request, $handler, $check);
    }

    protected function setRules(ServerRequestInterface $request) {}

    protected function getResponse(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler,
        bool $check,
    ): ResponseInterface {
        return $handler->handle($request);
    }

    protected function modifyData($data)
    {
        return $data;
    }

    private function getData($request)
    {
        $data = $request->getBody()->getContents();

        if (empty($data)) {
            $data = $request->getParsedBody();
        }

        if (is_string($data)) {
            $data = json_decode($data, true) ?? [];
        }

        $data += $request->getQueryParams();

        return $data;
    }
}
