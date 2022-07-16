<?php

namespace Upload\Uploader;

class File extends Uploader
{
    /**
     * list from allowed types
     * @var array
     */
    public static $allowedTypes = [
        "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
        "application/msword",
        "application/pdf",
    ];

    /**
     * list from allowed extensions
     * @var array
     */
    public static $allowedExtensions = [
        "doc",
        "docx",
        "pdf"
    ];

  /**
   * maxSize = 2mb
   * @param array $fileData
   * @param string $fileName
   * @param integer $maxSize
   * @return void
   */
    public function upload(array $fileData, string $fileName, $maxSize=2000000): ?string
    {
        if(empty($fileName)){
            $this->fail("file name is empty");
        }

        if($fileData['size'] > $maxSize){
            $this->fail("max size exceeded");
        }

        if(!$this->checkFileData($fileData)){
            return null;
        }

        $this->ext = $this->getExtension($fileData['name']);
        $this->name  = $this->createName($fileName);
        $this->path = $this->fullPath();

        if(!$this->save($fileData['tmp_name'])){
            $this->fail("erro in save");
        }

        return $this->path;
    }

    /**
     * @param string $file
     * @return boolean
     */
    private function save(string $file): bool
    {
        return ((move_uploaded_file($file, $this->path)) ? true : false);
    }
}
