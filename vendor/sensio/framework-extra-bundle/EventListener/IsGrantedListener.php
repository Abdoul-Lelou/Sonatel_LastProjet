<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sensio\Bundle\FrameworkExtraBundle\EventListener;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Handles the IsGranted annotation on controllers.
 *
 * @author Ryan Weaver <ryan@knpuniversity.com>
 */
class IsGrantedListener implements EventSubscriberInterface
{
    private $authChecker;

    public function __construct(AuthorizationCheckerInterface $authChecker = null)
    {
        $this->authChecker = $authChecker;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        $request = $event->getRequest();
        /** @var $configurations IsGranted[] */
        if (!$configurations = $request->attributes->get('_is_granted')) {
            return;
        }

        if (null === $this->authChecker) {
            throw new \LogicException('To use the @IsGranted tag, you need to install symfony/security-bundle.');
        }

        foreach ($configurations as $configuration) {
            $subject = null;
            if ($configuration->getSubject()) {
                if (!$request->attributes->has($configuration->getSubject())) {
                    $allAttributes = $request->attributes->all();
                    // remove one attribute we know is noise in the error message
                    unset($allAttributes['_is_granted']);
                    throw new \RuntimeException(sprintf('Could not find the subject "%s" for the @IsGranted annotation. Available attributes are: %s', $configuration->getSubject(), implode(', ', array_keys($allAttributes))));
                }

                $subject = $request->attributes->get($configuration->getSubject());
            }

            if (!$this->authChecker->isGranted($configuration->getAttributes(), $subject)) {
                $argsString = $this->getIsGrantedString($configuration);

                $message = $configuration->getMessage() ?: sprintf('Access Denied by controller annotation @IsGranted(%s)', $argsString);

                if ($statusCode = $configuration->getStatusCode()) {
                    throw new HttpException($statusCode, $message);
                }

                throw new AccessDeniedException($message);
            }
        }
    }

    private function getIsGrantedString(IsGranted $isGranted)
    {
        $attributes = array_map(function ($attribute) {
            return sprintf('"%s"', $attribute);
        }, (array) $isGranted->getAttributes());
        if (1 === count($attributes)) {
            $argsString = reset($attributes);
        } else {
            $argsString = sprintf('[%s]', implode(', ', $attributes));
        }

        if (null !== $isGranted->getSubject()) {
            $argsString = sprintf('%s, %s', $argsString, $isGranted->getSubject());
        }

        return $argsString;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(KernelEvents::CONTROLLER => 'onKernelController');
    }
}