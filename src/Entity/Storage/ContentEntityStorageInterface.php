<?php

namespace Drupal\multiversion\Entity\Storage;

use Drupal\Core\Entity\EntityStorageInterface;

interface ContentEntityStorageInterface extends EntityStorageInterface {

  const STATUS_MISSING = 0;

  const STATUS_IN_TRANSACTION = 1;

  const STATUS_AVAILABLE = 2;

  const STATUS_DELETED = 3;

  /**
   * @param integer $id
   * @return \Drupal\Core\Entity\ContentEntityInterface|null
   */
  public function loadDeleted($id);

  /**
   * @param array $ids
   * @return \Drupal\Core\Entity\ContentEntityInterface[]
   */
  public function loadMultipleDeleted(array $ids = NULL);

  /**
   * @param \Drupal\Core\Entity\ContentEntityInterface[] $entities
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function purge($entities);

  /**
   * @param array $revision_ids
   */
  public function onTransactionCommit(array $revision_ids);

}
