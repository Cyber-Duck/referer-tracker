# SilverStripe 4 Referer Tracker

Track the referer path and time for visitors

## Installation

* Add the folowing to your composer.json `"cyberduck/referer-tracker": "4.0.*"`
* Run a dev/build?flush=1
* Add the following tracking code to the init function of your Page.php

```php
public function init() 
{
    parent::init(); 
    $this->logger = new \CyberDuck\RefererTracker\Logger(function($a, $b) { 
        return Session::set($a, $b); 
    }, function ($q) { 
        return Session::get($q); 
    });
    $this->logger->log();
}
```

## Usage

Returns all referers which are deemed "internal"
```php
$this->logger->retrieveInternal()
```

Returns all referers which are deemed "external"
```php
$this->logger->retrieveExternal()
```

Convenience method which returns a single, merged array of the above
```php
$this->logger->retrieveAll()
```
