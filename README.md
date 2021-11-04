Example on how to use Leanlink's rest and webhook api.

# Installation
Requires php 8 and composer.

```
$ git clone git@github.com:LeanLink/SlackIntegrationExample.git
$ cd SlackIntegrationExample
$ composer install
$ cp .env .env.local
```

Edit .env.local with real values. 

To register the webhook browse to /register. 

Now everyting is setup and once a booking in Leanlink is confirmed a notification is sent to the provided Slack url.
