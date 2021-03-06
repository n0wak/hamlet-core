<?php

namespace Hamlet\Request;

class WebRequest extends Request
{
    public function __construct()
    {
        $this->environmentName = $_SERVER['SERVER_NAME'];
        $this->method = $_SERVER['REQUEST_METHOD'];
        if ($this->method == 'GET' or $this->method == 'POST') {
            $this->parameters = $_REQUEST;
        } else {
            parse_str(file_get_contents('php://input'), $this->parameters);
        }

        if (function_exists('getallheaders')) {
            $this->headers = getallheaders();
        }
        $this->cookies = $_COOKIE;
        $this->ip = isset($this->headers['X-Forwarded-For']) ? $this->headers['X-Forwarded-For'] : $_SERVER['REMOTE_ADDR'];

        $completePath = urldecode($_SERVER['REQUEST_URI']);
        $questionMarkPosition = strpos($completePath, '?');
        if ($questionMarkPosition === false) {
            $this->path = $completePath;
        } else {
            $this->path = substr($completePath, 0, $questionMarkPosition);
        }
    }

    public function getSessionParameter($name, $defaultValue = null)
    {
        assert(is_string($name));
        $this->startSession();
        return parent::getSessionParameter($name, $defaultValue);
    }

    public function getSessionParameters()
    {
        $this->startSession();
        return parent::getSessionParameters();
    }

    protected function startSession()
    {
        if (!session_id()) {
            session_start();
            $this->sessionParameters = isset($_SESSION) ? $_SESSION : array();
        }
    }

}