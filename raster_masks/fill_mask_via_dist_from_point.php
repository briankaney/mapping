#!/usr/bin/php -q
<?php

//--------------------------------------------------------------------------------------
//   If run with no command line args, print out usage statement and bail
//--------------------------------------------------------------------------------------

  $argc = count($argv);

  if($argc==1)
  {
    print "\n\nUsage:\n";
    print "   fill_mask_via_dist_from_point.php blank_mask target_lon target_lat dist_cutoff(km) output_file\n\n";
    print "Examples:\n";
    print "   ./fill_mask_via_dist_from_point.php sample_data/ok.751x501.blank.gz -95 36 25 ok-tahl.mask.gz\n\n";

    print "   This utility creates a new raster mask that uses the distance from a specific location to";
    print "   flag interior points and uses an input mask as a template.\n\n";

    print "   Five arguments:\n";
    print "   1.)  The input mask template file.  Only the header is read, so how the mask is flagged\n";
    print "        is not relevant.  The header parameters will just be copied to the newly created file.\n";
    print "   2.)  The longitude of the target location.\n";
    print "   3.)  The latitude of the target location.\n";
    print "   4.)  The distance cutoff (in km).  Beyond this point the mask will be flagged zero.  Within\n";
    print "        this radius the flag will be the rounded integer number of km from the target location.\n";
    print "   5.)  The output filename for the new raster mask.\n\n";

    print "   To save on floating point math, there is an assumption that the points need to be within\n";
    print "   5 degrees of each other in both long and lat.  A hard-code needs to be changed if this is\n";
    print "   not the case\n\n";

    exit(0);
  }

  include "library_mask.php";

//--------------------------------------------------------------------------------------
//   Read command line arguments.  No use of $argv after this point.
//--------------------------------------------------------------------------------------

  $mask = $argv[1];
  $target_lon = $argv[2];
  $target_lat = $argv[3];
  $dist_cutoff = $argv[4];
  $output = $argv[5];

//--------------------------------------------------------------------------------------
//   Test for needed files.  Open mask input, read header and close.  Open mask ouput
//   and copy over input mask header.
//--------------------------------------------------------------------------------------

  if(!file_exists($mask)) {  print "\nMask file not found: $mask\n\n";  exit(0);  }

  $inf = gzopen($mask,"rb");
  $header = ReadMaskHeader($inf);
  gzclose($inf);

  $outf = gzopen($output,"wb");
  CopyMaskHeader($outf,$header);

//--------------------------------------------------------------------------------------
//--------------------------------------------------------------------------------------

  for($y=0;$y<$header['num_y'];++$y)  //--The loop over latitudes
  {
    $test_lat = ($header['nlat']-$y*$header['del_lat']);

    for($x=0;$x<$header['num_x'];++$x)  {  //--The loop over longitudes
      $test_lon = ($header['wlon']+$x*$header['del_lon']);

         //--Test to see if point is within 5 degree long or lat.  Otherwise flag as zero without a dist test
         //  Needs more work to make this a robust feature.
      if(abs($test_lon-$target_lon)>5 || abs($test_lat-$target_lat)>5)
      {
        $binary_str = pack("s1",0);
        gzwrite($outf,$binary_str);
        continue;
      }

      $dist = distanceBetweenTwoLatLonPts($test_lon,$test_lat,$target_lon,$target_lat,"km");
//$flag = intval(round($dist));
//print "$flag ";

      if($dist>$dist_cutoff)
      {
        $binary_str = pack("s1",0);
        gzwrite($outf,$binary_str);
      }
      if($dist<=$dist_cutoff)
      {  
        $flag = intval(round($dist));
        $binary_str = pack("s1",$flag);
        gzwrite($outf,$binary_str);
      }
    }
  }

//--------------------------------------------------------------------------------------
//    Close the output raster mask
//--------------------------------------------------------------------------------------

  gzclose($outf);

?>

