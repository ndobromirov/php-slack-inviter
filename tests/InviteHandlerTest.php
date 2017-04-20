<?php

/**
 * @file
 * Testing file for the InviteHandler class.
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
use PHPUnit_Framework_TestCase;

/**
 * InviteHandlerTest.
 *
 * @author ndobromirov
 */
class InviteHandlerTest extends PHPUnit_Framework_TestCase
{
    /** @var string */
    private $team;

    /** @var string */
    private $token;

    /** @var string */
    private $email;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->team = 'slack-team-name';
        $this->token = 'slack-token-value';
        $this->email = 'email@example.com';
    }

    /**
     * Utility factory for InviteHandler instances with a mocked responses.
     *
     * @param array $responses Ordered oist of Slack API responses.
     * @return InviteHandler The newly created instance.
     */
    private function createMockHandler(array $responses = [])
    {
        $mock = new MockHandler($responses);
        $stack = HandlerStack::create($mock);
        $http = new Client(['handler' => $stack]);

        $handler = new InviteHandler($this->token, $this->team);
        $handler->setClient($http);

        return $handler;
    }

    public function testCreation()
    {
        $handler = new InviteHandler($this->token, $this->team);
        $this->assertTrue($handler->getClient() instanceof Client);
    }

    public function testSuccessfulInvite()
    {
        $handler = $this->createMockHandler([
            new Response(200, [], json_encode(['ok' => true])),
        ]);

        $this->assertTrue($handler->requestNewInvite($this->email));
    }

    /**
     * @dataProvider slackErrors
     *
     * @param string $code Error code.
     * @param string $message Expected human-readable error message.
     */
    public function testSlackError($code, $message)
    {
        $body = json_encode(['ok' => false, 'error' => $code]);
        $response = new Response(200, [], $body);
        $handler = $this->createMockHandler([$response]);

        $this->assertExceptionMessage($message, function () use ($handler) {
            $handler->requestNewInvite($this->email);
        });
    }

    /**
     * Data provider utility.
     *
     * @see InviteHandlerTest::testSlackError()
     *
     * @return array List of input data.
     */
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
        $handler = $this->createMockHandler([$response]);

        $message = 'There was an issue in communication with Slack!';
        $this->assertExceptionMessage($message, function () use ($handler) {
            $handler->requestNewInvite($this->email);
        });
    }

    /**
     * Invalid slack responces data provider.
     *
     * @see InviteHandlerTest::testHttpErrors()
     *
     * @return array List of problematic responses to use.
     */
    public function invalidSlackResponses()
    {
        return [
            [new Response(200, [], '')],
            [new Response(200, [], '{')],
            [new Response(500, [], '')],
            [new ServerException('Offline', new Request('POST', '-'))],
            [new RequestException('No connection!', new Request('POST', '-'))],
        ];
    }

    /**
     * Utility assert cb.
     *
     * @param string $message The expected exception message.
     * @param callable $callback No parameters callback throwing an exception.
     */
    public function assertExceptionMessage($message, $callback)
    {
        try {
            call_user_func($callback);
            $this->assertTrue(false);
        } catch (Exception $e) {
            $this->assertEquals($message, $e->getMessage());
        }
    }
}
