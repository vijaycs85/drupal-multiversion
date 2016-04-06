<?php

/**
 * @file
 * Contains \Drupal\multiversion\Entity\MenuLinkContent.
 */

namespace Drupal\multiversion\Entity;

use Drupal\menu_link_content\Entity\MenuLinkContent as CoreMenuLinkContent;

class MenuLinkContent extends CoreMenuLinkContent {

  /**
   * {@inheritdoc}
   */
  public function getPluginId() {
    return 'menu_link_content:' . $this->uuid() . ':' . $this->id();
  }

}
