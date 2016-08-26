<?php

return function ($page, $data) {
  function buildTree ($page) {
    if ($page) {
      $tree = $page->toArray();
      $tree['files'] = $page->files()->toArray();
      $tree['children'] = array_map(function ($n) {
        return buildTree(site()->find($n['id']));
      }, $page->children()->toArray());
      return $tree;
    }
  }
  return response::json(buildTree($page));
};
