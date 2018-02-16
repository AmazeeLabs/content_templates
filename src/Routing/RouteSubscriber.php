<?php

namespace Drupal\content_templates\Routing;


use Drupal\content_templates\Controller\ContentTemplatesNodeController;
use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

class RouteSubscriber extends RouteSubscriberBase {

  protected function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('node.add')) {
      $route->setDefault('_controller', ContentTemplatesNodeController::class . '::add');
    }
  }

}