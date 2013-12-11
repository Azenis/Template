<?php

/**
 * USAGE :
  if (isset($_POST["upload"])) {
  require("class/upload.php");
  try {
  $allowed = array("jpg", "jpeg", "png", "gif"); //here you can define allowed file types set to false or empty to allow all
  $upload = new upload(); //derp
  $upload->set("dir", "upload/"); //set a directory for uploading defaults to uploads/
  $upload->set("overwrite", false); //overwrite files.. default is false
  $upload->set("newfilename", true); //renames file
  $upload->set("extensions", $allowed); //set other extentions, defaults to browser supported images, set to empty to allow all
  $upload->set("maxsize", false); //size defined in bytes, set to false to use server limit.
  $upload->set("tempname", $_FILES["image"]["tmp_name"]); //mandatory
  $upload->set("filename", $_FILES["image"]["name"]); //mandatory
  $upload->set("preventBroken", true); //Prevent broken images such those with injected php to be uploaded.. defaults to true
  $upload->set("scaleratio", 0.5); //defines scaling ratio 1 = 100%, keeps aspect ratio, comment this for no resize
  $upload->set("crop", array('top' => 100, 'left' => 100, 'height' => 300, 'width' => 300)); //area to be cropped and wi
  $upload->set("quality", 85); //sets quality of image in percent. defaults to 85%
  $upload->set("replaceCharacters", array("/", "\\", "&)) //characters to be stripped from original filename
  $result = $upload->handleUpload();
  } catch (Exception $e) {
  $result = $e->getMessage();
  }
  }
 * @description this is a class used for uploading images to fileserver, will return array with image information
 * Will throw exceptions if unsuccesful upon upload
 * @return object ("filename", "extension", "fullPath", "uploadDir", "resized", "cropStatus", "size", "renamed", "quality")
 * @author Allan Thue Rehhoff @ http://rehhoff.me
 */
class upload {

    public $dir, $filename, $path, $tempname, $ext, $extensions, $newfilename;
    public $maxsize, $overwrite, $scaleratio, $crop, $quality, $chmod;
    private $resized, $cropStatus, $renamed, $preventBroken, $fileType, $replaceCharacters;
    /*
     * When i wrote this only god and I knew what i was writing.
     * Now only god knows.
     */
    function __construct() {
        $this->dir = "uploads/";
        $this->extensions = array("jpg", "jpeg", "png", "gif");
        $this->newFilename = true;
        $this->maxsize = $this->maxServerUploadSize();
        $this->overwrite = false;
        $this->scaleratio = false;
        $this->crop = false;
        $this->preventBroken = true;
        $this->quality = 85;
        //set defauult for class only properties
        $this->resized = 0;
        $this->cropStatus = 0;
        $this->renamed = 0;
        $this->chmod = 444;
        $this->replaceCharacters = array("/", "\\", "%", "&");
    }

    /*
     * PHP file upload max size is determined by 3 configuration values in php.ini
     * namely upload_max_filesize, post_max_size and memory_limit. 
     * We can get the maximum file size allowed in uploading by this method
     */

    private function maxServerUploadSize() {
        $max_upload = (int) (ini_get('upload_max_filesize'));
        $max_post = (int) (ini_get('post_max_size'));
        $memory_limit = (int) (ini_get('memory_limit'));
        return min($max_upload, $max_post, $memory_limit);
    }

    /*
     *  Function to generate a random string to minimize chance for filename collision
     */

