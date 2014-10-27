<?php

namespace Drupal\multiversion\Workspace;

use Drupal\multiversion\Entity\WorkspaceInterface;

interface WorkspaceManagerInterface {

  /**
   * @return \Drupal\multiversion\Entity\WorkspaceInterface
   */
  public function getActiveWorkspace();

  /**
   * @param \Drupal\multiversion\Entity\WorkspaceInterface $workspace
   * @return \Drupal\multiversion\WorkspaceManagerInterface
   */
  public function setActiveWorkspace(WorkspaceInterface $workspace);

  /**
   * @param \Drupal\multiversion\Workspace\WorkspaceNegotiatorInterface $negotiator
   * @param int $priority
   */
  public function addNegotiator(WorkspaceNegotiatorInterface $negotiator, $priority);

}
