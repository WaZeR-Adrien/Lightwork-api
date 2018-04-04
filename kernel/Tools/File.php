<?php
namespace Kernel\Tools;

class File
{
    private $_fileName;
    private $_fileSize;
    private $_fileTmp;
    private $_fileDim;
    private $_fileError;
    private $_fileExtension;
    private $_extensions;
    private $_size;
    private $_dim;
    private $_dir;
    public  $fileNameHash;
    private $_error = [];

    public function __construct($file, $size, $extensions, $dim, $dir)
    {
        $this->_fileName      = $this->getFileName($file['name']);
        $this->_fileSize      = $file['size'];
        $this->_fileTmp       = $file['tmp_name'];
        $this->_fileDim       = getimagesize($file['tmp_name']);
        $this->_fileError     = $file['error'];
        $this->_fileExtension = '.' . strtolower(  substr(  strrchr($file['name'], '.')  ,1)  );
        $this->_extensions    = $extensions;
        $this->_size          = $size;
        $this->_dim           = $dim;
        $this->_dir           = $dir;
    }

    public function getFileName($file)
    {
        $fileName = strtr($file,
            'ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ',
            'AAAAAACEEEEIIIIOOOOOUUUUYaaaaaaceeeeiiiioooooouuuuyy');
        $fileName = preg_replace('/[^.a-z0-9]+/i', '-', $fileName);
        $fileName = explode('.',$fileName)[0];

        return $fileName;
    }

    public function check()
    {
        if ($this->_fileError > 0) $this->_error['transfert'] = true;
        if (!in_array($this->_fileExtension, $this->_extensions)) $this->_error['extension'] = true;
        if ($this->_fileSize > $this->_size) $this->_error['size'] = true;
        if ($this->_fileDim[0] > $this->_dim[0] || $this->_fileDim[1] > $this->_dim[1]) $this->_error['dim'] = true;
    }

    public function upload()
    {
        if (empty($this->_error))
        {
            $this->fileNameHash = hash('sha1', microtime($this->_fileName)) . $this->_fileExtension;
            move_uploaded_file($this->_fileTmp, $_SERVER['DOCUMENT_ROOT'] . '/' . $this->_dir . $this->fileNameHash);
            chmod($_SERVER['DOCUMENT_ROOT'] . '/' . $this->_dir . $this->fileNameHash, 0755);

            return $this->fileNameHash;
        }
        else { return ['error' => $this->_error]; }
    }
}
