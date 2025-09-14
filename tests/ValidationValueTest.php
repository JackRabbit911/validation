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
    private $validation;

    public function setUp(): void
    {
        $message = new Message();
        $response = new Response($message);
        $this->validation = new Validation($response);
    }

    public function testCheck()
    {
        $result = $this->validation->rule('foo', 'length(3, 8)')
            ->check(['foo' => 'hello']);

        $this->assertTrue($result);

        $result = $this->validation->rule('baz', 'length(3, 8)')
            ->check(['baz' => 'hello world!']);

        $this->assertFalse($result);

        $result = $this->validation->rule('bar', 'is_string')
            ->check(['bar' => 'hello world!']);

        $this->assertTrue($result);

        $result = $this->validation->rule('ban', 'is_array')
            ->check(['ban' => 'hello world!']);

        $this->assertFalse($result);
    }
}
