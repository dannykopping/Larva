<?php

    use Larva\Bootstrap;

    require_once "vendor/autoload.php";

    $outDir    = __DIR__ . '/output';
    $filename  = 'db/schema.mwb';
    $options = array(
        'extends' => 'Model',
        'namespace' => 'Test\\Model',
    );

    Bootstrap::generate($filename, $options, $outDir);