<?php

namespace configMigrations\interfaces;

/**
 * IProvider 
 */
interface IProvider
{
    public static function create(array $config);

    /**
     * @param string $section
     * @return mixed
     */
    public function get($section, array $criteria);

    /**
     * @param string $section
     * @param array $criteria
     * @return mixed
     */
    public function find($section, array $criteria);

    /**
     * @param $section
     * @param array $data
     * @return mixed
     */
    public function modify($section, array $data);

    /**
     * return a configuration as key => value
     *
     * @return array
     */
    public function getConfig();
} 