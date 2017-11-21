<?php

return \Statamic\API\YAML::Parse("
collections:
  blog: '/blog/{year}/{month}/{day}/{slug}'
  things: '/things/{slug}'
taxonomies:
  tags: '/blog/tags/{slug}'
routes:
  /blog/tags: blog/taxonomies
  /search: search
  /users: user/index
  /users/{username}: user/profile
  /account: user/account
  /login: auth/login
  /register: auth/register
  /forgot-password: auth/password-forgot
  /reset-password: auth/password-reset
  /blog/feed:
    layout: feed
    template: feeds/blog
    content_type: atom
  /things/feed:
    layout: feed
    template: feeds/things
    content_type: atom
");