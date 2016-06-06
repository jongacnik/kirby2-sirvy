# Sirvy: Kirby Services API

This is a plugin for [Kirby](http://getkirby.com) that introduces an extensible URL based API for performing services such as resizing images or returning page contents as json.

## Installation

Put the `sirvy` folder in `/site/plugins`.

## What does this do?

Sirvy injects a `/sirvy/(:all?)` route into your site. This route passes data to PHP functions (we're calling them services).

For example, after you've added Sirvy to your project, you can access your homepage contents as json via this url:

```
http://yourkirby.com/sirvy/home?service=json
```

In fact, you could access any page's contents as json:

```
http://yourkirby.com/sirvy/some/uri/here?service=json
```

The `/sirvy` route grabs the page object based on the uri and passes it into a service function. In this case it is being passed into the json service function. The json service takes a page object and returns the response as json.

## Services

Sirvy comes with 3 services built-in, but you can add as many services as you need!

#### json

```
/sirvy/home?service=json
```

returns page contents as json


#### resize

```
/sirvy/home?service=resize&image=image.jpg&width=400
```

returns a resized thumbnail


#### crop

```
/sirvy/home?service=crop&image=image.jpg&width=200&height=300
```

returns a cropped thumbnail

## Sirvy Page Method

Instead of manually building up the URLs for services, a Sirvy page method is exposed to make this super simple:

```php
<?php

page('home')->sirvy('json');
// returns
// -> http://yourkirby.com/sirvy/home?service=json
```

The first parameter is always the name of the service, the second *optional* parameter is for passing in an array of additional data:

```php
<?php

page('home')->sirvy('crop', [
  'image'  => page('home')->image()->filename(),
  'width'  => 200,
  'height' => 300
]);
// returns
// -> .../sirvy/home?service=crop&image=image.jpg&width=200&height=300
```

This is great for spitting out many thumbnails without blocking initial page render:

```php
<?php

foreach ($hundredsOfImages as $i) {
  echo '<img src="' . $page->sirvy('resize', [
    'image' => $i->filename(),
    'width' => 500
  ]) . '">';
}

```

## Custom Services

The real power of Sirvy is in defining your own services. You want an API to expose not just the current page's content as json, but all of its children and files as well? You can make a service for that! You want an API to expose the page's contents as a PHP array? You can make a service for that!

Services are registered 2 ways:

1. From PHP files in the `services` folder inside of the Sirvy plugin folder
2. By registering them as an option in your `config.php`

The 3 included services (json, resize, and crop) are all defined as files in the `services` folder. The name of the file becomes the name of the service (`json.php` -> `json`). To add your own services, just add more files to this folder. The file needs to return a function. Take a peek at the included services as examples.

You can also register services via an option in your `config.php`. The key of the array becomes the name of the service. Here is how you could add a `toArray` service that exposes the page's contents as a PHP array:

```php
<?php

c::set('sirvy.services', [
  'toArray' => function ($page, $data) {
    print_r($page->toArray());
  }
]);

// you can now use
page('home')->sirvy('toArray')

// which returns
// -> http://yourkirby.com/sirvy/home?service=toArray

// That url would respond with the page object as a plain PHP array!
```

## Options

```php
<?php

// change the sirvy path
c::set('sirvy.path', 's');
// -> http://yourkirby.com/s/home?service=json

c::set('sirvy.services', [
  'serviceName' => function ($page, $data) {
    // do something.
  }
]);

```

## Security

As [brought up](https://github.com/getkirby/kirby/issues/412) in the early 2.3 discussions, an API like this could potentially lead to misuse when considering image manipulation. I might look into adding some built-in options for rate limiting or auth, but in the meantime if this is a concern for you, nothing stopping you from adding the functionality into the services yourself!

## Todo

- Caching
- Rate Limiting? See Security

## Author

Jon Gacnik <http://jongacnik.com>