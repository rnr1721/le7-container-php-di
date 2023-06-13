<?php

use Core\Interfaces\ConfigInterface;
use Core\Interfaces\UrlInterface;
use Core\Interfaces\ViewTopologyInterface;
use Psr\Container\ContainerInterface;
use Core\View\ViewTopologyGeneric;
use function DI\factory;

return [
    ViewTopologyInterface::class => factory(function (ContainerInterface $c) {
        /** @var ConfigInterface $config */
        $config = $c->get(ConfigInterface::class);
        /** @var UrlInterface $url */
        $url = $c->get(UrlInterface::class);
        $viewTopology = new ViewTopologyGeneric();
        $viewTopology->setBaseUrl($url->get())
                ->setCssUrl($url->css())
                ->setFontsUrl($url->fonts())
                ->setImagesUrl($url->images())
                ->setJsUrl($url->js())
                ->setLibsUrl($url->libs())
                ->setThemeUrl($url->theme())
                ->setTemplatePath($config->stringf('loc.templates') ?? '')
                ->setTemplatePath($config->string('loc.templates_base') ?? '')
                ->setTemplatePath($config->array('globals.viewDirs') ?? '');
        return $viewTopology;
    }),
];
