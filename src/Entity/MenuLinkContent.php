<?php

/**
 * @file
 * Contains \Drupal\multiversion\Entity\MenuLinkContent.
 */

namespace Drupal\multiversion\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\menu_link_content\Entity\MenuLinkContent as CoreMenuLinkContent;

class MenuLinkContent extends CoreMenuLinkContent {

  /**
   * {@inheritdoc}
   */
  public function getPluginId() {
    // Make the plugin ID unique adding the entity ID.
    return 'menu_link_content:' . $this->uuid() . ':' . $this->id();
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    // If the $this->link->uri contains the entity object, presave it to
    // replace the entity object with the uri.
    // @see \Drupal\multiversion\LinkItem::preSave().
    if (is_object($this->link->uri)) {
      $this->link->preSave();
    }
    parent::preSave($storage);
  }

}
