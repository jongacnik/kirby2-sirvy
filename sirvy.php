<?php

/**
 * Sirvy ~ Kirby Services API
 *
 * Maps PHP functions to a services route to do all sorts
 * of things like on the fly thumbnail resizing (ala early
 * 2.3 beta) or returning a page as json. *Docs in repo.*
 *
 * @author Jon Gacnik <jon@folderstudio.com>
 * @link https://github.com/jongacnik/sirvy
 * @version 1.0.0
 *
 */

class Sirvy {

  private $services;
  private $path;

  public function __construct() {

    $this->path = kirby()->option('sirvy.path', 'sirvy');

    $this->registerRoute();
    $this->registerServices();

  }

  protected function registerRoute () {

    kirby()->routes([
      [
        'pattern' => $this->path . '/(:all?)',
        'action' => function ($path = null) {
          return $this->run($path);
        }
      ]
    ]);

  }

  protected function registerServices () {

    $serviceFiles = glob(realpath(__DIR__) . DS . 'services' . DS . '*.php');

    // add services from directory
    foreach($serviceFiles as $service) {
      $this->services[basename($service, '.php')] = require_once($service);
    }

    // add services from options
    $this->services = array_merge($this->services, kirby()->option('sirvy.services', []));

  }

  protected function run ($path) {

    if ($page = page($path)) :
      $query = kirby()->request()->query();
      if ($service = $query->service()) :
        if (array_key_exists($service, $this->services)) :
          if (is_callable($this->services[$service])) :
            return $this->services[$service]($page, $query);
          else :
            echo "{$service} is not a valid Sirvy service function";
          endif;
        else :
          echo "{$service} service does not exist";
        endif;
      endif;
    else :
      echo "Sirvy can't find that page!";
    endif;

  }

  public function url ($page, $service, $options) {

    $base = site()->url() . DS . $this->path;
    $pageUri = $page ? $page->uri() : '';
    $serviceQuery = 'service=' . $service;
    $optionsQuery = http_build_query($options);

    return $base . DS . $pageUri . '?' . $serviceQuery . ($optionsQuery ? '&' . $optionsQuery : '');

  }

}

// Sirvy instance
$sirvy = new Sirvy();

// Sirvy page method
$sirvyPageMethod = function($page, $service = '', $options = []) use ($sirvy) {
  return $sirvy->url($page, $service, $options);
};

$kirby->set('page::method', 'sirvy', $sirvyPageMethod);
