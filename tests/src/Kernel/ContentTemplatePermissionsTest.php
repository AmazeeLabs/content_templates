<?php

namespace Drupal\Tests\content_templates\Kernel;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Prophecy\Argument;

/**
 * Test content template permissions.
 *
 * @group content_templates
 */
class ContentTemplatePermissionsTest extends ContentTemplateTestBase {

  /**
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $account;

  protected function setUp() {
    parent::setUp();
    $this->account = $this->prophesize(AccountProxyInterface::class);
    $this->container->set('current_user', $this->account->reveal());
    $this->account->id()->willReturn(2);

    // By default our test user is not allowed to do anything.
    $this->account->hasPermission(Argument::cetera())
      ->willReturn(FALSE);
    // Allow access to content.
    $this->account->hasPermission('access content')
      ->willReturn(TRUE);
  }


  /**
   * Verify that default node access stays the same.
   */
  public function testVanillaNodeAccess() {
    $node = Node::create([
      'title' => 'Test',
      'type' => 'page',
      'status' => NodeInterface::PUBLISHED,
    ]);
    $node->save();

    $this->assertTrue($node->access('view'));
    $this->assertFalse($node->access('update'));
    $this->assertFalse($node->access('delete'));
  }

  /**
   * By default, users can't see or edit templates.
   */
  public function testAnonymousTemplateAccess() {
    $node = Node::create([
      'title' => 'Test',
      'type' => 'page',
      'template' => TRUE,
      'status' => NodeInterface::PUBLISHED,
    ]);
    $node->save();

    $this->assertFalse($node->access('view'));
    $this->assertFalse($node->access('update'));
    $this->assertFalse($node->access('delete'));
  }

  /**
   * Test if "create content from template" roles can view a template.
   */
  public function testTemplateViewAccess()  {
    $node = Node::create([
      'title' => 'Test',
      'type' => 'page',
      'template' => TRUE,
      'status' => NodeInterface::PUBLISHED,
    ]);

    $node->save();
    $this->account->hasPermission('create content from templates', Argument::cetera())
      ->willReturn(TRUE);

    $this->assertTrue($node->access('view'));
    $this->assertFalse($node->access('update'));
    $this->assertFalse($node->access('delete'));
  }

  /**
   * Test if "administer content templates" roles can view a template.
   */
  public function testTemplateEditAccess()  {
    $node = Node::create([
      'title' => 'Test',
      'type' => 'page',
      'template' => TRUE,
      'status' => NodeInterface::PUBLISHED,
    ]);

    $node->save();

    $this->account->hasPermission('administer content templates', Argument::cetera())
      ->willReturn(TRUE);

    $this->assertTrue($node->access('view'));
    $this->assertTrue($node->access('update'));
    $this->assertTrue($node->access('delete'));
  }


}