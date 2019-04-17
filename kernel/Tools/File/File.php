<?php
namespace Kernel\Tools\File;

use Kernel\Tools\Collection\FileException;

class File
{
    private $fileName;
    private $fileSize;
    private $fileTmp;
    private $fileDim;
    private $fileError;
    private $fileExtension;
    private $extensions;
    private $size;
    private $dim;
    private $dir;

    /**
     * File constructor.
     * @param $file
     * @param $size
     * @param $extensions
     * @param $dim
     * @param $dir
     */
    public function __construct($file, $size, $extensions, $dim, $dir)
    {
        $this->fileExtension = '.' . strtolower(substr(strrchr($file['name'], '.'),1));
        $this->fileName      = $this->getFileName($file['name']);
        $this->fileSize      = $file['size'];
        $this->fileTmp       = $file['tmp_name'];
        $this->fileDim       = getimagesize($file['tmp_name']);
        $this->fileError     = $file['error'];
        $this->extensions    = $extensions;
        $this->size          = $size;
        $this->dim           = $dim;
        $this->dir           = $dir;
    }

    /**
     * Get the name of the file by replacing the specials chars
     * @param $file
     * @return mixed|string
     */
    private function getFileName($file)
    {
        $fileName = strtr($file,
            'ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ',
            'AAAAAACEEEEIIIIOOOOOUUUUYaaaaaaceeeeiiiioooooouuuuyy');
        $fileName = preg_replace('/[^.a-z0-9]+/i', '-', $fileName);
        $fileName = explode('.',$fileName)[0];

        return hash('sha1', microtime($fileName)) . $this->fileExtension;
    }

    /**
     * Check if there are a error
     * @throws FileException
     */
    private function check()
    {
        if ($this->fileError > 0) throw new FileException("Error during upload", FileException::FILE_ERROR);

        if (!in_array($this->fileExtension, $this->extensions)) throw new FileException("The extension is not allowed", FileException::EXTENSION_NOT_ALLOWED);

        if ($this->fileSize > $this->size) throw new FileException("The limit of size is reached", FileException::LIMIT_OF_SIZE_REACHED);

        if ($this->fileDim[0] > $this->dim[0] || $this->fileDim[1] > $this->dim[1]) throw new FileException("The dimension is not allowed", FileException::DIMENSION_NOT_ALLOWED);
    }

    /**
     * Upload the file if there are no error
     * @return array|string
     * @throws FileException
     */
    public function upload()
    {
        try {
            $this->check();
        } catch (FileException $e) {
            throw $e;
        }

        move_uploaded_file($this->fileTmp, $_SERVER['DOCUMENT_ROOT'] . '/' . $this->dir . $this->fileName);
        chmod($_SERVER['DOCUMENT_ROOT'] . '/' . $this->dir . $this->fileName, 0755);

        return $this->fileName;
    }
}
