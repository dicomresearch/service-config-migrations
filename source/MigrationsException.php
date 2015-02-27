<?php

namespace configMigrations;

/**
 * MigrationsException 
 */
class MigrationsException extends \CException
{
    public static function migrationHistoryTableNotExist()
    {
        return new static("Migrations history table not exists");
    }

    public static function migrationNameNotValid()
    {
        return new static("The name of the migration must contain letters, digits and/or underscore characters only");
    }

    public static function cantCreateMigrationConfigFolder()
    {
        return new static("Cant create folder for migration config");
    }

    public static function cantWriteToMigrationConfigFolder()
    {
        return new static("Cant write to config migrations folder");
    }

    public static function moduleNotExists($m)
    {
        return new static("Module: '" . $m . "' dont exists!");
    }

    public static function pathParseException($path)
    {
        return new static("Cant parse given path. Expected string, found: " . var_dump($path));
    }

    public static function configIsNotSet($section)
    {
        return new static("Cant find  '" . $section . "' in config");
    }

    public static function invalidMigrationPath()
    {
        return new static("Cant parse migration path. Invalid path given.");
    }

    public static function invalidMigrationName()
    {
        return new static("Cant parse migration name. Invalid migration name!");
    }
}