<?php

/**
 * @file
 * Contains \Drupal\multiversion\MultiversionServiceProvider.
 */

namespace Drupal\multiversion;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

/**
 * Defines a service profiler for the multiversion module.
 */
class MultiversionServiceProvider extends ServiceProviderBase {

  public function alter(ContainerBuilder $container) {
    try {
      // Override the comment.statistics class with a new class.
      $definition = $container->getDefinition('comment.statistics');
      $definition->setClass('Drupal\multiversion\CommentStatistics');

      // Override the password_migrate class with a new class.
      $definition = $container->getDefinition('password_migrate');
      $definition->setClass('Drupal\multiversion\MigratePassword');
    }
    catch (InvalidArgumentException $e) {
      // Do nothing, comment or migrate module is not installed.
    }
  }

}
