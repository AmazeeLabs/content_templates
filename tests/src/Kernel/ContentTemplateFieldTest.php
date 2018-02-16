<?php

namespace Drupal\Tests\content_templates\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\Tests\node\Traits\ContentTypeCreationTrait;

/**
 * Test the content template field handling.
 *
 * @group content_templates
 */
class ContentTemplateFieldTest extends ContentTemplateTestBase {

  /**
   * Verify that by default the template field is `false` and the node
   * keeps it's published state.
   */
  function testNoTemplate() {
    $node = Node::create([
      'title' => 'Test',
      'type' => 'page',
      'status' => NodeInterface::PUBLISHED,
    ]);
    $node->save();

    $this->assertFalse((bool) $node->template->value);
    $this->assertTrue((bool) $node->status->value);
  }

  /**
   * Verify initial creation of a template.
   */
  function testTemplate() {
    $node = Node::create([
      'title' => 'Test',
      'type' => 'page',
      'status' => NodeInterface::NOT_PUBLISHED,
      'template' => TRUE,
    ]);
    $node->save();

    $this->assertTrue((bool) $node->template->value);
    $this->assertFalse((bool) $node->status->value);
  }

  /**
   * Verify that status is "unpublished" if a node is save as a template.
   */
  function testAutoUnpublishOnInitialSave() {
    $node = Node::create([
      'title' => 'Test',
      'type' => 'page',
      'status' => NodeInterface::PUBLISHED,
      'template' => TRUE,
    ]);
    $node->save();

    $this->assertTrue((bool) $node->template->value);
    $this->assertFalse((bool) $node->status->value);
  }

  /**
   * Verify that status becomes unpublished when a node is turned into a
   * template.
   */
  function testAutoUnpublishOnSave() {
    $node = Node::create([
      'title' => 'Test',
      'type' => 'page',
      'status' => NodeInterface::PUBLISHED,
      'template' => FALSE,
    ]);
    $node->save();

    $node->template = TRUE;
    $node->save();

    $this->assertTrue((bool) $node->template->value);
    $this->assertFalse((bool) $node->status->value);
  }
}