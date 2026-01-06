# Mini Fedi Server

A nearly-minimalist Fediverse server in PHP.

This solely exists for integrating testing for the Fedi-E2EE [pkd-client](https://github.com/fedi-e2ee/pkd-client-php) 
and [pkd-server](https://github.com/fedi-e2ee/pkd-server-php) software. I make no guarantees about its security or
performance. To that end, a major version will never be tagged.

> [!WARNING]
>
> Do not use in production environments!

## Installing as a Dev-Dependency

```terminal
# Get the code
composer require --dev soatok/mini-fedi-server
cd vendor/soatok/mini-fedi-server

# Edit config/server.php

# Finally, start the server (defaults to port 65233, which is 0xFED1 or "Fedi")
composer start
```

## Installing (for Mini-Fedi Development)

```terminal
# Get the code and its dependencies
git clone https://github.com/soatok/mini-fedi-server
cd mini-fedi-server
composer install

# Then edit config/server.php

# Finally, start the server (defaults to port 65233, which is 0xFED1 or "Fedi")
composer start
```

## Using the Mini-Fedi Server in Unit Tests

Use the Orchestration test class to manage the SQL database.

```php
<?php
use Soatok\MiniFedi\Orchestration;
use ParagonIE\EasyDB\EasyDB;

class Foo extends \PHPUnit\Framework\TestCase
{
    public function yourTest(EasyDB $yourDatabaseGoesHere): void
    {
        $orchestration = new Orchestration($yourDatabaseGoeshere);
        $orchestration->stash(); // if any changes were already saved, back them up

        $alice = $orchestration->createActor('alice');
        $orchestration->createPublicKeyForActor($alice, 'public key goes here');
        $bob = $orchestration->createActor('bob');
        $this->assertSame('alice', $alice->username);

        $orchestration->unstash(); // restore backup
    }
}
```

Meanwhile, you can send HTTP requests to `http://localhost:65233` in callbacks and verify the changes through the 
Orchestration class.
