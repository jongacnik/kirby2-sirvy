<?php

return function ($page, $data) {
  if ($image = $page->image($data->image())) {
    if ($data->width()) {
      $image->resize($data->width(), $data->height(), $data->quality())->show();
    } else {
      $image->show();
    }
  } else {
    echo "Sirvy can't find that image!";
  }
};