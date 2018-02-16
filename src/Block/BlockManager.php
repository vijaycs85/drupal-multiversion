<?php

namespace Drupal\multiversion\Block;

use Drupal\Core\Block\BlockManager as CoreBlockManager;

/**
 * Adds the workspace ID to the cache key.
 *
 * @see \Drupal\Core\Block\BlockPluginInterface
 */
class BlockManager extends CoreBlockManager {

  /**
   * {@inheritdoc}
   */
  protected function setCachedDefinitions($definitions) {
    $active_workspace = \Drupal::service('workspace.manager')->getActiveWorkspace();
    $this->cacheKey = 'block_plugins:' . $active_workspace->id();
    parent::setCachedDefinitions($definitions);
  }

  /**
   * {@inheritdoc}
   */
  public function clearCachedDefinitions() {
    $active_workspace = \Drupal::service('workspace.manager')->getActiveWorkspace();
    if (isset($active_workspace)) {
      $this->cacheKey = 'block_plugins:' . $active_workspace->id();
    }
    parent::clearCachedDefinitions();
  }

  /**
   * {@inheritdoc}
   */
  protected function getCachedDefinitions() {
    $active_workspace = \Drupal::service('workspace.manager')->getActiveWorkspace();
    $this->cacheKey = 'block_plugins:' . $active_workspace->id();
    parent::getCachedDefinitions();
  }

}
