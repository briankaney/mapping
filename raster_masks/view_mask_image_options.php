#!/usr/bin/php -q
<?php

//--------------------------------------------------------------------------------------
//   If run with no command line args, print out usage statement and bail
//--------------------------------------------------------------------------------------

  $argc = count($argv);

  if($argc==1)
  {
    print "\n\nUsage:\n";
    print "   view_mask_image_options.php [options] input_mask_file output_image_file\n\n";
    print "Examples:\n";
    print "   ./view_mask_image_options.php sample_data/ok-adair-cherokee.751x501.mask.gz ok-adair-cherokee.png\n";
    print "   ./view_mask_image_options.php -h=1-1 sample_data/ok-adair-cherokee.751x501.mask.gz ok-adair-cherokee.png\n\n";

    print "   A way to get a simple, quick view of a raster map.  User specifies an input raster\n";
    print "   map file and a name for the output image.  The output will be a png with the same\n";
    print "   dimensions as the raster map.  It will cover the full lat/long region of the mask.\n";
    print "   Flag zero regions will be black and all non-zero flags will rotate thru a 12 color\n";
    print "   palette.\n\n";
    print "   Other map utilities will be needed to get a map with more options for size, region\n";
    print "   coverage, Mercator projection, and color choices.\n\n";

    exit(0);
  }

  include "library_mask.php";

//--------------------------------------------------------------------------------------
//   Read command line arguments.  No use of $argv after this point.  Test for existence
//   of input file.
//--------------------------------------------------------------------------------------

  $mask = $argv[$argc-2];
  $img  = $argv[$argc-1];

  $highlight_min=0;
  $highlight_max=32767;
  for($i=1;$i<=($argc-3);++$i)
  {
    if(strpos($argv[$i],"-h=")!==false)
    {
      $fields = explode('=',$argv[$i]);
      $parts = explode('-',$fields[1]);
      $highlight_min = intval($parts[0]);
      $highlight_max = intval($parts[1]);
    }
  }  

//--------------------------------------------------------------------------------------
//   Open input file and read header
//--------------------------------------------------------------------------------------

  if(!file_exists($mask)) { print "Error: $mask file not found\n\n";  exit(0); }

  $inf = gzopen($mask,"rb");
  $header = ReadMaskHeader($inf);

//--------------------------------------------------------------------------------------
//   Create an all black image the same dimensions as the raster map
//--------------------------------------------------------------------------------------

  $image = imagecreate($header['num_x'],$header['num_y']);

  $black = imagecolorallocate($image,0,0,0);
  imagefilledrectangle($image,0,0,$header['num_x'],$header['num_y'],$black);

//--------------------------------------------------------------------------------------
//--------------------------------------------------------------------------------------

  $rain_red   = Array(255,240,225,200,255,255,255,255);
  $rain_green = Array(  0,  0,  0,  0,180,150,120, 90);
  $rain_blue  = Array(  0,  0,  0,  0,  0,  0,  0,  0);

  $num_rainbow = count($rain_red);
  $rainbow = Array();
  for($i=0;$i<$num_rainbow;++$i)
  {
    $rainbow[$i] = imagecolorallocate($image,$rain_red[$i],$rain_green[$i],$rain_blue[$i]);
  }

  $grey_red   = Array(70,80,90,100,110,120,130,140,150,160,170,180);
  $grey_green = Array(70,80,90,100,110,120,130,140,150,160,170,180);
  $grey_blue  = Array(70,80,90,100,110,120,130,140,150,160,170,180);

  $num_grey = count($grey_red);
  $greyscale = Array();
  for($i=0;$i<$num_grey;++$i)
  {
    $greyscale[$i] = imagecolorallocate($image,$grey_red[$i],$grey_green[$i],$grey_blue[$i]);
  }

//--------------------------------------------------------------------------------------
//   Step through all the pixels and set the image color according to the flag value.
//--------------------------------------------------------------------------------------

  for($y=0;$y<$header['num_y'];++$y)
  {
    for($x=0;$x<$header['num_x'];++$x)
    {
      $binary_str = gzread($inf,2);
      $value = unpack("s1",$binary_str);
      $number = $value['1'];

      if($number==0) { continue; }

      if($number>=$highlight_min && $number<=$highlight_max) { $color = $rainbow[($number-1)%8]; }
      else { $color = $greyscale[($number-1)%12]; }

      imagesetpixel($image,$x,$y,$color);
    }
  }

//--------------------------------------------------------------------------------------
//   Close input file, write out the final image and clean up memory
//--------------------------------------------------------------------------------------

  gzclose($inf);
  imagepng($image,$img);
  imagedestroy($image);

?>
