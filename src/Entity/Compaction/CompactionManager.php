<?php

namespace Drupal\multiversion\Entity\Compaction;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Entity\EntityManagerInterface;

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

  public function __construct(ConfigFactoryInterface $config_factory, EntityManagerInterface $entity_manager, QueryFactory $query_factory) {
    $this->configFactory = $config_factory;
    $this->entityManager = $entity_manager;
    $this->queryFactory = $query_factory;
  }

  public function compact() {
    $limit = $this->configFactory->get('multiversion.settings')->get('compaction_batch_size');

    foreach ($this->entityManager->getDefinitions() as $entity_type => $definition) {
      // @todo: Figure out how to detect content entity types here.
      if ($entity_type == 'node') {
        // Query deleted entities.
        $ids = $this->queryFactory->get($entity_type)
          ->range(0, $limit)
          ->isDeleted()
          ->execute();

        // Load the deleted entities.
        $storage = $this->entityManager->getStorage($entity_type);
        $entities = $storage->loadMultipleDeleted($ids);

        // Purge the entities.
        $storage->purge($entities);
      }
    }
  }
}
