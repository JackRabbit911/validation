<?php

namespace Az\Validation;

class Validation
{
    public array $data = [];
    private array $rules = [];
    private array $check = [];
    private ?string $userHandler = null;
    private int $status_code = 200;

    public function __construct(private ValidationResponseInterface $response, ?array $options = null)
    {
        if ($options) {
            foreach ($options as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
        }
    }

    public function rule($name, $handler, ...$params): self
    {
        $value = [
            'handler' => $handler,
            'params' => $params,
        ];

        if (!isset($this->rules[$name])) {
            $this->rules[$name] = [$value];
        } else {
            $this->rules[$name][] = $value;
        }

        return $this;
    }

    public function reset(): self
    {
        $this->rules = [];
        $this->data = [];
        $this->check = [];

        return $this;
    }

    public function getRules(): array
    {
        return $this->rules;
    }

    public function check($data, $files = [], $dot_notation = false): bool
    {
        $this->data = $dot_notation ?
            flattenDot($data + $files) :
            flattenBracket($data + $files);

        foreach (array_keys(array_diff_key($this->rules, $this->data)) as $key) {
            $this->data[$key] = null;
        }

        $valid = new ValidationValue(
            $this->response,
            $this->rules,
            $this->data,
            $this->userHandler,
        );

        foreach ($this->data as $key => $value) {
            if (isset($this->rules[$key])) {
                $this->check[$key] = $valid->check($value, $key);
            }
        }

        return in_array(false, $this->check) ? false : true;
    }

    public function getResponse($is_api = false): array
    {
        return $is_api ?  $this->response->getApiResponse($this->data) :
            $this->response->getResponse($this->data, array_keys($this->rules));
    }

    public function response(): ValidationResponseInterface
    {
        return $this->response;
    }

    public function statusCode(?int $status_code = null): self | int
    {
        if ($status_code) {
            $this->status_code = $status_code;
            return $this;
        }

        return $this->status_code;
    }

    public function setUserHandler(string $className): self
    {
        $this->userHandler = $className;
        return $this;
    }

    public function addMsgPath($path): self
    {
        $this->response->addMsgPath($path);
        return $this;
    }

    public function setLang($lang): self
    {
        $this->response->setLang($lang);
        return $this;
    }
}

require_once 'library.php';
