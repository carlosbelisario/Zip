<?php

namespace Zip;

use ZipArchive;
use SplFileInfo;

class Extract extends Zip
{
    /**
     * array with files to extract
     *
     * @var array
     */
    protected $filesToExtract = array();

    /**
     * Destination directory
     *
     * @var string
     */
    protected $destinationDir;

    /**
     * Destination temporary directory
     *
     * @var string
     */
    protected $tmpDestinationDir;

    /**
     * Set up if search must check everything or be specific
     *    true : everything
     *    false: specific
     *
     * @var boolean
     */
    protected $greedy         = true;

    /**
     * Stay with same structure
     *
     * @var boolean
     */
    protected $sameStructure  = true;

    /**
     * Stay with same file name
     *
     * @var boolean
     */
    protected $sameName       = true;

    /**
     * Setup in name a suffix
     *
     * @var string
     */
    protected $suffix;

    /**
     * @var string
     */
    protected $separator      = '_';

    /**
     * Boolean to remove zip file
     *
     * @var boolean
     */
    protected $removeZipFile  = false;

    /**
     * Boolean to remove temporary directory
     *
     * @var boolean
     */
    protected $removeTmpDir   = true;

    /**
     * @var boolean
     */
    protected $overwrite      = false;

   /**
     * @param string $basePath               Base path of zip
     * @param string $fileName               Name of file
     * @param string|null $destinationDir    Destination to move allowed files or extract all zip file
     * @param string|null $tmpDestinationDir Destination to extract file and check mime
     * @throws \InvalidArgumentException
     */
    public function __construct($basePath = './', $fileName = null, $destinationDir = null, $tmpDestinationDir = null)
    {
        parent::__construct($basePath, $fileName);

        if (empty($tmpDestinationDir) === false) {
            $this->setTmpDestinationDir($this->getBasePath() . DIRECTORY_SEPARATOR . $tmpDestinationDir);
        }

        if (empty($destinationDir) === false) {
            $this->setDestinationDir($this->getBasePath() . DIRECTORY_SEPARATOR . $destinationDir);
        }
    }

    /**
     * Limit files to extracts
     *
     * @param string $file
     * @return \Zip
     */
    public function setFileToExtract($file)
    {
        $this->filesToExtract[] = $file;

        return $this;
    }

    /**
     * Limit files to extracts
     *
     * @param array $file
     * @return \Zip
     */
    public function setFilesToExtract(array $file)
    {
        $this->filesToExtract = $file;

        return $this;
    }

    /**
     * Return files to extract
     *
     * @return array
     */
    public function getFilesToExtract()
    {
        return $this->filesToExtract;
    }

    /**
     * Set destination directory
     *
     * @param string $destinationDir
     * @return \Zip
     */
    public function setDestinationDir($destinationDir)
    {
        if (file_exists($destinationDir) === false) {
            mkdir($destinationDir, $this->getMode(), true);
        }

        $this->destinationDir = realpath($destinationDir);

        return $this;
    }

    /**
     * Get destination directory
     *
     * @return string
     */
    public function getDestinationDir()
    {
        return $this->destinationDir;
    }

    /**
     * Set temporary destination directory
     *
     * @param string $tmpDestinationDir
     * @return \Zip
     */
    public function setTmpDestinationDir($tmpDestinationDir)
    {
        if (file_exists($tmpDestinationDir) === false) {
            mkdir($tmpDestinationDir, $this->getMode(), true);
        }

        $this->tmpDestinationDir = realpath($tmpDestinationDir);

        return $this;
    }

    /**
     * Get temporary destination directory
     *
     * @return string
     */
    public function getTmpDestinationDir()
    {
        return $this->tmpDestinationDir;
    }

    /**
     * @param boolean $bool
     * @return \Zip
     */
    public function greedy($bool)
    {
        $this->greedy = $bool;

        return $this;
    }

    /**
     * @param boolean $bool
     * @return \Zip
     */
    public function sameStructure($bool)
    {
        $this->sameStructure = $bool;

        return $this;
    }

