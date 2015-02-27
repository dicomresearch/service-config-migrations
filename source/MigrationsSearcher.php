<?php

namespace configMigrations\searchers;
use configMigrations\interfaces\IModuleHelper;
use configMigrations\migration\ObjectFactory;
use configMigrations\MigrationsException;
use configMigrations\ServiceConfigFacade;

/**
 * MigrationsSearcher
 *
 * Серчер миграций
 * Общий компонент для работы с поиском миграций. Работает с более низкоуровневыми серчерами
 */
class MigrationsSearcher 
{
    /**
     * @var IModuleHelper
     */
    private $moduleHelper;

    /**
     * @var ServiceConfigFacade
     */
    private $serviceConfigFacade;

    /**
     * @var ObjectFactory
     */
    private $migrationObjectFactory;

    /**
     * @param IModuleHelper $moduleHelper
     * @param ServiceConfigFacade $serviceConfigFacade
     * @param ObjectFactory $migrationObjectFactory
     */
    function __construct(IModuleHelper $moduleHelper, ServiceConfigFacade $serviceConfigFacade, ObjectFactory $migrationObjectFactory)
    {
        $this->moduleHelper = $moduleHelper;
        $this->serviceConfigFacade = $serviceConfigFacade;
        $this->migrationObjectFactory = $migrationObjectFactory;
    }

    /**
     * Найти непримененные миграции
     * Возвратить массив из объектов миграций
     *
     * @param $amount
     * @return array
     */
    public function searchNewMigrations($amount)
    {
        $allMigrationsPaths = $this->searchInFileSystem();
        $allMigrations = [];
        foreach ($allMigrationsPaths as $path) {
            $migrationName = $this->moduleHelper->getMigrationNameFromFilePath($path);
            $allMigrations[$migrationName] = $this->migrationObjectFactory->createFromPath($path);
        }

        // получить миграции из бд
        $appliedMigrations = $this->searchAppliedMigrations();

        if (count($allMigrations) < count($appliedMigrations)) {
            throw new MigrationsException("All migrations amount cant be smaller than applied migrations amount!
            May be you need to delete old unused migrations from Migrations db");
        }

        // получить разницу между всеми и примененными -> новые миграции.
        $newMigrations = array_diff_key($allMigrations, $appliedMigrations);

        // отсортировать миграции по времени создания (первыми идут самые старые)
        usort($newMigrations, function($a, $b) {
            return $a->getCreationTime() > $b->getCreationTime();
        });

        // взять $amount миграций начиная с самых старых
        if (!is_null($amount) && $amount>0) {
            $newMigrations = array_slice($newMigrations, 0, $amount);
        }

        return $newMigrations;
    }

    /**
     * Вернуть все миграции конфигов которые есть в проекте, начиная с самых старых
     *
     * @return array
     */
    private function searchInFileSystem()
    {
        $allFileSystemMigrations = [];

        // очищаем кэш функции по работе с файловой системой
        clearstatcache();

        $moduleNames = $this->moduleHelper->getModules();
        foreach($moduleNames as $moduleName) {
            // путь до папки с миграциями модуля
            $path = $this->moduleHelper->getMigrationFolderPath($moduleName);
            // получить php файлы в директории если они есть
            $migrationsList = glob($path . DIRECTORY_SEPARATOR . '*.php');
            $allFileSystemMigrations = array_merge($allFileSystemMigrations, $migrationsList);
        }

        return $allFileSystemMigrations;
    }

    /**
     * Найти примененные миграции
     * Возвратить массив из объектов миграций
     *
     * @param $amount
     * @return array
     */
    public function searchAppliedMigrations($amount = null)
    {
        $migrationsObjects = [];
        $migrations = $this->searchInDb();

        if (!is_null($amount) && $amount>0) {
            $migrations = array_slice($migrations, 0, $amount);
        }

        foreach($migrations as $migrationData) {
            $migrationsObjects[$migrationData['name']] =
                $this->migrationObjectFactory->createFromNameAndModule($migrationData['name'], $migrationData['module']);
        }

        return $migrationsObjects;
    }

    /**
     * искать записи о примененных миграциях на сервисе конфигов
     * Первыми вернет самые последние примененные миграции
     *
     * @return array
     */
    private function searchInDb()
    {
        $result = array_reverse($this->serviceConfigFacade->listMigrations());
        return $result;
    }
}