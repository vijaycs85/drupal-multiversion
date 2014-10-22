<?php

namespace Drupal\multiversion\Entity\Query;

trait QueryTrait {

  /**
   * @var null|string
   */
  protected $workspaceId = NULL;

  /**
   * @var boolean
   */
  protected $isDeleted = FALSE;

  /**
   * @var boolean
   */
  protected $isTransacting = FALSE;

  /**
   *
   */
  public function useWorkspace($id) {
    $this->workspaceId = $id;
    return $this;
  }

  /**
   * @see \Drupal\multiversion\Entity\Query\QueryInterface::isDeleted()
   */
  public function isDeleted() {
    $this->isDeleted = TRUE;
    return $this;
  }

  /**
   * @see \Drupal\multiversion\Entity\Query\QueryInterface::isNotDeleted()
   */
  public function isNotDeleted() {
    $this->isDeleted = FALSE;
    return $this;
  }

  /**
   * @see \Drupal\multiversion\Entity\Query\QueryInterface::isTransacting()
   */
  public function isTransacting() {
    $this->isTransacting = TRUE;
    return $this;
  }

  /**
   * @see \Drupal\multiversion\Entity\Query\QueryInterface::isNotTransacting()
   */
  public function isNotTransacting() {
    $this->isTransacting = FALSE;
    return $this;
  }

  public function prepare() {
    parent::prepare();
    $this->condition('_deleted', (int) $this->isDeleted);
    $this->condition('_trx', (int) $this->isTransacting);
    $this->condition('_workspace', $this->workspaceId ?: \Drupal::service('multiversion.manager')->getActiveWorkspaceName());
    return $this;
  }

}
