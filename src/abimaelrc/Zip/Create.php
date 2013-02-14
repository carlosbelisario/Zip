<?php

namespace Zip;

use ZipArchive;
use SplFileInfo;

class Create extends Zip
{
    /**
     * @param null|string $fileName
     * @return \Zip
     * @throws \RuntimeException
     */
    public function create($fileName = null)
    {
        $fileName = (empty($fileName) === true)
                  ? $this->getZipFileName()
                  : $fileName;
        if (empty($fileName) === true) {
            throw new \RuntimeException('$fileName cannot be empty');
        }

        $fileName = pathinfo($fileName, PATHINFO_FILENAME) . '.zip';
        $open = $this->open($fileName, ZIPARCHIVE::CREATE);

        return $this;
    }

    /**
     * @param string $dir
     * @return \Zip
     */
    public function addFullDir($dir)
    {
        $dir  = realpath($dir);
        if (empty($dir) === true) {
            throw new \RuntimeException('Directory do not exists');
        }

        $info = new SplFileInfo($dir);
        if ($info->isDir() === false) {
            throw new \RuntimeException('$dir must be a directory');
        }

        $dirName = pathinfo($dir, PATHINFO_FILENAME);
        $files   = $this->iterateDir($dir);
        foreach ($files as $k => $v) {
            $file = ($dirName . str_replace($dir, '', $v->getRealPath()));
            if ($v->isFile() === true) { 
                $this->zip->addFile($v->getRealPath(), $file);
            } elseif ($v->isDir() === true) {
                $this->zip->addEmptyDir($file);
            }
        }

        return $this;
    }
}