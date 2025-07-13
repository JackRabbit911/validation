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
    protected Validation $validation;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->validation = container()->get(Validation::class);
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
                'message' => $a['msg'],
                'value' => $a['value'],
            ];
        }, $validation_response);

        $response['success'] = false;
        $response['error'] = $validation_response;

        return new JsonResponse($response);
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

        if (empty($data)) {
            $data = $request->getQueryParams();
        }

        if (is_string($data)) {
            $data = json_decode($data, true) ?? [];
        }

        return $data;
    }
}
