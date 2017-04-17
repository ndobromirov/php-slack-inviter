# php-slack-inviter
Library to ease programmatic invites sending.

[![License](https://poser.pugx.org/ndobromirov/php-slack-inviter/license)](https://packagist.org/packages/ndobromirov/php-slack-inviter)
[![Latest Stable Version](https://poser.pugx.org/ndobromirov/php-slack-inviter/version)](https://packagist.org/packages/ndobromirov/php-slack-inviter)
[![Build Status](https://api.travis-ci.org/ndobromirov/php-slack-inviter.svg?branch=master)](https://travis-ci.org/ndobromirov/php-slack-inviter)
[![Coverage Status](https://coveralls.io/repos/github/ndobromirov/php-slack-inviter/badge.svg?branch=master)](https://coveralls.io/github/ndobromirov/php-slack-inviter?branch=master)

# Requirements
1. Create a Slack team or join one.

2. Aquire a personal access token for a particular team you are part of.
Go to [this page](https://api.slack.com/custom-integrations/legacy-tokens) and
generate your access token. Invitations will originate from the access token's
owner.

# Installation
```
composer require ndobromirov/php-slack-inviter
```

# Usage example
```php
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
