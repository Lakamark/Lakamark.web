<?php

namespace App\Http\Dashboard\Firewall;

use App\Http\dashboard\Controller\BaseController;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * To limit the user to get access to the dashboard.
 */
readonly class DashboardRequestEventListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            ControllerEvent::class => 'onController',
            RequestEvent::class => 'onRequest',
        ];
    }

    public function __construct(
        private string $dashboardPrefix,
        private AuthorizationCheckerInterface $authChecker,
    ) {
    }

    public function onRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $uri = '/'.trim($event->getRequest()->getRequestUri(), '/').'/';
        $prefix = '/'.trim($this->dashboardPrefix, '/').'/';

        if (
            substr($uri, 0, mb_strlen($prefix)) === $prefix
            && !$this->authChecker->isGranted('CMS_MANAGE')
        ) {
            $exception = new AccessDeniedException();
            $exception->setSubject($event->getRequest());
            throw $exception;
        }
    }

    /**
     * Check if the user can get access to the dashboard.
     *
     * This firewall is attached to the RequestEvent.
     * Sometimes an action in a controller is not prefixed by the dashboard path.
     */
    public function onController(ControllerEvent $event): void
    {
        if (false === $event->isMainRequest()) {
            return;
        }

        $controller = $event->getController();
        if (
            !is_array($controller)
            && $controller[0] instanceof BaseController
            && !$this->authChecker->isGranted('CMS_MANAGE')
        ) {
            $exception = new AccessDeniedException();
            $exception->setSubject($event->getRequest());
            throw $exception;
        }
    }
}
