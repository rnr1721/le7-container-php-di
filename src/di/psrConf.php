<?php

use Core\Interfaces\ConfigInterface;
use Core\Interfaces\ListenerProviderInterface;
use Core\EventDispatcher\EventDispatcher;
use Core\Bag\RequestBag;
use Core\EventDispatcher\Providers\ListenerProviderDefault;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use function DI\factory;
use function DI\get;

return [
    ServerRequestInterface::class => factory(function (ContainerInterface $c) {
        /** @var RequestBag $requestBag */
        $requestBag = $c->get(RequestBag::class);
        return $requestBag->getServerRequest();
    }),
    EventDispatcherInterface::class => get(EventDispatcher::class),
    ResponseInterface::class => factory(function (ContainerInterface $c) {
        /** @var ResponseFactoryInterface $factory */
        $factory = $c->get(ResponseFactoryInterface::class);
        return $factory->createResponse(404);
    }),
    ListenerProviderInterface::class => factory(function (ContainerInterface $c) {
        /** @var ConfigInterface $config */
        $config = $c->get(ConfigInterface::class);
        $events = $config->array('events') ?? [];
        $listeners = new ListenerProviderDefault();
        foreach ($events as $key => $eventValue) {
            $listeners->on($eventValue[0], $eventValue[1], $key);
        }
        return $listeners;
    }),
];
