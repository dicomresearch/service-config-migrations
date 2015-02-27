<?php

namespace configMigrations;
use configMigrations\interfaces\IConfiger;
use configMigrations\interfaces\IModuleHelper;
use configMigrations\interfaces\IProvider;
use configMigrations\migration\ObjectFactory;
use configMigrations\searchers\MigrationsSearcher;

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
        $this->checkMigrationsListIsEmpty($migrations);

        $promptMessage = 'Are you shure want to apply ' . count($migrations) . ' migrations?' . PHP_EOL;
        foreach ($migrations as $migration) {
            $promptMessage .= '* ' . $migration->getName() . PHP_EOL;
        }
        $this->promptToHandleMigrations($promptMessage);

        foreach ($migrations as $migration) {
            $migration->up();

            // пишем в базу миграций что миграция успешно накатилась
            $this->serviceConfigFacade->addMigration($migration);

            echo 'Migration "' . $migration->getName() . '" successfuly applied' . PHP_EOL;
        }

        echo PHP_EOL . 'Total migrations successfuly applied: ' . count($migrations) . PHP_EOL;
    }

    /**
     * @param $amount
     */
    public function migrateDown($amount)
    {
        // найти $amount миграций через сеарчер
        $migrations = $this->migrationsSearcher->searchAppliedMigrations($amount);
        $this->checkMigrationsListIsEmpty($migrations);

        $promptMessage = 'Are you shure want to revert ' . count($migrations) . ' migrations?' . PHP_EOL;
        foreach ($migrations as $migration) {
            $promptMessage .= '* ' . $migration->getName() . PHP_EOL;
        }
        $this->promptToHandleMigrations($promptMessage);

        foreach ($migrations as $migration) {
            $migration->down();

            // удаляем из базы миграций миграцию  которая успешно откатилась
            $this->serviceConfigFacade->deleteMigration($migration);

            echo 'Migration "' . $migration->getName() . '" successfuly reverted' . PHP_EOL;
        }

        echo PHP_EOL . 'Total migrations successfuly reverted: ' . count($migrations) . PHP_EOL;
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
        $this->promptToHandleMigrations(
            'Are you shure want to create migration \'' . $migrationName . '\' in module \'' . $moduleName . '\'?' . PHP_EOL);
        $migrationFileBuilder = new MigrationFileBuilder($this->configer, $this->moduleHelper);
        $migrationFileBuilder->build($migrationName, $moduleName);
        echo "New migration created successfully" . PHP_EOL;
    }

    /**
     * Проверка что список миграций не пустой
     *
     * @param $migrationsList
     */
    private function checkMigrationsListIsEmpty($migrationsList)
    {
        if (empty($migrationsList)) {
            echo 'There are no applied migrations' . PHP_EOL;
            exit;
        }
    }

    /**
     * Спрашиваем пользователя хочет ли он совершить действие над миграциями
     *
     * @param $promptMessage
     */
    private function promptToHandleMigrations($promptMessage)
    {
        echo PHP_EOL . $promptMessage;
        echo 'Type \'y\' to continue:' . PHP_EOL;

        $handle = fopen ('php://stdin', 'r');
        $line = fgets($handle);

        if (trim($line) === 'y') {
            echo 'Continuing...' . PHP_EOL;
        } else {
            echo 'Aborting...' . PHP_EOL;
            exit;
        }
    }
} 