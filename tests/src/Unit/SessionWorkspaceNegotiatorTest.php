<?php

/**
 * @file
 * Contains \Drupal\Tests\multiversion\Unit\SessionWorkspaceNegotiatorTest.
 */

namespace Drupal\Tests\multiversion\Unit;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\multiversion\Workspace\SessionWorkspaceNegotiator;
use Drupal\multiversion\Workspace\WorkspaceManager;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @coversDefaultClass \Drupal\multiversion\Workspace\SessionWorkspaceNegotiator
 * @group multiversion
 */
class SessionWorkspaceNegotiatorTest extends UnitTestCase {

  /**
   * The workspace negotiator.
   *
   * @var \Drupal\multiversion\Workspace\SessionWorkspaceNegotiator
   */
  protected $workspaceNegotiator;

  /**
   * The request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * The workspace entity.
   *
   * @var \Drupal\multiversion\Entity\Workspace|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $workspace;

  /**
   * The workspace id.
   *
   * @var string
   */
  protected $id;
  protected $entityManager;
  protected $requestStack;
  protected $workspaceManager;
  protected $path;
  protected $entityType;
  protected $negotiator;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->id = $this->randomMachineName();
    $values = array(
      'id' => $this->id,
      'label' => $this->id,
      'created' => (int) microtime(TRUE) * 1000000,
    );

    $methods = get_class_methods('\Drupal\multiversion\Entity\Workspace');
    $this->workspace = $this->getMock('\Drupal\multiversion\Entity\Workspace', $methods, array($values, 'workspace'));

    $new_id = $this->randomMachineName();
    $new_values = array(
      'id' => $new_id,
      'label' => $new_id,
      'created' => (int) microtime(TRUE) * 1000000,
    );
    $new_expected_links = array(
      $this->id => array(
        'href' => $this->path,
        'title' => $this->id,
        'query' => array(),
        'attributes' => array(
          'class' => array('session-active'),
        ),
      ),
      $new_id => array(
        'href' => $new_id,
        'title' => $new_id,
        'query' => array(
          'workspace' => $new_id
        ),
      ),
    );

    $methods = get_class_methods('\Drupal\multiversion\Entity\Workspace');
    $this->workspace = $this->getMock('\Drupal\multiversion\Entity\Workspace', $methods, array($new_values, 'workspace'));

    $this->path = '<front>';
    $this->request = Request::create($this->path);

    $this->entityType = $this->getMock('\Drupal\multiversion\Entity\WorkspaceInterface');
    $this->entityManager = $this->getMock('\Drupal\Core\Entity\EntityManagerInterface');
    $this->entityManager->expects($this->any())
      ->method('getDefinition')
      ->with('workspace')
      ->will($this->returnValue($this->entityType));
    $this->requestStack = $this->getMock('\Symfony\Component\HttpFoundation\RequestStack');

    $methods = get_class_methods('\Drupal\multiversion\Workspace\WorkspaceManagerInterface');
    $this->workspaceManager = $this->getMock(
      '\Drupal\multiversion\Workspace\WorkspaceManagerInterface',
      $methods,
      array($this->requestStack, $this->entityManager)
    );

    $container = new ContainerBuilder();
    $container->setParameter('workspace.default', 'default');
    $container->set('entity.manager', $this->entityManager);
    $container->set('workspace.manager', $this->workspaceManager);
    $container->set('request_stack', $this->requestStack);
    \Drupal::setContainer($container);

    $this->workspaceNegotiator = new SessionWorkspaceNegotiator();
    $this->workspaceNegotiator->setContainer($container);
  }

  /**
   * Tests the applies() method.
   *
   * @covers ::applies()
   */
  public function testApplies() {
    $this->assertTrue($this->workspaceNegotiator->applies($this->request));
  }

  /**
   * Tests the getWorkspaceId() method.
   *
   * @covers ::getWorkspaceId()
   */
  public function testGetWorkspaceId() {
    $this->assertSame('default', $this->workspaceNegotiator->getWorkspaceId($this->request));
  }

  /**
   * Tests the persist() method.
   *
   * @covers ::persist()
   */
  public function testPersist() {
    $this->workspace->expects($this->once())
      ->method('id')
      ->will($this->returnValue($this->id));
    $this->assertTrue($this->workspaceNegotiator->persist($this->workspace));
  }

  /**
   * Tests the getWorkspaceSwitchLinks() method.
   *
   * @covers ::getWorkspaceSwitchLinks()
   *
   * @todo Add tests for more than one workspace
   */
  public function testGetWorkspaceSwitchLinks() {
    $query = array();
    parse_str($this->request->getQueryString(), $query);
    $expected_links = array(
      $this->id => array(
        'href' => $this->path,
        'title' => $this->id,
        'query' => $query,
        'attributes' => array(
          'class' => array('session-active'),
        ),
      ),
    );

    $this->workspaceManager->addNegotiator($this->workspaceNegotiator, 1);
    $this->workspaceManager->setActiveWorkspace($this->workspace);

    $methods = get_class_methods('\Drupal\multiversion\Workspace\SessionWorkspaceNegotiator');
    $this->negotiator = $this->getMock('\Drupal\multiversion\Workspace\SessionWorkspaceNegotiator', $methods);
    $this->negotiator->setWorkspaceManager($this->workspaceManager);
    $this->negotiator->expects($this->any())
      ->method('setWorkspaceManager')
      ->with($this->workspaceManager);

    $this->workspace->expects($this->any())
      ->method('id')
      ->will($this->returnValue($this->id));

    $this->negotiator->expects($this->any())
      ->method('getActiveWorkspace')
      ->with($this->requestStack, $this->entityManager)
      ->will($this->returnValue($this->id));

    $storage = $this->getMock('\Drupal\Core\Entity\EntityStorageInterface');
    $storage->expects($this->any())
      ->method('loadMultiple')
      ->with()
      ->will($this->returnValue(array($this->workspace)));

    $this->entityManager->expects($this->any())
      ->method('getStorage')
      ->with('workspace')
      ->will($this->returnValue($storage));

    $this->workspaceManager->expects($this->any())
      ->method('loadMultiple')
      ->with()
      ->will($this->returnValue(array($this->workspace)));

    $workspace_manager = new WorkspaceManager($this->requestStack, $this->entityManager);
    $workspace_manager->addNegotiator($this->workspaceNegotiator, 1);
    $workspace_manager->setActiveWorkspace($this->workspace);
    $negotiator = new SessionWorkspaceNegotiator();
    $negotiator->setWorkspaceManager($workspace_manager);

    $links = $negotiator->getWorkspaceSwitchLinks($this->request, $this->path);
    $this->assertSame($expected_links, $links);
  }
}
