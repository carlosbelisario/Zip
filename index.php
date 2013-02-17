<?php
require_once __DIR__ . '/src/abimaelrc/Loader/AutoLoading.php';
$loader = new \Loader\Autoloading(__DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'abimaelrc');

echo '<pre>';


/**
 * Example 1
 *     Extract all files
 */
$zip   = new \Zip\Extract('./', 'test.zip', 'zip');
$files = $zip->open()
             ->extractAllFiles();
echo 'Files added: ';
var_dump($files);


/**
 * Example 2
 *     Extract by extension
 *     Add suffix to filename
 */
$zip   = new \Zip\Extract('./', 'test.zip', 'zip', 'tmp');
$files = $zip->open()
             ->setSuffix(date('Y-m-d'))
             ->extractByExtension();
echo 'Files added: ';
var_dump($files);


/**
 * Example 3
 *     Extract specifics files
 *
 * Another way is using: $zip->setFilesToExtract(array('file1.ext', 'file2.ext', '...'));
 */
$zip   = new \Zip\Extract('./', 'test.zip', 'zip', 'tmp');
$files = $zip->open()
             ->sameStructure(false)
             ->overwrite(true)
             ->setFileToExtract('file.ext')
             ->setFileToExtract('dir')
             ->extractSpecificsFiles();
echo 'Files added: ';
var_dump($files);


/**
 * Example 4
 *     Extract specifics files
 *
 * \Zip\Mime::setMimeFiles() change value of \Zip\Mime::$validateMime to true
 *
 * Another way to add files is using: $zip->setFilesToExtract(array('file1.ext', 'file2.ext', '...'));
 */
$mimes = array(
    'ext'  => 'mime',
    'ext2' => array(
        'mime1',
        'mme2',
    ),
);
\Zip\Mime::setMimeFiles($mimes);
$zip   = new \Zip\Extract('./', 'test.zip', 'zip', 'tmp');
$files = $zip->open()
             ->sameStructure(false)
             ->overwrite(true)
             ->setFileToExtract('file.ext')
             ->setFileToExtract('dir')
             ->extractSpecificsFiles();
echo 'Files added: ';
var_dump($files);


/**
 * Example 4
 *     Extract all files
 *     Set up magic.mime
 */
\Zip\Mime::setMagicMime(__DIR__ . DIRECTORY_SEPARATOR . 'magic.mime');

$zip = new \Zip\Extract('./', 'test.zip', 'zip');
$files = $zip->open()
             ->extractAllFiles();
echo 'Files added: ';
var_dump($files);


/**
 * Example 5
 *     Extract all files
 */
$zip   = new \Zip\Extract('./', 'test.zip', 'zip', 'tmp');
$files = $zip->open()
             ->sameStructure(false)
             ->overwrite(true)
             ->setFileToExtract('test')
             ->extractSpecificDirStructure();
echo 'Files added: ';
var_dump($files);


/**
 * Example 6
 *     create zip file
 */
$zip = new \Zip\Create('./');
$zip->create('zipfile')
    ->addFullDir('zip')
    ->addFromString('test.txt', 'work')
    ->addEmptyDir('directory');
echo $zip->getZip()->numFiles;

echo '</pre>';