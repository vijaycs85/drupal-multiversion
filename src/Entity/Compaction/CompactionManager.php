<?php

namespace Drupal\multiversion\Entity\Compaction;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\multiversion\MultiversionManagerInterface;

class CompactionManager implements CompactionManagerInterface {

  /**
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * @var \Drupal\Core\Entity\EntityManagerInterface 
   */
  protected $entityManager;

  /**
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  protected $queryFactory;

  public function __construct(ConfigFactoryInterface $config_factory, EntityManagerInterface $entity_manager, MultiversionManagerInterface $multiversion_manager, QueryFactory $query_factory) {
    $this->configFactory = $config_factory;
    $this->entityManager = $entity_manager;
    $this->multiversionManager = $multiversion_manager;
    $this->queryFactory = $query_factory;
  }

  /**
   * {@inheritdoc}
   */
  public function compact() {
    $count = 0;
    $limit = $this->configFactory->get('multiversion.settings')->get('compaction_batch_size');

    foreach ($this->entityManager->getDefinitions() as $entity_type_id => $entity_type) {
      if ($this->multiversionManager->isSupportedEntityType($entity_type)) {
        // Query deleted entities.
        $ids = $this->queryFactory->get($entity_type_id)
          ->range(0, $limit)
          ->isDeleted()
          ->execute();

        // Load the deleted entities.
        $storage = $this->entityManager->getStorage($entity_type_id);
        $entities = $storage->loadMultipleDeleted($ids);

        // Purge the entities.
        if (!empty($entities)) {
          $storage->purge($entities);
          $count = $count + count($entities);
        }
      }
    }

    return $count;
  }
}
