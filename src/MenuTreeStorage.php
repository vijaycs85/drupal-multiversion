<?php

/**
 * @file
 * Contains \Drupal\multiversion\MenuTreeStorage.
 */

namespace Drupal\multiversion;

use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\Core\Menu\MenuTreeStorage as CoreMenuTreeStorage;

class MenuTreeStorage extends CoreMenuTreeStorage {

  /**
   * {@inheritdocs}
   */
  protected function buildTreeCacheId($menu_name, MenuTreeParameters $parameters) {
    $cid = parent::buildTreeCacheId($menu_name, $parameters);
    return "$cid." . $this->getActiveWorkspaceId();
  }

  /**
   * {@inheritdocs}
   */
  protected function buildLinksQuery($menu_name, MenuTreeParameters $parameters) {
    $query = parent::buildLinksQuery($menu_name, $parameters);
    $query->condition('workspace', $this->getActiveWorkspaceId());
    return $query;
  }

  /**
   * Helper method to get the active workspace ID.
   */
  protected function getActiveWorkspaceId() {
    return \Drupal::service('workspace.manager')->getActiveWorkspace()->id();
  }
}
