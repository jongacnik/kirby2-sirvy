<?php

require_once realpath(__DIR__) . DS . 'sirvy-runner.php';

class Sirvy {

  private $services = [];
  private $path;

  public function __construct () {

    $this->path = kirby()->option('sirvy.path', 'sirvy');

    $this->registerServices();
    $this->registerRoute();

  }

  protected function registerRoute () {

    kirby()->routes([
      [
        'pattern' => $this->path . '/(:all?)',
        'action' => function ($path = null) {
          $runner = new SirvyRunner($path, $this->services);

          // Logans Run
          return $runner->run();
        }
      ]
    ]);

  }

  protected function registerServices () {

    $serviceFiles = glob(realpath(__DIR__) . DS . '../services' . DS . '*.php');

    // add services from directory
    foreach($serviceFiles as $service) {
      $this->services[basename($service, '.php')] = require_once($service);
    }

    // add services from options
    $this->services = array_merge($this->services, kirby()->option('sirvy.services', []));

  }

  public function getUrl ($page, $service, $options) {

    $base = site()->url() . DS . $this->path;
    $pageUri = $page ? $page->uri() : '';
    $serviceQuery = 'service=' . $service;
    $optionsQuery = http_build_query($options);

    return $base . DS . $pageUri . '?' . $serviceQuery . ($optionsQuery ? '&' . $optionsQuery : '');

  }

  public function flushCache () {
    $dir = kirby()->roots()->cache() . DS . 'sirvy';
    if (!file_exists($dir)) mkdir($dir);
    $cache = \cache::setup('file', ['root' => $dir]);
    $cache->flush();
  }

}