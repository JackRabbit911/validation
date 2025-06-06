<?php

declare(strict_types=1);

namespace Az\Validation\Middleware;

use Az\Validation\Validation;
use HttpSoft\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

abstract class ApiValidationMiddleware implements MiddlewareInterface
{
    public function __construct(protected Validation $validation) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $lang = $request->getHeaderLine('Accept-Language') ?? 'en';
        $this->validation->setLang($lang);

        $this->setRules($request);

        if (($response = $this->validate($request, $handler))) {
            return $response;
        }

        $validation_response = $this->validation->getResponse();

        $validation_response = array_map(function ($a) {
            return [
                'status' => $a['status'],
                'msg' => $a['msg'],
            ];
        }, $validation_response);

        $validation_response['success'] = false;
        return new JsonResponse($validation_response);
    }

    protected function setRules(ServerRequestInterface $request) {}

    protected function modifyData($data)
    {
        return $data;
    }

    protected function validate(ServerRequestInterface $request, RequestHandlerInterface $handler): ?ResponseInterface
    {
        $data = $this->getData($request);
        $files = $request->getUploadedFiles();

        if ($this->validation->check($data, $files)) {
            return $handler->handle($request->withAttribute('data', $data));
        }

        return null;
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

        return $data;
    }
}
