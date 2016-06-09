<?php

require_once realpath(__DIR__) . DS . 'sirvy-cache.php';

class SirvyRunner {

  private $query;
  private $services;
  private $service;
  private $cache;
  private $canCache = false;

  public function __construct ($path, $services) {

    $this->page = page($path);

    if (!$this->page) return;

    $this->query = kirby()->request()->query();
    $this->services = $services;
    $this->service = $this->findService();
    $this->cache = new SirvyCache(kirby()->request()->url());

  }

  protected function findService () {
    if ($service = $this->query->service()) {
      if (array_key_exists($service, $this->services)) {
        if (is_callable($this->services[$service])) {
          $this->canCache = $this->query->cache();
          return $this->services[$service];
        } else {
          return function () use ($service) {
            return $this->error("\"{$service}\" is not a valid Sirvy service function");
          };
        }
      } else {
        return function () use ($service) {
          return $this->error("\"{$service}\" service does not exist");
        };
      }
    }
  }

  protected function runService () {
    if ($this->canCache && $this->cache->exists()) {
      $content = $this->cache->get();
      return $content;
    } else {
      $function = $this->service;
      $response = $function($this->page, $this->query);
      if ($this->canCache && $this->isResponse($response)) {
        $this->cache->set($response, kirby()->option('sirvy.cache.duration', null));
      }
      return $response;
    }
  }

  // is response a Kirby response object?
  protected function isResponse ($response) {
    return is_object($response) && get_class($response) == 'Response';
  }

  protected function error ($message) {
    return new Response([
      'error' => $message
    ], 'json', 400);
  }

  public function run () {
    if ($this->page) {
      if ($this->service) {
        return $this->runService();
      } else {
        return $this->error("No service specified");
      }
    } else {
      return $this->error("Sirvy can't find that page");
    }
  }

}