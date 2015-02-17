<?php

namespace Drupal\multiversion\Entity\Storage;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\multiversion\Entity\Exception\ConflictException;

trait ContentEntityStorageTrait {

  /**
   * @var boolean
   */
  protected $isDeleted = FALSE;

  /**
   * {@inheritdoc}
   */
  public function getQueryServiceName() {
    return 'entity.query.sql.multiversion';
  }

  /**
   * {@inheritdoc}
   */
  protected function buildQuery($ids, $revision_id = FALSE) {
    $query = parent::buildQuery($ids, $revision_id);

    // Prevent to modify the query before entity type updates.
    if ($ids === NULL) {
      return $query;
    }

    $revision_alias = 'revision';
    if ($this->entityType->isTranslatable()) {
      // Join the revision data table in order to set the delete condition.
      $revision_table = $this->getRevisionDataTable();
      $revision_alias = 'revision_data';
      if ($revision_id) {
        $query->join($revision_table, $revision_alias, "$revision_alias.{$this->revisionKey} = revision.{$this->revisionKey} AND $revision_alias.{$this->revisionKey} = :revisionId", array(':revisionId' => $revision_id));
      }
      else {
        $query->join($revision_table, $revision_alias, "$revision_alias.{$this->revisionKey} = revision.{$this->revisionKey}");
      }
    }
    $query->condition("$revision_alias._deleted", (int) $this->isDeleted);
    // Entities in other workspaces than the active one can only be queried with
    // the Entity Query API and not by the storage handler itself.
    $query->condition("$revision_alias.workspace", $this->getActiveWorkspaceId());
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function onTransactionCommit(array $revision_ids) {
    foreach ($revision_ids as $id => $revision) {
      $this->database->update($this->baseTable)
        ->fields(array(
          $this->revisionKey => $revision,
        ))
        ->condition($this->idKey, $id)
        ->execute();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getActiveWorkspaceId() {
    return \Drupal::service('workspace.manager')->getActiveWorkspace()->id();
  }

  /**
   * {@inheritdoc}
   */
  public function loadMultiple(array $ids = NULL) {
    $this->isDeleted = FALSE;
    return parent::loadMultiple($ids);
  }

  /**
   * {@inheritdoc}
   */
  public function loadDeleted($id) {
    $entities = $this->loadMultipleDeleted(array($id));
    return isset($entities[$id]) ? $entities[$id] : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function loadMultipleDeleted(array $ids = NULL) {
    $this->isDeleted = TRUE;
    return parent::loadMultiple($ids);
  }

  /**
   * {@inheritdoc}
   */
  protected function doSave($id, EntityInterface $entity) {
    // Entities are always saved as new revisions when using a Multiversion
    // storage handler.
    $entity->setNewRevision();
    return parent::doSave($id, $entity);
  }

  /**
   * {@inheritdoc}
   */
  public function delete(array $entities) {
    // Entites are always "deleted" as new revisions when using a Multiversion
    // storage handler.
    foreach ($entities as $entity) {
      $entity->_deleted->value = TRUE;
      $this->save($entity);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function deleteRevision($revision_id) {
    throw new ConflictException(NULL, 'Revisions can not be deleted when using a Multiversion storage handler.');
  }

  /**
   * {@inheritdoc}
   */
  public function purge($entities) {
    // Purge equals that of a traditional delete when using a Multiversion
    // storage handler.
    return parent::delete($entities);
  }

  /**
   * {@inheritdoc}
   */
  public function resetCache(array $ids = NULL) {
    parent::resetCache($ids);
    $ws = $this->getActiveWorkspaceId();
    if ($this->entityType->isStaticallyCacheable() && isset($ids)) {
      foreach ($ids as $id) {
        unset($this->entities[$ws][$id]);
      }
    }
    else {
      $this->entities[$ws] = array();
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getFromStaticCache(array $ids) {
    $ws = $this->getActiveWorkspaceId();
    $entities = array();
    // Load any available entities from the internal cache.
    if ($this->entityType->isStaticallyCacheable() && !empty($this->entities[$ws])) {
      $entities += array_intersect_key($this->entities[$ws], array_flip($ids));
    }
    return $entities;
  }

  /**
   * {@inheritdoc}
   */
  protected function setStaticCache(array $entities) {
    if ($this->entityType->isStaticallyCacheable()) {
      $ws = $this->getActiveWorkspaceId();
      if (!isset($this->entities[$ws])) {
        $this->entities[$ws] = array();
      }
      $this->entities[$ws] += $entities;
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function buildCacheId($id) {
    $ws = $this->getActiveWorkspaceId();
    return "values:{$this->entityTypeId}:$id:$ws";
  }

  /**
   * {@inheritdoc}
   */
  protected function setPersistentCache($entities) {
    if (!$this->entityType->isPersistentlyCacheable()) {
      return;
    }
    $ws = $this->getActiveWorkspaceId();
    $cache_tags = array(
      $this->entityTypeId . '_values',
      'entity_field_info',
      'workspace_' . $ws,
    );
    foreach ($entities as $id => $entity) {
      $this->cacheBackend->set($this->buildCacheId($id), $entity, CacheBackendInterface::CACHE_PERMANENT, $cache_tags);
    }
  }
}
