<?php

return function ($page, $data) {
  return response::json($page->toArray());
};