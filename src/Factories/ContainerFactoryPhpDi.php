<?php

namespace Core\Factories;

use Core\Interfaces\ContainerFactoryInterface;
use Psr\Container\ContainerInterface;
use DI\ContainerBuilder;
use \RuntimeException;
use function array_merge,
             is_array,
             glob;

class ContainerFactoryPhpDi implements ContainerFactoryInterface
{

    private string $diCompiledFolder;
    private array $configDir;

    public function __construct(
            string $configDir,
            string $diCompiledFolder
    )
    {
        $this->configDir = [
            'app' => $configDir,
            'system' => __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'di'
        ];
        $this->diCompiledFolder = $diCompiledFolder;
    }

    public function getContainer(bool $isProduction): ContainerInterface
    {

        $definitions = $this->getDefinitions();

        $builder = new ContainerBuilder();

        $builder->useAttributes(true);

        if ($isProduction) {
            $builder->enableCompilation($this->diCompiledFolder);
            $builder->writeProxiesToFile(true, $this->diCompiledFolder);
        }

        $builder->addDefinitions($definitions);
        return $builder->build();
    }

    public function getDefinitions(): array
    {
        $filesApp = $this->getDir('app');
        $filesSystem = $this->getDir('system');

        $defApp = $this->scanDir($filesApp);
        $defSystem = $this->scanDir($filesSystem);

        if (!$defApp || !$defSystem) {
            throw new RuntimeException("Application or system DI directory not found");
        }

        return array_replace($defApp, $defSystem);
    }

    public function scanDir(array $files): array
    {
        $definitions = [];
        foreach ($files as $filename) {
            if ($filename !== '.' && $filename !== '..') {
                if (!file_exists($filename)) {
                    throw new RuntimeException('File not found:' . $filename);
                }
                $array = require $filename;
                if (is_array($array)) {
                    $definitions = array_merge($definitions, $array);
                }
            }
        }
        return $definitions;
    }

    private function getDir(string $place): array
    {
        return glob($this->configDir[$place] . DIRECTORY_SEPARATOR . "*Conf.php");
    }
}
