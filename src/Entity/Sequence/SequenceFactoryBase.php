<?php

namespace Drupal\multiversion\Entity\Sequence;

abstract class SequenceFactoryBase implements SequenceFactoryInterface {

  const DEFAULT_REPOSITORY = 'default';
  
  const DEFAULT_STORAGE_SERVICE = 'entity.sequence.storage.database';

  /**
   * @var string
   */
  protected $repositoryId;

  public function useRepository($repository_name) {
    $this->repositoryName = $repository_name;
    return $this;
  }

  public function currentRepository() {
    return $this->resolveRepositoryName();
  }

  protected function resolveRepositoryName($repository_name = NULL) {
    if (empty($repository_name) && !empty($this->repositoryName)) {
      return $this->repositoryName;
    }
    return self::DEFAULT_REPOSITORY;
  }

  abstract public function repository($repository_name = NULL);
}
