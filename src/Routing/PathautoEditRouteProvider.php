<?php

namespace Drupal\pathauto_edit_links\Routing;

use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\path_alias\AliasManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Dynamic route provider for pathauto edit URLs.
 */
class PathautoEditRouteProvider implements RouteProviderInterface {

  /**
   * The path alias manager.
   *
   * @var \Drupal\path_alias\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * Constructs a PathautoEditRouteProvider object.
   *
   * @param \Drupal\path_alias\AliasManagerInterface $alias_manager
   *   The path alias manager.
   */
  public function __construct(AliasManagerInterface $alias_manager) {
    $this->aliasManager = $alias_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function getRouteCollectionForRequest(Request $request) {
    $collection = new RouteCollection();
    $path = $request->getPathInfo();

    // Check if this looks like a pathauto edit URL
    if (preg_match('/^(.+)\/(edit|delete)$/', $path, $matches)) {
      $alias = $matches[1];
      $action = $matches[2];
      
      // Skip admin and system paths
      if (preg_match('/^\/?(admin|user|batch|system|core|modules|themes|sites)/', $alias)) {
        return $collection;
      }
      
      // Check if this alias resolves to a node
      $system_path = $this->aliasManager->getPathByAlias($alias);
      
      if (preg_match('/^\/node\/(\d+)$/', $system_path, $node_matches)) {
        // Create a dynamic route for this specific pathauto URL
        $route_name = 'pathauto_edit_links.dynamic_' . $action . '_' . $node_matches[1];
        
        $route = new Route(
          $path,
          [
            '_form' => $action === 'edit' 
              ? '\Drupal\pathauto_edit_links\Form\PathautoNodeEditForm'
              : '\Drupal\pathauto_edit_links\Form\PathautoNodeDeleteForm',
            '_title_callback' => '\Drupal\pathauto_edit_links\Controller\PathautoEditController::getTitle',
            'path' => ltrim($alias, '/'),
          ],
          [
            '_custom_access' => $action === 'edit'
              ? '\Drupal\pathauto_edit_links\Controller\PathautoEditController::checkEditAccess'
              : '\Drupal\pathauto_edit_links\Controller\PathautoEditController::checkDeleteAccess',
          ]
        );
        
        $collection->add($route_name, $route);
      }
    }

    return $collection;
  }

  /**
   * {@inheritdoc}
   */
  public function getRoutesByNames($names) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getRoutesByPattern($pattern) {
    return [];
  }

}
