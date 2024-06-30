<?php

declare(strict_types=1);

namespace UpiCore\Config;

use UpiCore\Exception\UpiException;
use stdClass;

class Config
{
    private static $configPath = null;

    protected static object $externalConfig;

    public function __construct(string $configPath = null)
    {
        // Initialize static configuration
        self::$externalConfig = new stdClass();

        // Check if config path is defined, either through parameter or constant
        if (\is_null($configPath) && !$defaultPath = \UpiCore\Ceremony\Utils\Destination\PathResolver::configPath()) {
            throw new UpiException('CONFIG_PATH_IS_NOT_DEFINED');
        }

        // Set config path if provided, or use the default if not
        self::$configPath = $configPath ?: $defaultPath;
    }

    /**
     * Brings a configuration from the config destination
     *
     * @param string $configName
     * 
     * @return mixed
     */
    public static function get(string $configName)
    {
        if (!self::$configPath) new self();

        $segments = explode('.', $configName);
        $file = $segments[0];
        $configKey = $segments[1] ?? null;

        $filePath = self::$configPath . DIRECTORY_SEPARATOR . $file . '.php';

        if (!file_exists($filePath)) {
            if (!\property_exists(self::$externalConfig, $configName))
                throw new UpiException('CONFIG_PATH_NOT_FOUND', $filePath);
            else
                $config = self::$externalConfig->{$configName};
        } else
            $config = json_decode(json_encode(require $filePath));

        if ($configKey) {
            return $config[$configKey] ?? null;
        }

        return $config;
    }

    /**
     * Defines a static property for keeping configuration in 
     * the project environment
     *
     * @param string $key
     * @param mixed $value
     * 
     * @return mixed
     */
    public static function set(string $key, mixed $value): mixed
    {
        if (!self::$configPath) new self();

        return self::$externalConfig->{$key} = $value;
    }
}
