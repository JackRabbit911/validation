<?php

namespace Az\Validation;

final class Message
{
    public array $keys = [];
    private array $msgPath = ['messages'];
    private array $messages = [];
    private string $lang = 'en';

    public function addMsgPath(string $path): void
    {
        array_push($this->msgPath, $path);
    }

    public function setLang($lang)
    {
        $this->lang = $lang;
    }

    public function get(string $key, array $params = [], string $default = 'default'): string
    {
        $this->setMessages();

        $search = array_keys($params);
        $replace = array_values($params);
        

        if (strpos($key, ' ') !== false) {
            return str_replace($search, $replace, $key);
        }

        $message = $this->messages[$key] ?? $this->messages[$default] ?? 'Invalid data';
        return str_replace($search, $replace, $message);
    }

    public function setMsgKey($name, $key)
    {
        $this->keys[$name] = $key;
    }

    private function setMessages()
    {
        foreach ($this->msgPath as $path) {
            $file = trim($path, '/') . '/' . $this->lang . '.php';
            $this->messages = array_replace($this->messages, require $file);
        }
    }
}
