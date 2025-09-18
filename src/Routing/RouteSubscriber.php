<?php

namespace Drupal\pathauto_edit_links\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\Core\Routing\RoutingEvents;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    // Modify our routes to exclude admin paths and other system paths
    if ($route = $collection->get('pathauto_edit_links.edit_page')) {
      // Add negative lookahead to exclude admin, user, and other system paths
      $route->setRequirement('path', '^(?!admin|user|node\/\d+|batch|system).*');
      $route->setOption('_route_priority', 100);
    }
    
    if ($route = $collection->get('pathauto_edit_links.delete_page')) {
      $route->setRequirement('path', '^(?!admin|user|node\/\d+|batch|system).*');
      $route->setOption('_route_priority', 100);
    }
    
    if ($route = $collection->get('pathauto_edit_links.revisions_page')) {
      $route->setRequirement('path', '^(?!admin|user|node\/\d+|batch|system).*');
      $route->setOption('_route_priority', 100);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    $events = parent::getSubscribedEvents();
    // Run after other route subscribers
    $events[RoutingEvents::ALTER] = ['onAlterRoutes', -200];
    return $events;
  }

}