    public function randStr($length = 24) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, mb_strlen($characters) - 1)];
        }
        return str_shuffle($randomString);
    }

    /*
     * This function will convert bytes to a human readable format
     * 
     * @param $bytes set the byte number you wish to be readable
     * @param $round Round off the byte number to this integer
     * @param $power the power to divide by 1000 or 1024 is the most common
     * @return string
     */

    public function convertBytes($bytes, $round = 2, $power = 1000) {
        // human readable format -- powers of 1000
        $unit = array("B", "KB", "MB", "GB", "TB", "PB", "EB", "YB");
        return (string) round($bytes / pow($power, ($i = floor(log($bytes, $power)))), $round) . ' ' . $unit[$i];
    }

    /*
     * Checks if file is to large
     * 
     * @return bool
     */

    public function allowedSize() {
        //allows unlimited size upload
        if ($this->get("maxsize") === false) {
            return true;
        }
        //fallback if something else than numeric is given
        if (!is_numeric($this->get("maxsize"))) {
            return true;
        }

        //else we will check if size is allowed
        $size = filesize($this->get("tempname"));
        /* if ($size > $this->get("maxsize")) {
          return false;
          } else {
          return true;
          } */
        return $size > $this->get("maxsise") ? false : true;
    }

    /*
     * Check if valid extions
     * @return bool 
     */

    public function validExt() {
        //fallback if allowed extensions is not array
        if (!is_array($this->get("extensions"))) {
            return true;
        }

        //if file extensions is not array assume everything is allowed
        if (sizeof($this->get("extensions")) == 0) {
            return true;
        }

        //check if valid exstension
        //PHP 5.4
        //return isset($this->get("extensions")[$this->get("fileType")]);
        //PHP 5.3 >
        return in_array($this->get("fileType"), $this->get("extensions"));

    }

    /*
     * Method to check if given path contains an image
     * used for checking when resizing.
     * 
     * @param $path Contains the path to image.
     * @return bool
     */

    public function isValidImage($path) {
        return getimagesize($path);
    }

    /**
     * method used to crop the image
     *
     * @param string $source_image Path of the source image
     * @param string $target_image Path of the target image
     * @param array $crop_area like array('top' => 100, 'left' => 100, 'height' => 300, 'width' => 300)
     */
    function cropImage($source_image, $target_image, $crop_area) {
        // detect source image type from extension
        $source_file_name = basename($source_image);
        $source_image_type = substr($source_file_name, -3, 3);

        // create an image resource from the source image  
        switch (strtolower($source_image_type)) {
            case 'jpg':
            case 'jpeg':
                $original_image = imagecreatefromjpeg($source_image);
                break;
            case 'gif':
                $original_image = imagecreatefromgif($source_image);
                break;
            case 'png':
                $original_image = imagecreatefrompng($source_image);
                break;
            default:
                throw new Exception("cropImage(): Invalid source image type");
                return false;
        }

        // create a blank image having the same width and height as the crop area
        // this will be our cropped image
        $cropped_image = imagecreatetruecolor($crop_area['width'], $crop_area['height']);

        // copy the crop area from the source image to the blank image created above
        imagecopy($cropped_image, $original_image, 0, 0, $crop_area['left'], $crop_area['top'], $crop_area['width'], $crop_area['height']);

        // detect target image type from extension
        //$target_file_name = basename($target_image);
        $target_image_type = $this->get("fileType"); //substr($target_file_name, -3, 3);
        // save the cropped image to disk
        switch ($target_image_type) {
            case 'jpg':
            case 'jpeg':
                imagejpeg($cropped_image, $target_image, 100);
                break;

            case 'gif':
                imagegif($cropped_image, $target_image);
                break;

            case 'png':
                imagepng($cropped_image, $target_image, 0);
                break;

            default:
                throw new Exception("cropImage(): Invalid target image type");
                imagedestroy($cropped_image);
                imagedestroy($original_image);
                return false;
        }

        // free resources
        imagedestroy($cropped_image);
        imagedestroy($original_image);

        return true;
    }

    /*
     * method used to resize image if set
     * 
     * @param $filename Filename to resize
     * @param $maxw Max widht of the image after resizing
     * $param $maxh max height of the image after resizing
     * 
     * @return bool
     */

    public function resize($filename, $maxw, $maxh) {
        $ext = $this->get("fileType");
        $quality = $this->get("quality");
        switch ($ext) {
            case 'jpeg':
            case 'jpe':
            case 'jpg':
                $srcim = imagecreatefromjpeg($filename);
                break;
            case 'gif':
                $srcim = imagecreatefromgif($filename);
                break;
            case 'png':
                $srcim = imagecreatefrompng($filename);
                break;
            default:
                return false;
        }
        $ow = imagesx($srcim);
        $oh = imagesy($srcim);
        $wscale = $maxw / $ow;
        $hscale = $maxh / $oh;
        //$scale = min($hscale, $wscale);
        $scale = $this->get("scaleratio");
        $nw = round($ow * $scale, 0);
        $nh = round($oh * $scale, 0);
        $dstim = imagecreatetruecolor($nw, $nh);
        imagecopyresampled($dstim, $srcim, 0, 0, 0, 0, $nw, $nh, $ow, $oh);
        switch ($ext) {
            case 'jpeg':
            case 'jpe':
            case 'jpg':
                imagejpeg($dstim, $filename, $quality);
                break;
            case 'gif':
                imagegif($dstim, $filename);
                break;
            case 'png':
                $png_q = floor(abs($quality / 10 - 9.9));
                imagepng($dstim, $filename, $png_q);
                break;
            default:
                return false;
        }
        imagedestroy($dstim);
        imagedestroy($srcim);
        return file_exists($filename);
    }

    /*
     * This class handles the actual upload process
     * 
     * @return array
     */

    public function handleUpload() {
        $tempfile = $this->get("tempname");
        //check if a file was found
        //no need to continue of we don't have a file to work with
        if (empty($tempfile)) {
            throw new Exception("Server did not recieve a file.. did you select one?>");
            return false;
        }
        //check if upload was done in this request
        if (!is_uploaded_file($tempfile)) {
            throw new Exception("Oups invalid file upload");
            return false;
        }
         //check if the image is broken
        if (!$this->isValidImage($tempfile) && $this->get("preventBroken") === true) {
            Throw new Exception("The image was broken, or contained invalid characters");
            return false;
        }
        //set filetype
        @$this->set("fileType", strtolower(end(explode('.', $this->get("filename"))))); //strict standards my fucking ass!
        //this is just kept for memorial purpose 
        //Strict Standards: Only variables should be passed by reference in /some/directory/upload.class.php on line 289
        //handle the filename
        if ($this->get("newfilename")) {
            $this->renamed = 1;
            $newname = $this->randStr(12) . "_" . str_replace($this->get("replaceCharacters"), //replace this
                                                              "",                              // with that
                                                              $this->get("filename"));         //on this
            $this->set("filename", $newname);
        } else {
            $this->set("filename", str_replace( $this->get("replaceCharacters"),//replace this
                                                "",                             //with that
                                                $this->get("filename")));       //on this
        }
        //set file path
        $this->set("path", "{$this->get("dir")}{$this->get("filename")}");


        //do not overwrite files if not set
        if (!$this->get("overwrite")) {
            if (file_exists($this->get("path"))) {
                Throw new Exception("File does already exist " . $this->get("filename"));
                return false;
            }
        }
        //Is it a valid extension
        if (!$this->validExt()) {
            throw new Exception("[{$this->get("fileType")}] Is not a valid extension.");
            return false;
        }
        //is file size within range?
        if (!$this->allowedSize()) {
            throw new Exception("File is to large.   max size is " . $this->convertBytes($this->get("maxsize")));
            return false;
        }
        //create directory if not exists
        if (!is_dir($this->get("dir"))) {
            mkdir($this->get("dir"));
        }
        //handle the upload
        if (move_uploaded_file($this->get("tempname"), $this->get("dir") . $this->get("filename"))) {
            //do some resizing if requested
            if ($this->get("scaleratio") !== false && $this->isValidImage($this->get("path"))) {
                //get image width and height
                list($width, $height) = getimagesize($this->get("path"));
                //calculate new dimansions
                //try to reeize
                if (!$this->resize($this->get("path"), $width, $height)) {
                    Throw new Exception("Did not suceed resizing image");
                    return false;
                } else {
                    $this->resized = 1;
                }
            }
            //now it's time for some cropping if it's configured
            if ($this->get("crop") !== false) {
                if (!is_array($this->get("crop"))) {
                    throw new Exception("The crop settings was not set properly.");
                    return false;
                } else {
                    //source image, target image, crop settings
                    if ($this->cropImage($this->get("path"), $this->get("path"), $this->get("crop"))) {
                        $this->cropStatus = 1;
                    }
                }
            }
            //Gather all the goodies to be returned.
            $return["filename"] = $this->get("filename");
            $return["extension"] = $this->get("fileType");
            $return["fullPath"] = $this->get("path");
            $return["uploadDir"] = $this->get("dir");
            $return["resized"] = $this->resized;
            $return["cropStatus"] = $this->cropStatus;
            $return["size"] = filesize($this->get("path"));
            $return["renamed"] = $this->renamed;
            $return["quality"] = $this->get("quality");
            //chmod if you need to
            if ($this->get("chmod") !== false) {
                $return["chmod"] = $this->get("chmod");
                chmod($this->get("path"), $this->get("chmod"));
            }
            return (object) $return;
        } else {
            throw new Exception("Couldn't upload" . $this->get("path"));
            return false;
        }
    }

    /*
     * getter method
     */

    public function get($property) {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }

    /*
     * setter method
     */

    public function set($property, $value) {
        if (property_exists($this, $property)) {
            $this->$property = $value;
        }

        return $this;
    }

}

?>