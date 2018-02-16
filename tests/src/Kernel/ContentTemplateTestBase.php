<?php

namespace Drupal\Tests\content_templates\Kernel;

use Drupal\Tests\node\Traits\ContentTypeCreationTrait;
use Drupal\Tests\token\Kernel\KernelTestBase;

/**
 * Simple base class for content template tests.
 */
class ContentTemplateTestBase extends KernelTestBase {
  use ContentTypeCreationTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'system',
    'field',
    'user',
    'node',
    'text',
    'filter',
    'content_templates',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installConfig(['node']);
    $this->installConfig(['filter']);
    $this->installEntitySchema('node');
    $this->installEntitySchema('user');
    $this->installSchema('node', 'node_access');
    $this->installSchema('system', 'sequences');

    $this->createContentType(['type' => 'page']);
  }
}