    /**
     * @param boolean $bool
     * @return \Zip
     */
    public function sameName($bool)
    {
        $this->sameName = $bool;

        return $this;
    }

    /**
     * @param string $file
     * @return string
     */
    protected function addSuffix($file)
    {
        $suffix = $this->getSuffix();
        if (empty($suffix) === true) {
            $suffix = $this->getSeparator() . microtime(true);
        }
        $pathInfo = pathinfo($file);

        return $pathInfo['filename'] . $suffix . '.' . $pathInfo['extension'];
    }

    /**
     * If set, it mean that want to add the suffix, so sameName must be set to false
     *
     * @param string      $suffix
     * @param null|string $separator
     * @return \Zip
     */
    public function setSuffix($suffix, $separator = null)
    {
        $separator    = (empty($separator) === true)
                      ? $this->getSeparator()
                      : $separator;
        $this->suffix = $separator . $suffix;

        $this->sameName(false);

        return $this;
    }

    /**
     * @return string
     */
    public function getSuffix()
    {
        return $this->suffix;
    }

    /**
     * @param string $separator
     * @return \Zip
     */
    public function setSeparator($separator)
    {
        $this->separator = $separator;

        return $this;
    }

    /**
     * @return string
     */
    public function getSeparator()
    {
        return $this->separator;
    }

    /**
     * Remove zip file
     *
     * @param boolean $bool
     * @return \Zip
     */
    public function removeZipFile($bool)
    {
        $this->removeZipFile = $bool;

        return $this;
    }

    /**
     * Remove temporary directory
     *
     * @param boolean $bool
     * @return \Zip
     */
    public function removeTmpDir($bool)
    {
        $this->removeTmpDir = $bool;

        return $this;
    }

    /**
     * Overwrite existing file or dir
     *
     * @param boolean $bool
     * @return \Zip
     */
    public function overwrite($bool)
    {
        $this->overwrite = $bool;

        return $this;
    }

    /**
     * Get list from a specific directory
     *
     * @return array
     */
    public function listFiles($path)
    {
        $files = array();

        foreach ($this->iterateDir($path) as $k => $v) {
            $files[] = $v->getRealPath();
        }

        return $files;
    }

    /**
     * Move allowed file to new destination
     *
     * @param array $moveFiles
     * @return array with files moved
     * @throws \InvalidArgumentException
     */
    public function moveValidFiles(array $moveFiles = array())
    {
        $filesMoved        = array();
        $tmpDestinationDir = $this->getTmpDestinationDir();
        $destinationDir    = $this->getDestinationDir();

        if (empty($tmpDestinationDir) === true) {
            throw new \InvalidArgumentException('There is no a temporary destination declared in $tmpDestinationDir');
        }

        if (empty($destinationDir) === true) {
            throw new \InvalidArgumentException('There is no a destination declared in $destinationDir');
        }

        foreach ($moveFiles as $file) {
            $tmpFile = realpath($tmpDestinationDir . DIRECTORY_SEPARATOR . $file);
            $tmpInfo = new SplFileInfo($tmpFile);

            if (empty($tmpFile) === false) {
                $validMime = Mime::isValidMime($tmpFile);

                if ($validMime === true) {
                    $destination = $destinationDir . DIRECTORY_SEPARATOR;
                    $baseName    = basename($file);

                    if ($this->sameStructure === true) {
                        $destination .= dirname($file) . DIRECTORY_SEPARATOR;

                        if (file_exists($destination) === false) {
                            mkdir($destination, $this->getMode(), true);
                        }
                    }

                    $destination    .= ($this->sameName === false)
                                     ? $this->addSuffix($baseName)
                                     : $baseName;
                    $destinationInfo = new SplFileInfo($destination);

                    /**
                     * If is a file use copy elseif is a dir use mkdir
                     */
                    if (
                        $tmpInfo->isFile() === true
                        && (
                            file_exists($destination) === false
                            || (
                                file_exists($destination) === true
                                && $this->overwrite === true
                                && $destinationInfo->isWritable() === true
                            )
                        ) && copy($tmpFile, $destination) === true
                    ) {
                        $filesMoved[] = realpath($destination);
                    } elseif (
                        (
                            $tmpInfo->isDir() === true
                            && file_exists($destination) === false
                            && mkdir($destination, $this->getMode(), true) === true
                        ) || (
                            $tmpInfo->isDir() === true
                            && file_exists($destination) === true
                            && $this->overwrite === true
                        )
                    ){
                        $filesMoved[] = realpath($destination);
                    }
                }
            }
        }

        return $filesMoved;
    }

