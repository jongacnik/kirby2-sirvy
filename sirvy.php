<?php

/**
 * Sirvy ~ Kirby Services API
 *
 * @author Jon Gacnik <jon@folderstudio.com>
 * @link https://github.com/jongacnik/sirvy
 * @version 1.1.1
 *
 */

require_once realpath(__DIR__) . DS . 'lib/core.php';

// new Sirvy instance
$sirvy = new Sirvy();

// define Sirvy page method
$sirvyPageMethod = function($page, $service = '', $options = []) use ($sirvy) {
  return $sirvy->getUrl($page, $service, $options);
};

// set Sirvy page method
$kirby->set('page::method', 'sirvy', $sirvyPageMethod);

// flush Sirvy cache hooks
$flushHooks = [
  'panel.page.create',
  'panel.page.update',
  'panel.page.delete',
  'panel.page.sort',
  'panel.page.hide',
  'panel.page.move',
  'panel.file.upload',
  'panel.file.replace',
  'panel.file.rename',
  'panel.file.update',
  'panel.file.sort',
  'panel.file.delete'
];

foreach ($flushHooks as $flushHook) {
  $kirby->hook($flushHook, function ($page) use ($sirvy) {
    $sirvy->flushCache();
  });
}