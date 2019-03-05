<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "master_record".
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
    'title' => 'Master Record',
    'description' => 'Provides a TCA-based way to relate records to a "master" record and allows instances of a "master" record to be tracked and kept in sync',
    'category' => 'misc',
    'shy' => 0,
    'version' => '1.0.0',
    'dependencies' => 'typo3',
    'conflicts' => '',
    'priority' => '',
    'loadOrder' => '',
    'module' => '',
    'state' => 'experimental',
    'uploadfolder' => 0,
    'createDirs' => '',
    'modify_tables' => '',
    'clearCacheOnLoad' => 1,
    'lockType' => '',
    'author' => 'Claus Due',
    'author_email' => 'claus@namelesscoder.net',
    'author_company' => '',
    'CGLcompliance' => '',
    'CGLcompliance_note' => '',
    'constraints' => array(
        'depends' => array(
            'typo3' => '4.5-9.5.99',
        ),
        'conflicts' => array(
        ),
        'suggests' => array(
        ),
    ),
    '_md5_values_when_last_written' => 'a:0:{}',
    'suggests' => array(
    ),
);
