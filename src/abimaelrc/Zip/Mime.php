<?php

namespace Zip;

use SplFileInfo;

class Mime
{
    /**
     * Path to magic.mime file
     *
     * @var null|string
     */
    protected static $magicMime = null;

    /**
     * array with default accepted mime files
     *
     * Example:
     *     Specify a single mime extension
     *     \Zip\Mime::setMimeFile('jpg', 'image/jpeg');
     *     \Zip\Mime::setMimeFile('gif', 'image/gif');
     *     
     *     Specify multiple mimes to an extension
     *     \Zip\Mime::setMimeFile('txt', array('text/plain', 'application/x-empty'));
     *
     *     Setup multiple mimes
     *     $mimes = array(
     *         'jpg' => 'image/jpeg',
     *         'txt' => array(
     *             'text/plain',
     *             'application/x-empty',
     *         ),
     *     );
     *     \Zip\Mime::setMimeFiles($mimes);
     *
     * @var array
     */
    protected static $mimeFiles = array();

    /**
     * @var boolean
     */
    protected static $validateMime = false;

    /**
     * @param string $fileName
     * @return boolean
     */
    public static function isValidMime($fileName)
    {
        if (self::$validateMime === false) {
            return true;
        }

        $isValid   = false;
        $info      = new SplFileInfo($fileName);
        $mimeFiles = self::getMimeFiles();
        $finfo     = new \finfo(FILEINFO_MIME, self::getMagicMime());
        $e         = explode(';', $finfo->file($fileName));
        if (empty($e[0]) === true) {
            return false;
        }

        if (array_key_exists($info->getExtension(), $mimeFiles) === true && is_array($mimeFiles[$info->getExtension()]) === true) {
            $isValid = in_array($e[0], $mimeFiles[$info->getExtension()]);
        } elseif (array_key_exists($info->getExtension(), $mimeFiles) === true) {
            $isValid = ($e[0] == $mimeFiles[$info->getExtension()]);
        }

        return $isValid;
    }

    /**
     * Set allowed mime and setup self::$validateMime to true
     *
     * @param string $extension Extension value
     * @param string|array $mime Mime value
     * @see \Zip\Mime::$mimeFiles For examples
     * @throws \InvalidArgumentException
     */
    public static function setMimeFile($extension, $mime)
    {
        if (empty($extension) === true) {
            throw new \InvalidArgumentException('$extension cannot be empty');
        }

        if (empty($mime) === true) {
            throw new \InvalidArgumentException('$mime cannot be empty');
        }

        self::$mimeFiles[$extension] = $mime;

        self::validateMime(true);
    }

    /**
     * Set allowed mime and setup self::$validateMime to true
     *
     * @param array $mimes
     * @see \Zip\Mime::$mimeFiles For examples
     * @throws \InvalidArgumentException
     */
    public static function setMimeFiles(array $mimes)
    {
        if (empty($mimes) === true) {
            throw new \InvalidArgumentException('$mimes cannot be empty');
        }

        foreach ($mimes as $extension => $mime) {
            self::$mimeFiles[$extension] = $mime;
        }

        self::validateMime(true);
    }

    /**
     * @param boolean $bool
     */
    public static function validateMime($bool)
    {
        self::$validateMime = $bool;
    }

    /**
     * Get all allowed mimes
     *
     * @param boolean $flatArray If true, flat array to have all values together
     *     Some extension can have differents mimes for example:
     *         txt: text/plain | application/x-empty
     * @return array
     */
    public static function getMimeFiles($flatArray = false)
    {
        $localMimeFiles = array();

        if ($flatArray === true) {
            array_walk_recursive(self::$mimeFiles, function ($mime, $extension) use (&$localMimeFiles) {
                if (in_array($mime, $localMimeFiles) === false) {
                    $localMimeFiles[] = $mime;
                }
            });
        } else {
            $localMimeFiles = self::$mimeFiles;
        }

        return $localMimeFiles;
    }

    /**
     * Set path of magic.mime
     *
     * @param string|null $path Path to magic.mime file
     * @throws \RuntimeException
     */
    public static function setMagicMime($path = null)
    {
        $path = realpath($path);

        if (empty($path) === true) {
            throw new \RuntimeException('Path is not correct for magic.mime file');
        }

        self::$magicMime = $path;
    }

    /**
     * Get path of magic.mime file
     *
     * @return string
     */
    public static function getMagicMime()
    {
        return self::$magicMime;
    }

    /**
     * Unset specific mime by key (extension name) in self::$mimeFiles
     *
     * @param string $keys
     */
    public static function unsetMimeByKey(array $keys)
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, self::$mimeFiles) === true) {
                unset(self::$mimeFiles[$key]);
            }
        }
    }

    /**
     * Unset specific mime by value in self::$mimeFiles
     *
     * @param array $searchValues
     */
    public static function unsetMimeByValue(array $searchValues)
    {
        foreach ($searchValues as $searchValue) {
            foreach (self::$mimeFiles as $key => $value) {
                if (is_array($value) === true) {
                    foreach ($value as $k => $v) {
                        if ($v == $searchValue) {
                            unset(self::$mimeFiles[$key][$k]);
                        }
                    }

                    if (empty(self::$mimeFiles[$key]) === true) {
                        unset(self::$mimeFiles[$key]);
                    }
                }

                if ($value == $searchValue) {
                    unset(self::$mimeFiles[$key]);
                }
            }
        }
    }

    /**
     * Unset all values in self::$mimeFiles
     */
    public static function unsetAllMime()
    {
        self::$mimeFiles = array();
    }
}