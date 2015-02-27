<?php

namespace dicom\configMigrations\interfaces;

/**
 * IModuleHelper
 * TODO: стоит ли повысить уровень абстракции и сделать вместо модульхелпера pathHelper чтобы можно было использовать
 * TODO: миграции конфигов в тех системах где миграции лежат не по модулям а в одном месте например?
 */
interface IModuleHelper
{
    public function isModuleExists($moduleName);
    public function getModules();
    public function getModulePath($moduleName);
    public function getMigrationFolderPath($moduleName);
    public function getMigrationFilePath($migrationName, $moduleName);
    public function getMigrationNameFromFilePath($path);
    public function getMigrationModuleNameFromFilePath($path);
} 