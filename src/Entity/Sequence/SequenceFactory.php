<?php

namespace Drupal\multiversion\Entity\Sequence;

use Drupal\Core\Site\Settings;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SequenceFactory extends SequenceFactoryBase {

  /**
   * @var \Symfony\Component\DependencyInjection\ContainerInterface
   */
  protected $container;

  /**
   * @var \Drupal\Core\Site\Settings
   */
  protected $settings;

  public function __construct(ContainerInterface $container, Settings $settings) {
    $this->container = $container;
    $this->settings = $settings;
  }

  public function workspace($workspace_name = NULL) {
    // Resolve sequence storage service.
    if (!$service_name = $this->settings->get('entity.sequence.storage')) {
      $service_name = self::DEFAULT_STORAGE_SERVICE;
    }
    return $this->container->get($service_name)->workspace($this->resolveWorkspaceName($workspace_name));
  }
}
