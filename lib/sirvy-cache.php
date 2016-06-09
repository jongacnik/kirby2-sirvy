<?php

class SirvyCache {

  public function __construct ($url) {

    $this->key = md5($url);

    $dir = kirby()->roots()->cache() . DS . 'sirvy';

    if (!file_exists($dir)) mkdir($dir);

    // Cache setup
    $this->cache = \cache::setup('file', ['root' => $dir]);

    // Cache clean-up
    if ($this->cache->expired($this->key)) {
      $this->cache->remove($this->key);
    }

  }

  public function __call($name, $args = []) {
    return call_user_func_array([$this->cache, $name], array_merge([$this->key], $args));
  }

}