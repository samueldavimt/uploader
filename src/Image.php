<?php

namespace Uploader\Uploader;

use Exception;
use Intervention\Image\Exception\ImageException;
use Intervention\Image\ImageManager;
use Intervention\Image\Image as ImageIntervention;

class Image extends Uploader
{
    /**
     * engine for image manipulation
     * @var ImageManager
     */
    private $engine;

    /**
     * list of allowed types
     * @var array
     */
    public static $allowedTypes = [
        "image/png",
        "image/jpg",
        "image/jpeg"
    ];

    /**
     * list of allowed extensions
     * @var array
     */
    public static $allowedExtensions = [
        "png",
        "jpg",
        "jpeg"
    ];

    /**
     * @param string $baseDirUpload
     * @param string $typeFileUpload
     * @param boolean $monthYearPath
     */
    public function __construct(string $baseDirUpload, string $typeFileUpload, bool $monthYearPath=false)
    {
        parent::__construct($baseDirUpload, $typeFileUpload, $monthYearPath);
        $this->engine = new ImageManager();
    }

    /**
     * maxsize = 8mb
     * @param array $data
     * @param string $fileName
     * @param integer $width
     * @param integer $quality
     * @param integer $maxSize
     * @return string|null
     */
    public function upload(array $data, string $fileName, int $width=2000, $quality=90, $maxSize=8388608): ?string
    {
        if(empty($fileName)){
            $this->fail("file name is empty");
        }

        if($data['size'] > $maxSize){
            $this->fail("max size exceeded");
        }

        if(!$this->checkFileData($data)){
            return null;
        }

        $this->ext = $this->getExtension($data['name']);
        $this->name = $this->createName($fileName);
        $this->path = $this->fullPath();

        if($this->isGif($data)){
            move_uploaded_file($data['tmp_name'], $this->path);
            return $this->path;
        }

        if(!$this->generateImage($data['tmp_name'], $width)){
            $this->fail("error in generate image");
        }

        if(!$this->save($quality)){
            $this->fail("error in save");
        }
        return $this->path;
    }

    /**
     * @param array $data
     * @return boolean
     */
    private function isGif(array $data): bool
    {
        if($data['type'] == "image/gif"){
            return true;
        }else{
            return false;
        }
    }

    /**
     * @param string $fileLocal
     * @param int $width
     * @return boolean
     */
    private function generateImage(string $fileLocal, int $width): bool
    {
        try {

            $this->file = $this->engine->make($fileLocal)->resize($width, null, function($constraint){
                $constraint->aspectRatio();
            });
            return true;

        } catch (ImageException | Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @param integer $quality
     * @return boolean
     */
    private function save(int $quality): bool
    {
        try{
            /**
             * @var ImageIntervention $file
             */
            $this->file->save($this->path, $quality);
            return true;
    
        } catch (ImageException | Exception $e) {
            $this->fail($e->getMessage());
        }

    }
}