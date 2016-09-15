<?php

$components = new Componentizer\Components;
while (have_posts()) :
  the_post();
  $components->build();
endwhile;