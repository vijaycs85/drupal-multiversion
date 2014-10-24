<?php

namespace Drupal\multiversion;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class WorkspaceManager implements WorkspaceManagerInterface, ContainerAwareInterface {

  use ContainerAwareTrait;

  /**
   * @var string
   */
  protected $activeId;

  /**
   * {@inheritdoc}
   */
  public function getActiveId() {
    return $this->activeId ?: $this->container->getParameter('workspace.default');
  }

  /**
   * {@inheritdoc}
   */
  public function setActiveId($id) {
    $this->activeId = $id;
    return $this;
  }

}
