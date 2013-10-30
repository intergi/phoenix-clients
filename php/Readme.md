# PHP Playwire Client

The PHP Playwire Client provides a fairly simple class to manage your videos in the new Playwire.

## Installation
This client has no external dependencies. Just download the client library, place it in a location of your choosing, and require/include it where you'd like to use it.

## Usage

Most of the API methods outlined in the [documentation](http://kb.intergi.com/kb/PHX3146/) require authentication before they can be used.

The constructor provides two methods to start your authenticated API session: you can provide your API token (`new PlaywireClient('API_TOKEN')`) OR your login credentials (`new PlaywireClient('user@example.com', 'secure_password')`). In the latter case, the client will authenticate with those credentials to retreive the API token for each subsequent request. When instantiated without any arguments, no authentication will take place and you'll need to authenticate manually.

```php
require('playwire_client.php');

$playwire = new PlaywireClient();

// Authenticate Manually
$playwire->authenticate('user@example.com', 'secure_password');

// Or use your token
$playwire->set_token('API_TOKEN');
```

Once authenticated, most methods should work as expected. Each is fairly-well documented at its defintion in the class.

## Fluent Interface

Where relevant, the client supports using a fluent, chainable interface. This means setup procedures can be performed in a chained manner.

```php
$playwire = new PlaywireClient();
$videos = $playwire->authenticate('API_TOKEN')->page(3)->per(25)->videos();
```
