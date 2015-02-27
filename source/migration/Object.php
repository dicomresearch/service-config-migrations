<?php

namespace configMigrations\migration;
use configMigrations\MigrationsException;

/**
 * MigrationObject
 * Обертка для файла миграции.
 * Создает удобный интерфейс для работы с миграцией
 */
class Object 
{
    /**
     * инстанс класса миграции
     * @var
     */
    private $migration;

    /**
     * Название миграции - совпадает с названием класса миграции
     * @var
     */
    private $name;

    /**
     * Модуль в котором лежит миграция
     * @var
     */
    private $module;

    /**
     * Время создания миграции
     * @var
     */
    private $creationTime;

    /**
     * Физический путь до миграции
     *
     * @var
     */
    private $path;

    /**
     * @param $name
     * @param $module
     * @param $path
     */
    public function __construct($name, $module, $path)
    {
        $this->migration = new $name();
        $this->name = $name;
        $this->module = $module;
        $this->path = $path;
    }

    /**
     * Накатить миграцию
     *
     * @return mixed
     */
    public function up()
    {
        return $this->migration->up();
    }

    /**
     * Откатить миграцию
     *
     * @return mixed
     */
    public function down()
    {
        return $this->migration->down();
    }

    /**
     * @return string
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Получить время создания миграции
     *
     * Часть названия миграции - есть таймштапм - время создания миграции
     *
     * @return timestamp
     */
    public function getCreationTime()
    {
        if ($this->creationTime) {
            return $this->creationTime;
        }

        $nameArr = explode('_', $this->name);

        if (!isset($nameArr[0])) {
            throw MigrationsException::invalidMigrationName();
        }

        // вырежем букву m из названия
        $result = substr($nameArr[0], 1);

        if (false === $result) {
            throw MigrationsException::invalidMigrationName();
        }

        $this->creationTime = $result;
        return $result;
    }
} 