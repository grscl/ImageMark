<?php
ini_set('display_errors', '1');
ini_set('error_reporting', E_ALL);

require(__DIR__.'/ImageMark.php');

$path1 = __DIR__.'/mario.png';
$path2 = __DIR__.'/ubuntu.jpg';

$Image = new ImageMark($path1, $path2, 'http://dot.com');

echo $Image->render();
