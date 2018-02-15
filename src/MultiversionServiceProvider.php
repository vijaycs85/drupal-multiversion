<?php

namespace Drupal\multiversion;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Defines a service profiler for the multiversion module.
 */
class MultiversionServiceProvider extends ServiceProviderBase {

  public function alter(ContainerBuilder $container) {
    $renderer_config = $container->getParameter('renderer.config');
    $renderer_config['required_cache_contexts'][] = 'workspace';
    $container->setParameter('renderer.config', $renderer_config);

    // Switch the menu tree storage to our own that respect Workspace cache
    // contexts.
    $definition = $container->getDefinition('menu.tree_storage');
    $definition->setClass('Drupal\multiversion\MenuTreeStorage');

    // Override the path.alias_storage class with a new class.
    $definition = $container->getDefinition('path.alias_storage');
    $definition->setClass('Drupal\multiversion\AliasStorage')
      ->addArgument(new Reference('workspace.manager'))
      ->addArgument(new Reference('entity_type.manager'))
      ->addArgument(new Reference('state'));

    // Override the router.route_provider class with a new class.
    $definition = $container->getDefinition('router.route_provider');
    $definition->setClass('Drupal\multiversion\RouteProvider')
      ->setArguments([
        new Reference('database'),
        new Reference('state'),
        new Reference('path.current'),
        new Reference('cache.data'),
        new Reference('path_processor_manager'),
        new Reference('cache_tags.invalidator'),
        'router',
        new Reference('workspace.manager'),
      ]);

    // Override the comment.statistics class with a new class.
    try {
      $definition = $container->getDefinition('comment.statistics');
      $definition->setClass('Drupal\multiversion\CommentStatistics');
    }
    catch (InvalidArgumentException $e) {
      // Do nothing, comment module is not installed.
    }
  }

}
