<?php

/**
 * @file
 * Contains \Drupal\multiversion\MultiversionServiceProvider.
 */

namespace Drupal\multiversion;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Defines a service profiler for the multiversion module.
 */
class MultiversionServiceProvider extends ServiceProviderBase {

  public function alter(ContainerBuilder $container) {
    // Override the comment.statistics class with a new class.
    $definition = $container->getDefinition('comment.statistics');
    $definition->setClass('Drupal\multiversion\CommentStatistics');
  }

}
