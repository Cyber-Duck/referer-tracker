# Silverstripe-Referer-Tracker

Track the referer path for visitors in silverstripe

## Installation

* Save the folder referer-tracker in your site root
* Or with composer run `composer require Cyber-Duck/referer-tracker`
* Run a dev/build?flush=1
* Add the following tracking code to the init function of your Page.php

```php
public function init() {
    parent::init(); 
    $this->refererTracker = new refererTracker(function($a, $b) { return \Session::set($a, $b); }, function ($q) { return \Session::get($q); } );
    $this->refererTracker->log();
}
```

## Usage

Returns all referers which are deemed "internal"
```php
$this->refererTracker->retrieveInternal()
```

Returns all referers which are deemed "external"
```php
$this->refererTracker->retrieveExternal()
```

Convenience method which returns a single, merged array of the above
```php
$this->refererTracker->retrieveAll()`
```
