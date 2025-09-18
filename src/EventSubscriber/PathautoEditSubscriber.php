<?php

namespace Drupal\pathauto_edit_links\EventSubscriber;

use Drupal\path_alias\AliasManagerInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Url;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Event subscriber for pathauto edit URLs.
 */
class PathautoEditSubscriber implements EventSubscriberInterface {

  /**
   * The path alias manager.
   *
   * @var \Drupal\path_alias\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * Constructs a PathautoEditSubscriber object.
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
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['onRequest', 100];
    return $events;
  }

  /**
   * Handles the request event.
   *
   * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
   *   The request event.
   */
  public function onRequest(RequestEvent $event) {
    $request = $event->getRequest();
    $path = $request->getPathInfo();

    // Only process paths that end with /edit, /delete, or /revisions
    // Skip if this is already a /node/ID/action path to prevent loops
    if (preg_match('/^\/node\/\d+\/(edit|delete|revisions)$/', $path)) {
      return;
    }

    // Check if this is a path ending with /edit, /delete, or /revisions
    if (preg_match('/^(.+)\/(edit|delete|revisions)$/', $path, $matches)) {
      $alias = $matches[1];
      $action = $matches[2];
      
      // Skip admin paths, system paths, and webform paths
      if (preg_match('/^\/?(admin|user|batch|system|core|modules|themes|sites|webform)/', $alias)) {
        return;
      }
      
      // Get the system path from the alias
      $system_path = $this->aliasManager->getPathByAlias($alias);
      
      // Only proceed if the alias actually resolves to a different system path
      if ($alias === $system_path) {
        return;
      }
      
      // Check if this is a node path
      if (preg_match('/^\/node\/(\d+)$/', $system_path, $node_matches)) {
        $node_id = $node_matches[1];
        
        // Load the node to check if it exists
        $node_storage = \Drupal::entityTypeManager()->getStorage('node');
        $node = $node_storage->load($node_id);
        
        if ($node) {
          try {
            // Create a proper sub-request that maintains the full Drupal context
            $kernel = \Drupal::service('http_kernel');
            $sub_request = $request->duplicate();
            
            // Set the sub-request to the node edit path
            $node_path = '/node/' . $node_id . '/' . $action;
            $sub_request->server->set('REQUEST_URI', $node_path);
            $sub_request->server->set('PATH_INFO', $node_path);
            
            // Preserve the original request context (session, user, etc.)
            $sub_request->attributes->set('_controller_request', $request);
            
            // Handle the sub-request to get the full page with proper theme and layout
            $response = $kernel->handle($sub_request, HttpKernelInterface::SUB_REQUEST);
            
            // Modify the response content to fix any internal links that point to node URLs
            if ($response->getStatusCode() === 200) {
              $content = $response->getContent();
              
              // Replace any node URLs in the content with pathauto URLs
              $content = str_replace(
                ['/node/' . $node_id . '/edit', '/node/' . $node_id . '/delete', '/node/' . $node_id . '/revisions'],
                [$alias . '/edit', $alias . '/delete', $alias . '/revisions'],
                $content
              );
              
              // Also fix form action URLs
              $content = preg_replace(
                '/action="[^"]*\/node\/' . $node_id . '\/' . $action . '"/',
                'action="' . $alias . '/' . $action . '"',
                $content
              );
              
              $response->setContent($content);
            }
            
            // Modify any redirects in the response to use pathauto URLs
            if ($response->headers->has('location')) {
              $location = $response->headers->get('location');
              if (strpos($location, '/node/' . $node_id) !== false) {
                $new_location = str_replace('/node/' . $node_id, $alias, $location);
                $response->headers->set('location', $new_location);
              }
            }
            
            $event->setResponse($response);
          } catch (\Exception $e) {
            // If sub-request fails, fall back to redirect
            $route_name = '';
            switch ($action) {
              case 'edit':
                $route_name = 'entity.node.edit_form';
                break;
              case 'delete':
                $route_name = 'entity.node.delete_form';
                break;
              case 'revisions':
                $route_name = 'entity.node.version_history';
                break;
            }
            
            if ($route_name) {
              $url = Url::fromRoute($route_name, ['node' => $node_id]);
              $response = new TrustedRedirectResponse($url->toString());
              $event->setResponse($response);
            }
          }
        }
      }
    }
  }

}
