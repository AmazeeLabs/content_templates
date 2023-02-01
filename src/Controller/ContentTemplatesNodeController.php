<?php

namespace Drupal\content_templates\Controller;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\Render\RendererInterface;
use Drupal\node\Controller\NodeController;
use Drupal\node\NodeInterface;
use Drupal\node\NodeTypeInterface;
use Drupal\replicate\Replicator;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Custom override of the node controller to step into node creation process.
 */
class ContentTemplatesNodeController extends NodeController {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\replicate\Replicator
   */
  protected $replicator;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('date.formatter'),
      $container->get('renderer'),
      $container->get('entity_type.manager'),
      $container->get('replicate.replicator')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(
    DateFormatterInterface $date_formatter,
    RendererInterface $renderer,
    EntityTypeManagerInterface $entityTypeManager,
    Replicator $replicator
  ) {
    $this->entityTypeManager = $entityTypeManager;
    $this->replicator = $replicator;
    parent::__construct($date_formatter, $renderer);
  }

  /**
   * {@inheritdoc}
   */
  public function add(NodeTypeInterface $node_type) {
    /** @var \Drupal\node\NodeStorageInterface $storage */
    $storage = $this->entityTypeManager->getStorage('node');
    $query = $storage->getQuery();
    $query->accessCheck();
    $query->condition('type', $node_type->id());
    $query->condition('template', TRUE);

    $templates = $query->execute();


    if (count($templates) == 1) {
      return $this->redirect('node.add_from_template', [
        'node_type' => $node_type->id(),
        'node' => array_shift($templates),
      ]);
    }
    else if ($templates) {
      $build = [
        'description' => [
          '#markup' => '<p>' . $this->t('Please choose a template to create the new "@type" from.', [
            '@type' => $node_type->label(),
          ]),
        ],
        'list' => [
          '#theme' => 'item_list',
          '#items' => [],
        ],
      ];

      foreach ($storage->loadMultiple($templates) as $template) {
        $build['list']['#items'][$template->id()] = Link::createFromRoute($template->label(), 'node.add_from_template', [
          'node_type' => $node_type->id(),
          'node' => $template->id(),
        ])->toRenderable();
      }

      return $build;
    }
    else {
      return parent::add($node_type);
    }
  }

  /**
   * Create a new node from a given template node.
   */
  public function addFromTemplate(NodeTypeInterface $node_type, NodeInterface $node) {
    $clone = $this->replicator->cloneEntity($node);

    if (!$clone) {
      \Drupal::messenger()->addStatus($this->t('Could not load @type template with id @id.', [
        '@type' => $node_type->id(),
        '@id' => $node->id(),
      ]));
      $clone = \Drupal::entityTypeManager()->getStorage('node')->create([
        'type' => $node_type->id(),
      ]);
    }

    // Add the same title as the template node, but with a '(copy)' suffix.
    $clone->setTitle($this->t('@title (copy)', ['@title' => $clone->label()]));
    // Also, the new node we want to save should not be by default a template.
    $clone->set('template', FALSE);
    $form = $this->entityFormBuilder()->getForm($clone);
    return $form;
  }

  /**
   * Page title when creating a node from a template.
   */
  public function addFromTemplatePageTitle(NodeTypeInterface $node_type, NodeInterface $node) {
    return $this->t('Create @name from template "@template"', [
      '@name' => $node_type->label(),
      '@template' => $node->label(),
    ]);
  }

}