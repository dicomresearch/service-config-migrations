<?php

namespace dicom\configMigrations\interfaces;

/**
 * IConfiger 
 */
interface IConfiger
{
    public function getRefbook();
    public function getEndpoint();
    public function getRefbooksDataWsdl();
    public function getRefbooksStructureWsdl();
} 