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

Sirvy comes with 4 services built-in, but you can add as many services as you need!

#### tree

```
/sirvy/home?service=tree
```

returns page contents, files, and children recursively as json

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
page('home')->sirvy('json');
// returns
// -> http://yourkirby.com/sirvy/home?service=json
```

The first parameter is always the name of the service, the second *optional* parameter is for passing in an array of additional data:

```php
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
<? foreach ($hundredsOfImages as $i) :
  $thumb = $page->sirvy('resize', [
    'image' => $i->filename(),
    'width' => 500
  ]) ?>
  <img src="<?= $thumb ?>">
<? endforeach ?>

```

## Custom Services

The real power of Sirvy is in defining your own services. You want an API to expose not just the current page's content as json, but all of its children and files as well? You can make a service for that! You want an API to expose the page's contents as a PHP array? You can make a service for that!

Services are registered 2 ways:

1. From PHP files in the `services` folder inside of the Sirvy plugin folder
2. By registering them as an option in your `config.php`

The 3 included services (json, resize, and crop) are all defined as files in the `services` folder. The name of the file becomes the name of the service (`json.php` -> `json`). To add your own services, just add more files to this folder. The file needs to return a function. Unless you are returning media files, you will typically want this function to return a [Kirby response object](https://getkirby.com/docs/toolkit/api#response). Take a peek at the included services as examples.

You can also register services via an option in your `config.php`. The key of the array becomes the name of the service. Here is how you could add a `toArray` service that exposes the page's contents as a PHP array:

```php
c::set('sirvy.services', [
  'toArray' => function ($page, $data) {
    return new Response(print_r($page->toArray()));
  }
]);

// you can now use
page('home')->sirvy('toArray')

// which returns
// -> http://yourkirby.com/sirvy/home?service=toArray

// That url would respond with the page object as a plain PHP array!
```

## Caching

Caching is enabled on a per-service basis using the Kirby file cache. This is great if you want to cache partials like json or html snippets:

```
/sirvy/home?service=json&cache=1
```

This json response will be cached in the Kirby cache directory.

By default the cached items will never expire, but you can change this with an option. The cache is always flushed when making content changes via the panel.

Caching will only work when your service returns a [Kirby response object](https://getkirby.com/docs/toolkit/api#response). I also wouldn't recommend trying to cache a non-text response (like image or video). I haven't put in catches so weirdness will likely happen.

## Options

```php
// change the sirvy path
c::set('sirvy.path', 's');
// -> http://yourkirby.com/s/home?service=json

// expire cached responses after an hour
c::set('sirvy.cache.duration', 60);

// register additional services
c::set('sirvy.services', [
  'serviceName' => function ($page, $data) {
    return new Response('content');
  }
]);

// enable cors on sirvy requests
c::set('sirvy.cors', '*');

```

## Errors

If Sirvy encounters an error, such as being unable to find a page uri or service, a json error response  will be returned (status code 400).

## Security

As [brought up](https://github.com/getkirby/kirby/issues/412) in the early 2.3 discussions, an API like this could potentially lead to misuse when considering image manipulation. I might look into adding some built-in options for rate limiting or auth, but in the meantime if this is a concern for you, nothing stopping you from adding the functionality into the services yourself!

## Todo

- Rate Limiting? See Security

## Author

Jon Gacnik <http://jongacnik.com>
