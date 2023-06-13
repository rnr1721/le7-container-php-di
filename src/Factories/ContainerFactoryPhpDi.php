<?php

namespace Core\Factories;

use Core\Interfaces\ContainerFactoryInterface;
use Psr\Container\ContainerInterface;
use DI\ContainerBuilder;
use \RuntimeException;
use function array_merge,
             is_array,
             glob;

/**
 * Factory to make PHP-DI Container instance from two dirs - app and system
 */
class ContainerFactoryPhpDi implements ContainerFactoryInterface
{

    /**
     * Directory for compiled container
     * @var string
     */
    private string $diCompiledFolder;

    /**
     * Array of paths with DI configs
     * 
     * @var array
     */
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

    /**
     * Get ready-to-use container with definitions
     * 
     * @param bool $isProduction Production flag for compile container
     * @return ContainerInterface
     */
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

    /**
     * Get PHP-DI container definitions
     * 
     * @return array
     * @throws RuntimeException
     */
    protected function getDefinitions(): array
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

    /**
     * Scan dir and return single config array from one folder
     * 
     * @param array $files 
     * @return array Files list
     * @throws RuntimeException
     */
    protected function scanDir(array $files): array
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

    /**
     * Get list of config directory files
     * 
     * @param string $place Directory with PHP-DI configs
     * @return array List of files
     */
    protected function getDir(string $place): array
    {
        return glob($this->configDir[$place] . DIRECTORY_SEPARATOR . "*Conf.php");
    }
}
