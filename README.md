# Validation
## Install
composer require alpha-zeta/validation
## Usage 
(for example)
```php
use Az\Validation\Middleware\ValidationMiddleware;
use Auth\Model\ModelUser;
use Psr\Http\Message\ServerRequestInterface;

class DataValidation extends ValidationMiddleware
{
    public function __construct(private ModelUser $modelUser){}

    protected function setRules(ServerRequestInterface $request)
    {
        $this->validation->rule('username', 'required|username|length(5, 15)')
            ->rule('email', 'required|email')
            ->rule('email', [$this->modelUser, 'isUniqueEmail'])
            ->rule('password', 'required|password|minLength(8)')
            ->rule('confirm', 'required|confirm(:data)')
            ->rule('agree', 'yes|boolean');
    }
}
```
### Or
```php
use Az\Validation\Validation;
use Auth\Model\ModelUser;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DataValidation extends ValidationMiddleware
{
    public function __construct(
        private Validation $validation,
        private ModelUser $modelUser
    ){}

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface
    {
        $this->validation->rule('username', 'required|username|length(5, 15)')
            ->rule('email', 'required|email')
            ->rule('email', [$this->modelUser, 'isUniqueEmail'])
            ->rule('password', 'required|password|minLength(8)')
            ->rule('confirm', 'required|confirm(:data)')
            ->rule('agree', 'yes|boolean');

        $data = $request->getParsedBody();
        $session = $request->getAttribute('session');

        if (!$this->validation->check($data)) {
            $session->flash('validation', $this->validation->getResponse());
            return new RedirectResponse($request->getServerParams()['HTTP_REFERER'], 302);
        }

        unset($data['confirm']);
        unset($data['agree']);

        return $handler->handle($request->withParsedBody($data));
    }
}
```
