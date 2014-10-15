[![Build Status](https://travis-ci.org/dickolsson/drupal-multiversion.svg?branch=8.x-1.x)](https://travis-ci.org/dickolsson/drupal-multiversion)

Multiversion
============

Extends the revision model for content entities.

## Content staging

This module is part of [the content staging suite for D8](https://www.drupal.org/project/deploy#d8).

## Presentations

- https://austin2014.drupal.org/session/content-staging-drupal-8
- https://amsterdam2014.drupal.org/session/content-staging-drupal-8-continued

## Development

Module require following drupal core patches
* https://www.drupal.org/node/2335879
* https://www.drupal.org/node/2342543
* https://www.drupal.org/node/1869548

There is a drupal core fork with these patches being applied so you can use it
for development of this module (see .travis.yml).
git clone --depth 1 --branch 2335879-2342543-1869548 https://github.com/dickolsson/drupal-core.git
