<?php

namespace Drupal\multiversion\Entity;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorage as CoreSqlContentEntityStorage;
use Drupal\multiversion\Entity\Transaction\TransactionManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SqlContentEntityStorage extends CoreSqlContentEntityStorage implements ContentEntityStorageInterface {

  use ContentEntityStorageTrait;

  /**
   * @var \Drupal\multiversion\Entity\Transaction\TransactionManagerInterface
   */
  protected $transactionManager;

  /**
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   * @param \Drupal\Core\Database\Connection $database
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   * @param \Drupal\multiversion\Entity\Transaction\TransactionManagerInterface $transaction_manager
   */
  public function __construct(EntityTypeInterface $entity_type, Connection $database, EntityManagerInterface $entity_manager, CacheBackendInterface $cache, TransactionManagerInterface $transaction_manager) {
    parent::__construct($entity_type, $database, $entity_manager, $cache);
    $this->transactionManager = $transaction_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('database'),
      $container->get('entity.manager'),
      $container->get('cache.entity'),
      $container->get('entity.transaction.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getQueryServiceName() {
    return 'entity.query.sql.multiversion';
  }

  public function getTransactionManager() {
    return $this->transactionManager;
  }

  /**
   * {@inheritdoc}
   */
  protected function buildQuery($ids, $revision_id = FALSE) {
    $query = parent::buildQuery($ids, $revision_id);

    if ($this->entityType->isTranslatable()) {
      $table = $this->getRevisionDataTable();
      $alias = 'revision_data';
      if ($revision_id) {
        $query->join($table, $alias, "$alias.{$this->revisionKey} = revision.{$this->revisionKey} AND $alias.{$this->revisionKey} = :revisionId");
      }
      else {
        $query->join($table, $alias, "$alias.{$this->revisionKey} = revision.{$this->revisionKey}");
      }
    }
    else {
      $alias = 'revision';
    }
    $query->condition("$alias._deleted", $this->loadDeleted ? 1 : 0);
    return $query;
  }

}