    /**
     * @param array $files
     */
    public function extractFiles(array $files)
    {
        $tmpDestinationDir = $this->getTmpDestinationDir();

        if (empty($tmpDestinationDir) === true) {
            throw new \InvalidArgumentException('There is no a temporary destination declared in $tmpDestinationDir');
        }

        $this->zip->extractTo($tmpDestinationDir, $files);

        return $this->moveValidFiles($files);
    }

    /**
     * Extract allowed files to temporary directory
     *
     * @return array
     */
    public function extractByExtension()
    {
        for ($i = 0; $i < $this->zip->numFiles; $i++) {
            $file     = $this->zip->statIndex($i);
            $pathInfo = pathinfo($file['name']);

            if (array_key_exists('extension', $pathInfo) === true) {
                $mimeFiles = Mime::getMimeFiles();

                /**
                 * Include by specific extension or all if empty Mime::mimeFiles
                 */
                if (array_key_exists($pathInfo['extension'], $mimeFiles) === true) {
                    $this->setFileToExtract($file['name']);
                }
            }
        }

        return $this->extractFiles($this->getFilesToExtract());
    }

    /**
     * Extract specifics files
     *
     * @return array
     */
    public function extractSpecificsFiles()
    {
        $files = array();
        for ($i = 0; $i < $this->zip->numFiles; $i++) {
            $fileInfo = $this->zip->statIndex($i);

            /**
             * Check first if user write full path
             * or check if user only write the name of the file
             * and want to check everything
             */
            if (
                in_array($fileInfo['name'], $this->getFilesToExtract()) === true
                || ($this->greedy === true && in_array(basename($fileInfo['name']), $this->getFilesToExtract()) === true)
            ){
                $files[] = $fileInfo['name'];
            }
        }

        return $this->extractFiles($files);
    }

    /**
     * @return array
     */
    public function extractSpecificDirStructure()
    {
        $files = array();
        foreach ($this->getFilesToExtract() as $k => $v) {
            $v = ltrim(preg_replace('~(\\+)|(/+)~', '/', ($v . '/')), '/');
            $i = $this->zip->locateName($v);
            for ($i; $i < $this->zip->numFiles; $i++) {
                $file = $this->zip->getNameIndex($i);
                if (preg_match('~^' . preg_quote($v, '~') . '~', $file) === 1) {
                    $files[] = $file;
                } else {
                    break;
                }
            }
        }
        var_dump($files);

        return $this->extractFiles($files);
    }

    /**
     * Extract all files
     *
     * @return array
     * @throws \InvalidArgumentException
     */
    public function extractAllFiles()
    {
        $files          = array();
        $destinationDir = $this->getDestinationDir();

        if (empty($destinationDir) === true) {
            throw new \InvalidArgumentException('There is no a destination declared in $destinationDir');
        }

        for ($i = 0; $i < $this->zip->numFiles; $i++) {
            $files[] = $this->zip->getNameIndex($i);
        }

        $this->zip->extractTo($destinationDir);

        return $files;
    }

    /**
     * Close object \ZipArchive()
     */
    public function __destruct()
    {
        $tmpDestinationDir = $this->getTmpDestinationDir();
        if ($this->removeTmpDir === true && empty($tmpDestinationDir) === false) {
            $this->removeFiles($tmpDestinationDir);
        }

        if ($this->removeZipFile === true) {
            unlink($this->zipFileName);
        }
    }
}