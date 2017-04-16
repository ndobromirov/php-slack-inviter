<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace PhpSlackInviter;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\json_encode;

/**
 * Description of InviteTest
 *
 * @todo Improve test coverage for negative cases.
 *
 * @author ndobromirov
 */
class InviteHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testSuccessfulInvite()
    {
        $handler = $this->createHandler([
            new Response(200, [], json_encode(['ok' => true])),
        ]);

        $this->assertTrue($handler->requestNewInvite('email@example.com'));
    }

    private function createHandler(array $responses = [])
    {
        $handler = new InviteHandler('slack-token-value', 'slak-team-name');
        return $handler->setClient(new Client([
            'handler' => HandlerStack::create(new MockHandler($responses)),
        ]));
    }
}
