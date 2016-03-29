<?php
/**
 * @file
 * Contains \Drupal\multiversion\AccountProxy
 */

namespace Drupal\multiversion;

use Drupal\Core\Session\AccountProxy as CoreAccountProxy;
use Drupal\multiversion\Entity\Storage\ContentEntityStorageInterface;

class AccountProxy extends CoreAccountProxy {

  /**
   * {@inheritdoc}
   */
  protected function loadUserEntity($account_id) {
    $storage = \Drupal::entityManager()->getStorage('user');

    // Always load proxy accounts from the default workspace to ensure
    // consistent session behaviour.
    if ($storage instanceof ContentEntityStorageInterface) {
      $workspace_id = \Drupal::getContainer()->getParameter('workspace.default');
      return $storage
        ->useWorkspace($workspace_id)
        ->load($account_id);
    }
    else {
      return $storage->load($account_id);
    }
  }
}
