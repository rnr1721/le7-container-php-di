<?php

use Core\Interfaces\BundleManagerInterface;
use Core\Interfaces\ErrorHandlerFactoryInterface;
use Core\Interfaces\RequestInterface;
use Core\Interfaces\HttpOutputInterface;
use Core\Interfaces\ResponseEmitterInterface;
use Core\Interfaces\RouteHttpInterface;
use Core\Interfaces\RouteCliInterface;
use Core\Interfaces\RouteRepositoryInterface;
use Core\Interfaces\LocalesInterface;
use Core\Interfaces\MessageFactoryInterface;
use Core\Interfaces\MessageCollectionInterface;
use Core\Interfaces\MessageCollectionFlashInterface;
use Core\Interfaces\MiddlewareFactoryInterface;
use Core\Interfaces\ConfigInterface;
use Core\Interfaces\UrlInterface;
use Core\ErrorHandler\ErrorHandlerCli;
use Core\ErrorHandler\ErrorHandlerHttp;
use Core\Request;
use Core\HttpOutput;
use Core\Bag\RouteBag;
use Core\Bundles\BundleManager;
use Core\Routing\RouteRepository;
use Core\Factories\ErrorHandlerFactoryDefault;
use Core\Locales\LocalesDefault;
use Core\Messages\MessageFactoryGeneric;
use Core\Links\UrlBuilder;
use Core\Response\ResponseEmitterGeneric;
use Core\Factories\MiddlewareFactoryDefault;
use Core\Config\ConfigFactoryGeneric;
use Psr\SimpleCache\CacheInterface;
use Psr\Container\ContainerInterface;
use function DI\factory;
use function DI\get;
use function DI\autowire;

return [
    ConfigInterface::class => factory(function (ContainerInterface $c) {
        $cache = $c->get(CacheInterface::class);
        $factory = new ConfigFactoryGeneric($cache);
        if (!defined('BASE_PATH')) {
            throw new RuntimeException('Please define BASE_PATH constant');
        }
        return $factory->harvest(BASE_PATH . DS . 'config');
    }),
    MiddlewareFactoryInterface::class => autowire(MiddlewareFactoryDefault::class),
    MessageFactoryInterface::class => get(MessageFactoryGeneric::class),
    MessageCollectionInterface::class => factory(function (ContainerInterface $c): MessageCollectionInterface {
        $factory = $c->get(MessageFactoryInterface::class);
        return $factory->getMessagesSession();
    }),
    MessageCollectionFlashInterface::class => factory(function (ContainerInterface $c): MessageCollectionFlashInterface {
        $factory = $c->get(MessageFactoryInterface::class);
        return $factory->getMessagesSession();
    }),
    LocalesInterface::class => get(LocalesDefault::class),
    UrlInterface::class => get(UrlBuilder::class),
    RouteHttpInterface::class => factory(function (ContainerInterface $c) {
        /** @var RouteBag $bag */
        $bag = $c->get(RouteBag::class);
        return $bag->getRoute();
    }),
    RouteCliInterface::class => factory(function (ContainerInterface $c) {
        /** @var RouteBag $bag */
        $bag = $c->get(RouteBag::class);
        return $bag->getRoute();
    }),
    ResponseEmitterInterface::class => get(ResponseEmitterGeneric::class),
    ErrorHandlerFactoryInterface::class => get(ErrorHandlerFactoryDefault::class),
    ErrorHandlerCli::class => factory(function (ContainerInterface $c) {
        /** @var ErrorHandlerFactoryInterface $factory */
        $factory = $c->get(ErrorHandlerFactoryInterface::class);
        return $factory->getErrorHandlerCli();
    }),
    ErrorHandlerHttp::class => factory(function (ContainerInterface $c) {
        /** @var ErrorHandlerFactoryInterface $factory */
        $factory = $c->get(ErrorHandlerFactoryInterface::class);
        return $factory->getErrorHandlerHttp($c->get(RouteHttpInterface::class));
    }),
    HttpOutputInterface::class => get(HttpOutput::class),
    RequestInterface::class => get(Request::class),
    RouteRepositoryInterface::class => get(RouteRepository::class),
    BundleManagerInterface::class => get(BundleManager::class)
];
