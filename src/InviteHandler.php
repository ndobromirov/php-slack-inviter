<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace PhpSlackInviter;

use GuzzleHttp\Client;
use GuzzleHttp\json_decode;

/**
 * Description of InviteSender
 *
 * @author ndobromirov
 */
class InviteHandler
{
    use HttpAwareTrait;

    /** @var string */
    private $accessToken;

    /** @var string */
    private $teamName;

    /** @var array List of channels to subscribe automatically. */
    private $channels;

    /**
     * Client sonstructor.
     *
     * @param string $token
     * @param string $teamName
     * @param array $channels List of channel IDs to add.
     */
    public function __construct($token, $teamName, array $channels = [])
    {
        $this->accessToken = $token;
        $this->teamName = $teamName;
        $this->channels = $channels;
    }

    /**
     * Factory method for the internal comunications with the Slack's API.
     *
     * @return Client New vald instance with a correct base URI set.
     */
    public function initClient()
    {
        return new Client([
            'base_uri' => "https://{$this->teamName}.slack.com/api/",
        ]);
    }

    /**
     * Requests an inveto for a given email.
     *
     * @param string $email Email to send the invite to.
     * @return bool True on success.
     *
     * @throws Exception On any error (HTTP or Slack related).
     */
    public function requestNewInvite($email)
    {
        try {
            // https://github.com/ErikKalkoken/slackApiDoc/blob/master/users.admin.invite.md
            $response = $this->getClient()->post('users.admin.invite', [
                'form_params' => array_filter([
                    'token' => $this->accessToken,
                    'email' => $email,
                    'channels' => implode(',', $this->channels),
                    'resend' => 1,
                ]),
            ]);

            $result = json_decode($response->getBody()->getContents());
        } catch (\Exception $e) {
            $result = null;
        }

        // Empty request body or any exception.
        if (empty($result)) {
            $result = (object) ['ok' => false, 'error' => 'http'];
        }

        if (!$result->ok) {
            throw new Exception($this->mapError($result->error));
        }

        return true;
    }

    private function mapError($code)
    {
        $map = [
            // Our custom ones.
            'http' => 'There was an issue in communication with Slack!',
            'unknown_error' => "Unknown error forom Slack API: $code!",

            // Slack errors.
            'sent_recently' => "You've requested an invitation from this email recetly!",
            'already_invited' => 'An invitation was already sent to your email!',
            'already_in_team' => 'You are already part of the team!',
            'user_disabled' => 'Your account has been disabled!',
            'invalid_email' => 'Slack considers your email invalid.',
        ];

        return $map[isset($map[$code]) ? $code : 'unknown_error'];
    }
}
