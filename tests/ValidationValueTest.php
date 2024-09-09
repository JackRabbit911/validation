<?php declare(strict_types=1);

namespace Tests\Az\Validation;

use Az\Validation\Validation;
use Az\Validation\ValidationValue;
use Az\Validation\Parser;
use Az\Validation\Resolver;
use Az\Validation\ValidationHandler;
use Az\Validation\Response;
use Az\Validation\Message;
use PHPUnit\Framework\TestCase;

final class ValidationValueTest extends TestCase
{
    private $validator;

    public function setUp(): void
    {
        $message = new Message();
        $response = new Response($message);
        $defaultHandler = new ValidationHandler();
        $parser = new Parser();

        $resolver = new Resolver($defaultHandler);
        $validation = new Validation($response, $parser, $resolver);

        $this->validator = new ValidationValue($parser, $resolver, $validation);

        $this->validator->rule('length', 3, 8);
        
    }

    public function testRule()
    {
        $this->validator->rule('minLength', 3);

        $this->assertIsList($this->validator->rules);
        $this->assertSame(2, count($this->validator->rules));
        $this->assertIsObject($this->validator->rules[0]);
        $this->assertIsObject($this->validator->rules[1]);

        $this->assertSame('length', $this->validator->rules[0]->handler);
        $this->assertSame([3, 8], $this->validator->rules[0]->params);
    }

    public function testCheck()
    {
        $is_valid = $this->validator->check('foo');
        $this->assertTrue($is_valid);
        $is_valid = $this->validator->check('fo');
        $this->assertFalse($is_valid);
    }
}
