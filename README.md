# Mini Fedi Server

A nearly-minimalist Fediverse server in PHP.

This solely exists for integrating testing for the Fedi-E2EE [pkd-client](https://github.com/fedi-e2ee/pkd-client-php) 
and [pkd-server](https://github.com/fedi-e2ee/pkd-server-php) software. I make no guarantees about its security or
performance. To that end, a major version will never be tagged.

> [!WARNING]
>
> Do not use in production environments!

## Installing

```terminal
# Get the code and its dependencies
git clone https://github.com/soatok/mini-fedi-server
cd mini-fedi-server
composer install

# Then edit config/server.php

# Finally, start the server
composer start
```
