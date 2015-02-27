<?php

namespace configMigrations\migration;
use configMigrations\interfaces\IModuleHelper;
use configMigrations\MigrationsException;

/**
 * ObjectFactory
 * Создает объект-обертку над миграцией
 */
class ObjectFactory 
{
    /**
     * @var IModuleHelper
     */
    private $moduleHelper;

    /**
     *
     */
    function __construct(IModuleHelper $moduleHelper)
    {
        $this->moduleHelper = $moduleHelper;
    }

    /**
     * Создать объект миграции из пути до файла миграции
     *
     * @param $path
     * @return Object object
     * @throws MigrationsException
     */
    public function createFromPath($path)
    {
        require_once($path);
        $migrationName = $this->moduleHelper->getMigrationNameFromFilePath($path);
        $moduleName = $this->moduleHelper->getMigrationModuleNameFromFilePath($path);
        return new self($migrationName, $moduleName, $path);
    }

    /**
     * Создать объект миграции из названия миграции и названия модуля в котором лежит миграция
     *
     * @param $migrationName
     * @param $moduleName
     * @return Object object
     */
    public function createFromNameAndModule($migrationName, $moduleName)
    {
        $path = $this->moduleHelper->getMigrationFilePath($migrationName, $moduleName);
        require_once($path);
        return new self($migrationName, $moduleName, $path);
    }
} 