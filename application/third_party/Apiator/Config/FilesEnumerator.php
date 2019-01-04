<?php
/**
 * Created by PhpStorm.
 * User: vsergiu
 * Date: 10/10/18
 * Time: 1:50 PM
 */
namespace Apiator\Config;

class FilesEnumerator implements EnumeratorInterface
{
    private $path;
    private $fileExt;

    private function __construct ($path, $fileExt)
    {

        $this->path = $path;
        $this->fileExt = $fileExt;
    }

    static function init($location, $fileExt,$currentUser)
    {

        $path = $location."/".$currentUser;
        if(!is_dir($path))
            return null;
        return new FilesEnumerator($path,$fileExt);
    }

    /**
     * @param null $match
     * @param int $offset
     * @param int $pageSize
     * @return array
     */
    function enumerate($match=null, $pageSize=20,$offset=0)
    {
        if(!$match)
            $match = "/.*/";

        $dir = opendir($this->path);
        $cnt = 0;
        $files = [];
        while(($file = readdir($dir)) && ($cnt<$pageSize)) {
            $tmp = explode(".".$this->fileExt,$file);
            if(count($tmp)<2)
                continue;
            if($offset>0) {
                $offset--;
                continue;
            }

            if(!@preg_match_all($match,$file))
                continue;
            $cnt++;
            $files[] = $tmp[0];
        }
        closedir($dir);
        return $files;
    }


    /**
     * @param $name
     * @return mixed
     */
    function delete ($name)
    {
        $filePath = $this->path."/".$name.".".$this->fileExt;
        return unlink($filePath);
    }

    /**
     * @param $oldName
     * @param $newName
     * @return mixed
     */
    function rename ($oldName, $newName)
    {
        rename($this->path."/".$oldName.$this->fileExt,$this->path."/".$newName.$this->fileExt);
    }

    /**
     * @param $name
     * @return mixed
     */
    function exists ($name)
    {
        return FilesEnumerator::item_exists($this->path."/".$name.$this->fileExt);
    }

    /**
     * @param string $name
     * @param $content
     * @return ApiConfig
     */
    function add ($name, $content)
    {
        $filePath = $this->fullFilePath($name);
        if(is_null($content)) {
            // TODO: log empty data
            echo "File: empty data. Nothing to save";
            return null;
        }

        if(is_file($filePath)) {
            // TODO: log duplicate file
            echo "File: duplicate file $name";
            return null;
        }

        // create file for writing to it
        $fp = fopen($filePath,"w");
        if(!$fp) {
            // TODO: log cannot create file
            echo "File: could not create new config $name";
            return false;
        }

        $toWrite = !is_string($content)?json_encode($content):$content;

        fwrite($fp,$toWrite);
        fclose($fp);

        return ApiConfig::init($name,$content,$this);


        // TODO: Implement add() method.
    }

    /**
     * @param $name
     * @param bool $asArray
     * @return ApiConfig
     */
    function get ($name, $asArray=true)
    {
         $id = $this->fullFilePath($name);
         if(!file_exists($id)) {
             // TODO: log file not found
             //echo "File: file $id does not exist\n";
             return null;
         }

        $data = file_get_contents($id);
        if(!$data) {
            // TODO: log could not read file
            //echo "File: could not read file";
            return null;
        }

        return ApiConfig::init($name,$data,$this,$asArray);
    }

    private function fullFilePath($name) {
        return $this->path."/$name.".$this->fileExt;
    }

    /**
     * @param $name
     * @param $content
     * @return mixed
     */
    function update ($name, $content)
    {
        $fullFileName = $this->fullFilePath($name);
        $fp = fopen($fullFileName,"w");
        if(!$fp)
            return false;

        $write = fwrite($fp,$content)!==false;
        fclose($fp);
        return $write;
    }
}