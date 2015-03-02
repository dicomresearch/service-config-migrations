<?php

namespace dicom\configMigrations;

/**
 * ServiceConfigFacade
 * Предоставляет интерфейс для удобной работы с провайдером сервиса конфигов
 */
class ServiceConfigFacade 
{
    /**
     * Справочник с которым работаем
     *
     * @var string
     */
    protected $refbook;

    /**
     * Справочник с которым работаем
     *
     * @var interfaces\IProvider
     */
    protected $provider;

    /**
     * constructor
     */
    public function __construct($provider, $refbook)
    {
        $this->provider = $provider;
        $this->refbook = $refbook;
    }

    /**
     * Создать запись о миграции на сервисе конфигов
     *
     * @param $migrationObject MigrationObject
     * @return array
     */
    public function addMigration($migrationObject)
    {
        $data = [
            'name' => $migrationObject->getName(),
            'module' => $migrationObject->getModule()
        ];

        return $this->provider->modify($this->refbook, [[
            'action' => 'add',
            'entityName' => $this->refbook,
            'data' => [ $data ]
        ]]);
    }

    /**
     * Удалить запись о миграции из сервиса конфигов
     *
     * @param $migrationObject MigrationObject
     * @return array
     */
    public function deleteMigration($migrationObject)
    {
        $migrationData = $this->getMigration($migrationObject);

        $data = [[
            'action' => 'remove',
            'entityName' => $this->refbook,
            'data' => [
                [
                    '_id' => $migrationData['_id'],
                ]
            ]
        ]];

        return $this->provider->modify($this->refbook, $data);
    }

    /**
     * Получить список записей о миграциях c сервиса конфигов
     *
     * @return array
     */
    public function listMigrations()
    {
        return $this->provider->find($this->refbook, [], [], 0, null, null);
    }

    /**
     * Получить запись о миграции с сервиса конфигов
     *
     * @param $migrationObject MigrationObject
     * @return array
     */
    public function getMigration($migrationObject)
    {
        return $this->provider->get($this->refbook, [
            'name' => $migrationObject->getName(),
            'module' => $migrationObject->getModule()
        ]);
    }
} 