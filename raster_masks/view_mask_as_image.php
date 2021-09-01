#!/usr/bin/php -q
<?php

//--------------------------------------------------------------------------------------
//   If run with no command line args, print out usage statement and bail
//--------------------------------------------------------------------------------------

  $argc = count($argv);

  if($argc==1)
  {
    print "\n\nUsage:\n";
    print "   view_mask_as_image.php input_mask_file output_image_file\n\n";
    print "Examples:\n";
    print "   ./view_mask_as_image.php sample_data/ok-adair-cherokee.750x500.mask.gz ok-adair-cherokee.png\n\n";

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

  $mask = $argv[1];
  $img  = $argv[2];

  if(!file_exists($mask)) { print "Error: $mask file not found\n\n";  exit(0); }

//--------------------------------------------------------------------------------------
//   Read input file
//--------------------------------------------------------------------------------------

  $inf = gzopen($mask,"rb");
  $header = ReadMaskHeader($inf);

  rewind($inf);
  $flags = ReadMaskBody($inf);
  gzclose($inf);

//--------------------------------------------------------------------------------------
//   Create an all black image the same dimensions as the raster map
//--------------------------------------------------------------------------------------

  $image = imagecreate($header['num_x'],$header['num_y']);

  $black = imagecolorallocate($image,0,0,0);
  imagefilledrectangle($image,0,0,$header['num_x'],$header['num_y'],$black);

//--------------------------------------------------------------------------------------
//--------------------------------------------------------------------------------------

  $colors = Array();
  $colors[0] = imagecolorallocate($image,255,255,255);
  $colors[1] = imagecolorallocate($image,0,0,255);
  $colors[2] = imagecolorallocate($image,0,230,230);
  $colors[3] = imagecolorallocate($image,0,255,0);
  $colors[4] = imagecolorallocate($image,180,255,0);
  $colors[5] = imagecolorallocate($image,255,255,0);
  $colors[6] = imagecolorallocate($image,255,180,0);
  $colors[7] = imagecolorallocate($image,255,0,0);
  $colors[8] = imagecolorallocate($image,255,0,255);
  $colors[9] = imagecolorallocate($image,200,0,120);
  $colors[10] = imagecolorallocate($image,180,90,0);
  $colors[11] = imagecolorallocate($image,180,180,180);

//--------------------------------------------------------------------------------------
//   Step through all the pixels and set the image color according to the flag value.
//--------------------------------------------------------------------------------------

  for($y=0;$y<$header['num_y'];++$y)
  {
    for($x=0;$x<$header['num_x'];++$x)
    {
      $value = $flags[$y*$header['num_x']+$x];

      if($value==0) { continue; }
      $color = $colors[($value-1)%12];
      imagesetpixel($image,$x,$y,$color);
    }
  }

//--------------------------------------------------------------------------------------
//   Write out the final image and clean up memory
//--------------------------------------------------------------------------------------

  imagepng($image,$img);
  imagedestroy($image);

?>
