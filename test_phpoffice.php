<?php

require 'vendor/autoload.php';

$class = '\\PhpOffice\\PhpSpreadsheet\\IOFactory';
echo "Checking class: $class\n";
echo 'Class exists: '.(class_exists($class) ? 'YES' : 'NO')."\n";

if (class_exists($class)) {
    echo "IOFactory loaded successfully\n";
} else {
    echo "IOFactory NOT found\n";
    echo "Checking PhpOffice directory...\n";
    if (is_dir('vendor/phpoffice/phpspreadsheet')) {
        echo "Directory exists\n";
        echo "Files:\n";
        system('ls -la vendor/phpoffice/phpspreadsheet/src/');
    }
}
