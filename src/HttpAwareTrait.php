<?php

/**
 * @file
 * Utility for HTTP client management.
 */

namespace PhpSlackInviter;

use GuzzleHttp\Client;

/**
 * Utility trait to handle the HTTP communications.
 *
 * This will ease up mocking of the http client instance during testing.
 *
 * @author ndobromirov
 */
trait HttpAwareTrait
{
    /** @var Client */
    private $http;

    /**
     * Factory method for the HTTP client instance.
     *
     * @return Client Client instance to be used for HTTP communications.
     */
    abstract public function initClient();

    /**
     * Accessor to the HTTP client instance.
     *
     * Internal nstance is lazy instantiated, based on the internal factory.
     *
     * @return Client
     */
    public function getClient()
    {
        if ($this->http === null) {
            $this->http = $this->initClient();
        }
        return $this->http;
    }

    /**
     * Mutator to the HTTP client instance.
     *
     * @param Client $client New client to use for testing.
     */
    public function setClient(Client $client)
    {
        $this->http = $client;
        return $this;
    }
}
