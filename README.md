# php-slack-inviter
Library to ease programmatic invites sending.

# Requirements
1. Create a Slack team or join one.

2. Slack a personal access token for the particular team.
Go to [this page](https://api.slack.com/custom-integrations/legacy-tokens) and
generate your access token for a particular team you are part of. invitations
will originate the access token's owner.

# Installation
```
composer require ndobromirov/php-slack-inviter
```

# Usage example
```
require __DIR__ . '/vendor/autoload.php';

$team = 'my-team-name'
$token = 'my-token-value';

$handler = new \PhpSlackInviter\InviteHandler($token, $team);

try {
    $handler->requestNewInvite('email@example.com');
    echo 'Invitation was sent!'
}
catch (\PhpSlackInviter\Exception $e) {
    echo $e->getMessage();
}
```
