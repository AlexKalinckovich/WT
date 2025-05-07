<?php
declare(strict_types=1);

namespace utils;

require_once __UTILS__ . '/SingletonTrait.php';
require_once __UTILS__ . '/Disposable.php';
require_once __ROOT__ . '/vendor/autoload.php';
require_once __UTILS__ . '/Logger.php';


use Exception;
use RuntimeException;

final class ClassLoader implements Disposable {
    use SingletonTrait;

    private static array $instances = [];
    private static array $disposableObjects = [];
    private static array $definitions = [];
    private static string $configPath = __CONFIG__ . '/classes.yaml';

    protected function __construct() {
        $this->init();
    }

    public function init(): void {
        if (!extension_loaded('yaml')) {
            throw new RuntimeException("YAML extension is not loaded");
        }

        if (!file_exists(self::$configPath)) {
            throw new RuntimeException("YAML config missing: " . self::$configPath);
        }

        $config = yaml_parse_file(self::$configPath);
        if ($config === false) {
            throw new RuntimeException("Invalid YAML syntax");
        }

        self::$definitions = $config['classes'] ?? [];
        self::$instances[ClassLoader::class] = $this;
        spl_autoload_register([self::class, 'loadClass']);
    }
    private function loadClass(string $className): void {
        if (isset(self::$definitions[$className]['file'])) {
            require_once __ROOT__ . self::$definitions[$className]['file'];
        }
    }

    /**
     * @throws Exception
     */
    public function get(string $classKey): object {
        if (isset(self::$instances[$classKey])) {
            return self::$instances[$classKey];
        }

        if (!isset(self::$definitions[$classKey])) {
            throw new Exception("Class definition not found: " . $classKey);
        }

        $classConfig = self::$definitions[$classKey];
        $dependencies = [];

        foreach ($classConfig['dependencies'] as $dependency) {
            $dependencies[] = $this->get($dependency);
        }

        $object = $this->createInstance($classConfig['class'], $dependencies);

        if ($object instanceof Disposable) {
            self::$disposableObjects[$classKey] = $object;
        }

        self::$instances[$classKey] = $object;
        return $object;
    }

    private function createInstance(string $className, array $dependencies): object {
        if (method_exists($className, 'getInstance')) {
            return $className::getInstance(...$dependencies);
        }

        return new $className(...$dependencies);
    }

    public function dispose(): void {
        foreach (self::$disposableObjects as $object) {
            $object->dispose();
        }
        self::$instances = [];
        self::$disposableObjects = [];
    }
}