<?php

namespace dicom\configMigrations;
use dicom\configMigrations\interfaces\IConfiger;
use dicom\configMigrations\interfaces\IModuleHelper;
use dicom\configMigrations\interfaces\IProvider;
use dicom\configMigrations\migration\ObjectFactory;

/**
 * ConfigMigrationsEngine
 *
 * Точка входа в библиотеку
 */
class MigrationsEngine
{
    /**
     * @var IConfiger
     */
    private $configer;

    /**
     * @var IModuleHelper
     */
    private $moduleHelper;

    /**
     * Откуда брать сведения о миграциях и куда их записывать
     *
     * @var IProvider
     */
    private $provider;

    /**
     * @var ServiceConfigFacade
     */
    private $serviceConfigFacade;

    /**
     * @var MigrationsSearcher
     */
    private $migrationsSearcher;

    /**
     * @var ObjectFactory
     */
    private $migrationObjectFactory;

    /**
     * constructor обязательно принимает конфигер и помощник для получения модулей
     *
     * @param IConfiger $configer
     * @param IModuleHelper $moduleHelper
     */
    function __construct(IConfiger $configer, IProvider $provider, IModuleHelper $moduleHelper)
    {
        $this->configer = $configer;
        $this->provider = $provider;
        $this->moduleHelper = $moduleHelper;
        $this->serviceConfigFacade = new ServiceConfigFacade($this->provider, $this->configer->getRefbook());
        $this->migrationObjectFactory = new ObjectFactory($moduleHelper);
        $this->migrationsSearcher = new MigrationsSearcher(
            $moduleHelper,
            $this->serviceConfigFacade,
            $this->migrationObjectFactory
        );
    }

    /**
     * @param $amount
     * @throws MigrationsException
     */
    public function migrateUp($amount)
    {
        // найти $amount миграций через сеарчер
        $migrations = $this->migrationsSearcher->searchNewMigrations($amount);
        foreach ($migrations as $migration) {
            $migration->up();

            // пишем в базу миграций что миграция успешно накатилась
            $this->serviceConfigFacade->addMigration($migration);

            echo 'Migration "' . $migration->getName() . '" successfuly applied' . PHP_EOL;
        }
    }

    /**
     * Получить новые миграции
     *
     * @param $amount
     * @return array
     * @throws MigrationsException
     */
    public function getNewMigrations($amount)
    {
        return $this->migrationsSearcher->searchNewMigrations($amount);
    }

    /**
     * Откатить миграции
     *
     * @param $amount
     */
    public function migrateDown($amount)
    {
        // найти $amount миграций через сеарчер
        $migrations = $this->migrationsSearcher->searchAppliedMigrations($amount);
        foreach ($migrations as $migration) {
            $migration->down();

            // удаляем из базы миграций миграцию  которая успешно откатилась
            $this->serviceConfigFacade->deleteMigration($migration);

            echo 'Migration "' . $migration->getName() . '" successfuly reverted' . PHP_EOL;
        }
    }

    /**
     * Получить примененные миграции
     *
     * @param $amount
     * @return array
     */
    public function getAppliedMigrations($amount)
    {
        return $this->migrationsSearcher->searchAppliedMigrations($amount);
    }

    /**
     * Создать файл миграции
     *
     * @param $migrationName
     * @param $moduleName
     * @throws MigrationsException
     */
    public function createMigration($migrationName, $moduleName)
    {
        $migrationFileBuilder = new MigrationFileBuilder($this->configer, $this->moduleHelper);
        $migrationFileBuilder->build($migrationName, $moduleName);
    }
}