<?php

namespace configMigrations;
use configMigrations\interfaces\IConfiger;
use configMigrations\interfaces\IModuleHelper;

/**
 * MigrationFileBuilder 
 */
class MigrationFileBuilder 
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
     * @param $configer
     * @param $moduleHelper
     */
    function __construct($configer, $moduleHelper)
    {
        $this->configer = $configer;
        $this->moduleHelper = $moduleHelper;
    }

    /**
     * @param $migrationName
     * @param $moduleName
     * @throws MigrationsException
     */
    public function build($migrationName, $moduleName)
    {
        $this->checkMigrationNameIsCorrect($migrationName);
        $this->checkModuleExists($moduleName);
        $this->checkMigrationFolder($moduleName);

        $name = 'm' . time() . '_' . $migrationName;
        $content = strtr($this->getMigrationFileTemplate(), array('{ClassName}' => $name));
        $file = $this->moduleHelper->getMigrationFilePath($name, $moduleName);

        file_put_contents($file, $content);
    }

    /**
     * Проверить что модуль существует
     *
     * @param $moduleName
     * @throws MigrationsException
     */
    private function checkModuleExists($moduleName)
    {
        if (!$this->moduleHelper->isModuleExists($moduleName)) {
            throw MigrationsException::moduleNotExists($moduleName);
        }
    }

    /**
     * Проверить имя миграции
     *
     * @param $name
     * @throws MigrationsException
     */
    private function checkMigrationNameIsCorrect($name)
    {
        if(!preg_match('/^\w+$/',$name)) {
            throw MigrationsException::migrationNameNotValid();
        }
    }

    /**
     * Проверить что папка для миграций существует, и что она доступна для записи. иначе создать
     *
     * @param $moduleName
     * @throws MigrationsException
     */
    private function checkMigrationFolder($moduleName)
    {
        if (!is_dir($this->moduleHelper->getMigrationFolderPath($moduleName))) {
            if (!mkdir($this->moduleHelper->getMigrationFolderPath($moduleName), 0755)) {
                throw MigrationsException::cantCreateMigrationConfigFolder();
            }
        }

        if (!is_writable($this->moduleHelper->getMigrationFolderPath($moduleName))) {
            throw MigrationsException::cantWriteToMigrationConfigFolder();
        }
    }

    /**
     * Шаблон для создания нового файла миграции
     * TODO: вынести
     *
     * @return string
     */
    private function getMigrationFileTemplate()
    {
        return <<<EOD
<?php

class {ClassName}
{
    public function up() {}

	public function down() {}
}
EOD;
    }

} 