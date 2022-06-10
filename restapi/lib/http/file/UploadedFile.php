<?php

namespace lib\http\file;

class UploadedFile extends \SplFileInfo
{
    private $originalName;
    private $mimeType;
    private $error;

    public function __construct($path, $originalName, $mimeType = null, $error = null )
    {
        parent::__construct($path);

        $this->originalName = $this->getName($originalName);
        $this->mimeType = $mimeType ?: 'application/octet-stream';
        $this->error = $error ?: \UPLOAD_ERR_OK;

    }

    public function getClientOriginalName()
    {
        return $this->originalName;
    }

    public function getClientOriginalExtension()
    {
        return pathinfo($this->originalName, \PATHINFO_EXTENSION);
    }

    public function getClientMimeType()
    {
        return $this->mimeType;
    }

    public function getError()
    {
        return $this->error;
    }

    public function isValid()
    {
        $isOk = \UPLOAD_ERR_OK === $this->error;

        return $isOk && is_uploaded_file($this->getPathname());
    }

    public function getErrorMessage()
    {
        static $errors = [
            \UPLOAD_ERR_FORM_SIZE => 'The file "%s" exceeds the upload limit defined in your form.',
            \UPLOAD_ERR_PARTIAL => 'The file "%s" was only partially uploaded.',
            \UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
            \UPLOAD_ERR_CANT_WRITE => 'The file "%s" could not be written on disk.',
            \UPLOAD_ERR_NO_TMP_DIR => 'File could not be uploaded: missing temporary directory.',
            \UPLOAD_ERR_EXTENSION => 'File upload was stopped by a PHP extension.',
        ];

        $errorCode = $this->error;
        $message = isset($errors[$errorCode]) ? $errors[$errorCode] : 'The file "%s" was not uploaded due to an unknown error.';

        return sprintf($message, $this->getClientOriginalName() );
    }

    protected function getName($name)
    {
        $originalName = str_replace('\\', '/', $name);
        $pos = strrpos($originalName, '/');
        $originalName = false === $pos ? $originalName : substr($originalName, $pos + 1);

        return $originalName;
    }

}