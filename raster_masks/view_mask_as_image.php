#!/usr/bin/php -q
<?php

//--------------------------------------------------------------------------------------
//   If run with no command line args, print out usage statement and bail
//--------------------------------------------------------------------------------------

  $argc = count($argv);

  if($argc==1)
  {
    print "\n\nUsage:\n";
    print "   view_mask_as_image.php [options] input_mask_file output_image_file\n\n";
    print "Examples:\n";
    print "   ./view_mask_as_image.php sample_data/ok-adair-cherokee.750x500.mask.gz ok-adair-cherokee.png\n\n";

    print "   A way to get a simple, quick view of a raster map.  User specifies an input raster\n";
    print "   map file and a name for the output image.  The output will be a png with the same\n";
    print "   dimensions as the raster map.  It will cover the full lat/long region of the mask.\n";
    print "   Flag zero regions will be black and all non-zero flags will rotate thru a 20 color\n";
    print "   palette.  The one allowed option is to add '-shift' which will rotate the colors\n";
    print "   through the first 19 only.  This gives a little help with the issue of touching blocks\n";
    print "   having the same color.  19 and 20 (or any number of) colors will have this problem but\n";
    print "   since these values are relatively prime the two setting will not share the same matches\n";
    print "   up to an index 380.\n\n";

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

  $num_colors=20;
  for($i=1;$i<=($argc-3);++$i)
  {
    if(strpos($argv[$i],"-shift")!==false) { $num_colors=19; }
  }

  if(!file_exists($mask)) { print "Error: $mask file not found\n\n";  exit(0); }

//--------------------------------------------------------------------------------------
//   Open input file and read header
//--------------------------------------------------------------------------------------

  $inf = gzopen($mask,"rb");
  $header = ReadMaskHeader($inf);

//--------------------------------------------------------------------------------------
//   Create an all black image the same dimensions as the raster map
//--------------------------------------------------------------------------------------

  $image = imagecreate($header['num_x'],$header['num_y']);

  $black = imagecolorallocate($image,0,0,0);
  imagefilledrectangle($image,0,0,$header['num_x'],$header['num_y'],$black);

//--------------------------------------------------------------------------------------
//   Set up color array.  These 12 colors will be 'rotated' for the different mask flags
//--------------------------------------------------------------------------------------

  $colors = Array();
  $colors[0] = imagecolorallocate($image,255,255,255);
  $colors[1] = imagecolorallocate($image,0,0,200);
  $colors[2] = imagecolorallocate($image,0,0,255);
  $colors[3] = imagecolorallocate($image,0,170,170);
  $colors[4] = imagecolorallocate($image,0,230,230);
  $colors[5] = imagecolorallocate($image,0,200,0);
  $colors[6] = imagecolorallocate($image,0,255,0);
  $colors[7] = imagecolorallocate($image,150,255,0);
  $colors[8] = imagecolorallocate($image,200,255,0);
  $colors[9] = imagecolorallocate($image,255,255,0);
  $colors[10] = imagecolorallocate($image,255,200,0);
  $colors[11] = imagecolorallocate($image,255,150,0);
  $colors[12] = imagecolorallocate($image,255,0,0);
  $colors[13] = imagecolorallocate($image,255,0,255);
  $colors[14] = imagecolorallocate($image,200,0,120);
  $colors[15] = imagecolorallocate($image,180,90,0);
  $colors[16] = imagecolorallocate($image,120,40,0);
  $colors[17] = imagecolorallocate($image,80,80,80);
  $colors[18] = imagecolorallocate($image,130,130,130);
  $colors[19] = imagecolorallocate($image,180,180,180);

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
      $color = $colors[($number-1)%$num_colors];
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
