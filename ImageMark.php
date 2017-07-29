<?php

/*
* Class adds wotermark to Image with fit sizes
* Dmitriy Morozov 2017
* Usage: new ImageMark ($path1, $path2, $path to place)
* @ return link to sys tmp file and-or place it to $path
*/

class ImageMark
{

    private $_i2Ri;
    private $_i2Mi;
    private $_i2Rs;
    private $_i2Ms;
    private $image1path;
    private $image2path;
    private $imageResized;
    private $oldSizes;
    private $factor;
    private $path;
    private $name = 'mark.jpg';
    private $tmp;

  function __construct($file1, $file2, $path = __DIR__)
  {
    $this->path = $path;
    $this->image1path = $file1;
    $this->image2path = $file2;
    $this->_i2Ri = getimagesize($file1);
    $this->_i2Mi = getimagesize($file2);
    $this->oldSizes = $this->_i2Ri;
    $this->factor = $this->_i2Mi[1]/$this->_i2Ri[1];
    $this->_i2Ri[0] = round( $this->_i2Ri[0] * $this->factor );
    $this->_i2Ri[1] = round( $this->_i2Ri[1] * $this->factor );

    $this->_i2Rs = $this->_image($this->_i2Ri, $file1);
    $this->_i2Ms = $this->_image($this->_i2Mi, $file2);

    $this->resize();
    $this->merge();
  }

  private function _image($info, $file)
  {
    switch ( $info[2] ) {
      case IMAGETYPE_GIF:   return imagecreatefromgif($file);   break;
      case IMAGETYPE_JPEG:  return imagecreatefromjpeg($file);  break;
      case IMAGETYPE_PNG:   return imagecreatefrompng($file);   break;
      default: return false;
    }
  }

  public function resize()
  {
    $this->imageResized = imagecreatetruecolor( $this->_i2Ri[0], $this->_i2Ri[1] );
    if ($this->_i2Ri[2] == IMAGETYPE_PNG) {
        imagealphablending($this->imageResized, false);
        $color = imagecolorallocatealpha($this->imageResized, 0, 0, 0, 127);
        imagefill($this->imageResized, 0, 0, $color);
        imagesavealpha($this->imageResized, true);
    }
    imagecopyresampled($this->imageResized, $this->_i2Rs, 0, 0, 0, 0, $this->_i2Ri[0], $this->_i2Ri[1], $this->oldSizes[0], $this->oldSizes[1]);
  }

  public function merge()
  {

    $canvas = imagecreatetruecolor($this->_i2Mi[0], $this->_i2Mi[1]);

    imagealphablending($canvas, false);
    $transparent = imagecolorallocatealpha($canvas, 255, 255, 255, 127);
    imagefilledrectangle($canvas, 0, 0, $this->_i2Mi[0], $this->_i2Mi[1], $transparent);
    imagecolortransparent($canvas, $transparent);
    imagealphablending($canvas, true);
    imagecopy($canvas, $this->_i2Ms, 0, 0, 0, 0, $this->_i2Mi[0], $this->_i2Mi[1]);

    imagecopy($canvas, $this->imageResized, 0, 0, 0, 0, $this->_i2Ri[0], $this->_i2Ri[1]);
    imagedestroy($this->_i2Rs);
    imagedestroy($this->_i2Ms);

    $this->tmp = tempnam(sys_get_temp_dir(), md5(time()));
    imagejpeg($canvas, $this->tmp, 90);

    if( preg_match('/^http/im', $this->path) ) {
      copy($this->tmp, __DIR__.'/'.$this->name);       
    } else {
      copy($this->tmp, $this->path.'/'.$this->name);
    }

  }

  public function render()
  {
    return '<img src="'.
    $this->path .'/'. $this->name . '" />';
  }

  public function tmpfile()
  {
    return $this->tmp;
  }

}
