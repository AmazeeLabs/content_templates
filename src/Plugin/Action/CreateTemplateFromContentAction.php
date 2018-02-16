<?php

namespace Drupal\content_templates\Plugin\Action;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Action\ActionBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\NodeInterface;
use Drupal\replicate\Replicator;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @Action(
 *   id = "content_templates_create",
 *   type = "node",
 *   label = @Translation("Create templates from content."),
 * )
 */
class CreateTemplateFromContentAction extends ActionBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\replicate\Replicator
   */
  protected $replicator;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    Replicator $replicator
  ) {
    $this->replicator = $replicator;
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('replicate.replicator')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function access(
    $object,
    AccountInterface $account = NULL,
    $return_as_object = FALSE
  ) {
    if ($account && $account->hasPermission('administer content templates')) {
      if ($return_as_object) {
        return AccessResult::allowed();
      }
      else {
        return TRUE;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    if ($entity instanceof NodeInterface) {
      $template = $this->replicator->cloneEntity($entity);
      $template->template = TRUE;
      $template->created = time();
      $template->save();
    }
  }

}