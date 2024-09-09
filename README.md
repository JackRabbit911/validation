# Validation
## Install
composer require alpha-zeta/validation
## Usage
```php
use Az\Validation\Validation;

class DataValidation implements MiddlewareInterface
{
    protected Validation $validation;

    public function __construct(Validation $validation)
    {
        $this->validation = $validation;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler)
    {
        $this->validation->rule('username', 'required|username|length(5, 15)')
            ->rule('email', 'required|email')
            ->rule('email', [$modelUser, 'isUniqueEmail'])
            ->rule('password', 'required|password|minLength(8)');

        if (!$this->validation->check($request->getPasedBody(), $request->getUploadedFiles())) {
            $session->flash('validation', $this->validation->getResponse());
            return new RedirectResponse($request->getServerParams()['HTTP_REFERER'], 302);
        }

        return $handler->handle($request->withAttribute('validation', $this->validation));
    }
}
```
