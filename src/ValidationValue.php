<?php

namespace Az\Validation;

final class ValidationValue
{
    use SantizeParams;

    private Parser $parser;
    private Resolver $resolver;

    public function __construct(
        private Response $response,
        private array $rules,
        private array $data,
        private ?string $userHandler = null,
    ) {
        $this->parser = new Parser();
        $this->resolver = new Resolver($userHandler);
    }

    public function check($value, $key)
    {
        $rule = $this->rules[$key];
        $field_rules = $this->parser->parse($rule);

        foreach ($field_rules as $rule) {
            $params = $this->santizeParams($rule->params, $value);
            $handler = $this->resolver->resolve($rule->handler);
            $result = call_user_func_array($handler, $params);
            $result = ($rule->inverse) ? !$result : $result;

            if ($result !== true) {
                array_shift($params);
                $this->response->setErrorData($key, $rule->handler, $params);
                break;
            }
        }

        return $result;
    }
}
