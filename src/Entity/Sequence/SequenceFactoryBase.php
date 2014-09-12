<?php

namespace Drupal\multiversion\Entity\Sequence;

abstract class SequenceFactoryBase implements SequenceFactoryInterface {

  const DEFAULT_WORKSPACE = 'default';
  
  const DEFAULT_STORAGE_SERVICE = 'entity.sequence.storage.database';

  /**
   * @var string
   */
  protected $workspaceName;

  /**
   * @var string
   */
  protected $workspaceId;

  public function useWorkspace($workspace_name) {
    $this->workspaceName = $workspace_name;
    return $this;
  }

  public function currentWorkspace() {
    return $this->resolveWorkspaceName();
  }

  protected function resolveWorkspaceName($workspace_name = NULL) {
    if (empty($workspace_name) && !empty($this->workspaceName)) {
      return $this->workspaceName;
    }
    return self::DEFAULT_WORKSPACE;
  }

  abstract public function workspace($workspace_name = NULL);
}
