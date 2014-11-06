<?php

/**
 * @file
 * Contains \Drupal\Tests\multiversion\Unit\SessionWorkspaceNegotiatorTest.
 */

namespace Drupal\Tests\multiversion\Unit;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\multiversion\Workspace\SessionWorkspaceNegotiator;
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

    $container = new ContainerBuilder();
    $container->setParameter('workspace.default', 'default');
    \Drupal::setContainer($container);
    $this->workspaceNegotiator = new SessionWorkspaceNegotiator();
    $this->workspaceNegotiator->setContainer($container);
    $this->request = Request::create('<front>');
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
}
