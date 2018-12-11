<?php

namespace Kirkanta\Ptv;

class Client
{
    private $config;
    private $token;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Login to the API to get an authentication token.
     *
     * Tokens are valid for 24 hours.
     */
    public function auth($username, $password)
    {

    }

    public function createServiceChannel(array $document)
    {

    }

    public function updateServiceChannel(array $document)
    {

    }
}
