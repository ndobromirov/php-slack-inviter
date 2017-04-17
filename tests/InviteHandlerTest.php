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
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\json_encode;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;

/**
 * Description of InviteTest
 *
 * @todo Improve test coverage for negative cases.
 *
 * @author ndobromirov
 */
class InviteHandlerTest extends \PHPUnit_Framework_TestCase
{
    private function createHandler(array $responses = [])
    {
        $handler = new InviteHandler('slack-token-value', 'slak-team-name');
        return $handler->setClient(new Client([
            'handler' => HandlerStack::create(new MockHandler($responses)),
        ]));
    }

    public function testSuccessfulInvite()
    {
        $handler = $this->createHandler([
            new Response(200, [], json_encode(['ok' => true])),
        ]);

        $this->assertTrue($handler->requestNewInvite('email@example.com'));
    }

    /**
     * @dataProvider slackErrors
     *
     * @param string $code Error code.
     * @param string $message Expected human-readable error message.
     */
    public function testSlackError($code, $message)
    {
        $handler = $this->createHandler([
            new Response(200, [], json_encode(['ok' => false, 'error' => $code])),
        ]);

        try {
            $handler->requestNewInvite('email@example.com');
            $this->assertTrue(false);
        } catch (Exception $e) {
            $this->assertEquals($message, $e->getMessage());
        }
    }

    public function slackErrors()
    {
        return [
            ['some-unknown-error', "Unknown error forom Slack API: some-unknown-error!"],
            ['sent_recently', "You've requested an invitation from this email recetly!"],
            ['already_invited', 'An invitation was already sent to your email!'],
            ['already_in_team', 'You are already part of the team!'],
            ['user_disabled', 'Your account has been disabled!'],
            ['invalid_email', 'Slack considers your email invalid.'],
        ];
    }

    /**
     * @dataProvider invalidSlackResponses
     *
     * @param Response $response
     */
    public function testHttpErrors($response)
    {
        $handler = $this->createHandler([$response]);

        try {
            $handler->requestNewInvite('email@example.com');
        } catch (Exception $e) {
            $this->assertEquals('There was an issue in communication with Slack!', $e->getMessage());
        }
    }

    public function invalidSlackResponses()
    {
        return [
            [new Response(200, [], '')],
            [new Response(200, [], '{')],
            [new Response(500, [], '')],
            [new ServerException('Server offline', new Request('POST', 'fake'))],
            [new RequestException('Can not connect to slack server.', new Request('POST', 'fake'))],
        ];
    }
}
