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
    // Set very high priority for our routes to ensure they're matched first
    if ($route = $collection->get('pathauto_edit_links.edit_page')) {
      $route->setOption('_route_priority', 1000);
    }
    
    if ($route = $collection->get('pathauto_edit_links.delete_page')) {
      $route->setOption('_route_priority', 1000);
    }
    
    if ($route = $collection->get('pathauto_edit_links.revisions_page')) {
      $route->setOption('_route_priority', 1000);
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
