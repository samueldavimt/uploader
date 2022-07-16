<?php

namespace Upload\Uploader;

abstract class Uploader
{

    /**
     * resource file
     */
    protected $file;

    /**
     * file path
     * 
     * @var string
     */
    protected $path;

    /**
     * file name
     * @var string
     */
    protected $name;

    /**
     * file extension
     * @var string
     */
    protected $ext;

    /**
     * list from allowed types
     * @var array
     */
    public static $allowedTypes = [];

    /**
     * list from allowed extensions
     * @var array
     */
    public static $allowedExtensions = [];

     /**
     * stores the error, if it exists
     * @var string
     */
    protected $fail;

    /**
     * @param string $baseDirUpload
     * @param string $typeFileUpload
     * @param boolean $monthYearPath
     * @example $u = new Upload('uploads', 'images', true) = storage/images/2022/04/
     */
    public function __construct(string $baseDirUpload, string $typeFileUpload, bool $yearMonthPath=false)
    {
        $dir = "{$baseDirUpload}/{$typeFileUpload}"; 
        if($yearMonthPath){
            $dir .=  "/" . date("Y/m");
        }

        $this->handleDirectory($dir);
        $this->path = $dir;
    }

    /**
     * manipulate directory estruture
     * @param string $dirName
     * @param integer $mode
     * @return void
     */
    protected function handleDirectory(string $dirName, $mode=0755): void
    {
        if(!file_exists($dirName) || !is_dir($dirName)){
            mkdir($dirName, $mode, true);
        }
    }

    /**
     * @param string $type
     * @return boolean
     */
    protected function isAllowedType(string $type): bool
    {
        return (in_array($type, static::$allowedTypes) ? true : false);
    }

    /**
     * @param string $ext
     * @return boolean
     */
    protected function isAllowedExtension(string $ext): bool
    {
        return (in_array($ext, static::$allowedExtensions) ? true : false);
    }

    /**
     * validate data of file
     * @param array $data
     * @return boolean
     */
    protected function checkFileData(array $data): bool
    {
        if($data["error"] != 0 || empty($data['name']) || empty($data['type']) || empty($data['tmp_name'])){
            $this->fail("error in read file data");
        }

        if(!$this->isAllowedType($data['type'])){
            $this->fail("invalid type");
        }

        if(!$this->isAllowedExtension($this->getExtension($data['name']))){
            $this->fail("invalid extension");
        }

        return true;
    }

    /**
     * @return string
     */
    protected function fullPath(): string
    {
        $fullPath = $this->path . "/" . $this->name;
        while(file_exists($fullPath)){
           $this->renameFile($this->name);
           $fullPath = $this->path . "/" . $this->name;
        }

        return $fullPath;
    }
    
    /**
     * @param string $name
     * @return string
     */
    protected function createName(string $name): string
    {
        $utf8 = array(
            '/[áàâãªä]/u'   =>   'a',
            '/[ÁÀÂÃÄ]/u'    =>   'A',
            '/[ÍÌÎÏ]/u'     =>   'I',
            '/[íìîï]/u'     =>   'i',
            '/[éèêë]/u'     =>   'e',
            '/[ÉÈÊË]/u'     =>   'E',
            '/[óòôõºö]/u'   =>   'o',
            '/[ÓÒÔÕÖ]/u'    =>   'O',
            '/[úùûü]/u'     =>   'u',
            '/[ÚÙÛÜ]/u'     =>   'U',
            '/ç/'           =>   'c',
            '/Ç/'           =>   'C',
            '/ñ/'           =>   'n',
            '/Ñ/'           =>   'N',
            '/–/'           =>   '-', // UTF-8 hyphen to "normal" hyphen
            '/[’‘‹›‚]/u'    =>   ' ', // Literally a single quote
            '/[“”«»„]/u'    =>   ' ', // Double quote
            '/ /'           =>   ' ', // nonbreaking space (equiv. to 0x160)
        );

        $string = mb_convert_case(strip_tags($name), MB_CASE_LOWER);
        $string = preg_replace(array_keys($utf8), array_values($utf8), $string);
        $string = preg_replace('/[^A-Za-z0-9\- ]/', '', utf8_decode($string));
        $name = str_replace(["-----", "----", "---", "--"], "-", str_replace(" ", "-", $string));
    
        //$name = $string . "-" . random_int(100, 500) . random_int(100, 500);
        return "{$name}.{$this->ext}";
    }

    /**
     * @param string $name
     * @return string
     */
    protected function renameFile(string $name): string
    {
        $name = str_replace(".{$this->getExtension($name)}", "", $name);
        $name .= "-" . random_int(100, 500) . random_int(100, 500);
        $name .= ".{$this->ext}";
        $this->name = $name;
        return $name;
    }

    /**
     * @param string $fileName
     * @return string
     */
    protected function getExtension(string $fileName): string
    {
        return pathinfo($fileName, PATHINFO_EXTENSION);
    }


    /**
     * set fail
     * @param string $message
     * @return void
     */
    protected function fail(string $message)
    {
        $this->fail = $message;
        throw new \Exception($message);
    }

    /**
     * @return string|null
     */
    public function getFail(): ?string
    {
        return $this->fail;
    }
}
