# Componentizer


1. `Components\build();` saves to `the_content` from the admin
  * Build happens pre-save, so needs to run after the save
1. Sync configurations between environments with JSON
1. Migrate existing `the_content` to component
1. Taxonomy meta & descriptions