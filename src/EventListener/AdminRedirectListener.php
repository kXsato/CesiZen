<?php

namespace App\EventListener;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;

class AdminRedirectListener implements EventSubscriberInterface
{
    private const USER_DASHBOARD_PREFIX = '/mon-compte';
    private const EXCLUDED_ROUTES = ['account_export', 'account_delete'];

    public function __construct(
        private Security $security,
        private RouterInterface $router,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::REQUEST => 'onKernelRequest'];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        if (!str_starts_with($request->getPathInfo(), self::USER_DASHBOARD_PREFIX)) {
            return;
        }

        if (in_array($request->attributes->get('_route'), self::EXCLUDED_ROUTES, true)) {
            return;
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            $event->setResponse(new RedirectResponse($this->router->generate('admin')));
        }
    }
}
