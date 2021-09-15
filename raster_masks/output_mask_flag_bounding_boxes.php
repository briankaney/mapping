#!/usr/bin/php -q
<?php

//--------------------------------------------------------------------------------------
//   If run with no command line args, print out usage statement and bail
//--------------------------------------------------------------------------------------

  $argc = count($argv);

  if($argc==1)
  {
    print "\n\nUsage:\n";
    print "   output_mask_info.php input_file\n\n";
    print "Examples:\n";
    print "   ./output_mask_info.php sample_data/ok-adair-cherokee.751x501.mask.gz\n\n";

    print "   Given a mask file this utility outputs some information about the file.  This starts\n";
    print "   with an output of the dimensions, resolution and lat/long bounds of the mask.  The\n";
    print "   Utility then counts the number of occurrences of each flag value that is used in the\n";
    print "   file (the number of possible flags is capped at 32,766).  The utility then outputs a\n";
    print "   list of the counts of the flags used.\n\n";

    exit(0);
  }

  include "library_mask.php";

//--------------------------------------------------------------------------------------
//   Read command line arguments.  No use of $argv after this point.  Test for existence
//   of input file.
//--------------------------------------------------------------------------------------

  $mask = $argv[1];

  if(!file_exists($mask)) { print "Error: $mask file not found\n\n";  exit(0); }

//--------------------------------------------------------------------------------------
//   Set up a fixed length array of bin_counts and bounding boxes and initialize.
//--------------------------------------------------------------------------------------

  $bin_count = Array();
  $wlon = Array();
  $nlat = Array();
  $elon = Array();
  $slat = Array();
  for($i=0;$i<32767;++$i)
  {
    $bin_count[$i] = 0;
    $wlon[$i] = 180;
    $nlat[$i] = -90;
    $elon[$i] = -180;
    $slat[$i] = 90;
  }

//--------------------------------------------------------------------------------------
//   Read input file and increment the bin counts.
//--------------------------------------------------------------------------------------

  $inf = gzopen($mask,"rb");

  $header = ReadMaskHeader($inf);

  for($y=0;$y<$header['num_y'];++$y)  //--The loop over latitudes
  {
    $binary_str = gzread($inf,$header['num_x']*2);
    $format = "s".$header['num_x'];
    $values = unpack($format,$binary_str);

    for($x=0;$x<$header['num_x'];++$x)  //--The loop over longitudes
    {
      $xi = $x+1;
      if($values[$xi]<32767)
      {
        ++$bin_count[$values[$xi]];
        $test_lat = ($header['nlat']-$y*$header['del_lat']);
        $test_lon = ($header['wlon']+$x*$header['del_lon']);
        if($test_lon<$wlon[$values[$xi]]) { $wlon[$values[$xi]]=$test_lon; }
        if($test_lon>$elon[$values[$xi]]) { $elon[$values[$xi]]=$test_lon; }
        if($test_lat>$nlat[$values[$xi]]) { $nlat[$values[$xi]]=$test_lat; }
        if($test_lat<$slat[$values[$xi]]) { $slat[$values[$xi]]=$test_lat; }
      }
    }
  }

  gzclose($inf);

//--------------------------------------------------------------------------------------
//   Print out header and bin counts.
//--------------------------------------------------------------------------------------

  print "\n";
  $str = "Dimensions: ".$header['num_x']."x".$header['num_y'];
  print "$str\n";
  $str = "Bounding Box: ".$header['wlon'].",".$header['nlat'].",".($header['wlon']+($header['num_x']-1)*$header['del_lon']).
         ",".($header['nlat']-($header['num_y']-1)*$header['del_lat']);
  print "$str\n\n";

  $n=0;
  for($i=0;$i<32767;++$i)
  {
    if($bin_count[$i]>0)
    {
      ++$n;
      printf("%-3d  Flag: %-3d   Count: %-8d  Bounds: %10.5f %8.5f %10.5f %8.5f\n",$n,$i,$bin_count[$i],$wlon[$i],$nlat[$i],$elon[$i],$slat[$i]);
    }
  }
  print "\n";

?>
