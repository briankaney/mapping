#!/usr/bin/php -q
<?php

//--------------------------------------------------------------------------------------
//   If run with no command line args, print out usage statement and bail
//--------------------------------------------------------------------------------------

  $argc = count($argv);

  if($argc==1)
  {
    print "\n\nUsage:\n";
    print "   add_mask_flag_via_polygon_interior.optimized.php input_mask new_flag_value pts_file pts_index known_blocks_file output_file\n\n";
    print "Examples:\n";
    print "   ./add_mask_flag_via_polygon_interior.optimized.php sample_data/ok-adair.751x501.mask.gz 2 sample_data/OK.Adair.Cherokee.pts 1 known.cherokee.txt ok-adair-cherokee.751x501.mask.gz\n\n";

    print "   Similar to the 'fill_mask_via_polygon_interior.php' utility.  Except this utility keeps all\n";
    print "   flags in the input file for points outside the specified polygon.  Only those points inside\n";
    print "   the polygon will be changed to the new flag.  Hence, this can be used to gradually build up\n";
    print "   a multi-flag raster mask for a whole polygon set.\n\n";

    print "   Five arguments:\n";
    print "   1.)  The input mask file.  Any point outside the polygon specified below will be passed to\n";
    print "        the output mask unchanged.\n";
    print "   2.)  The integer index used to flag the new polygon interior added to the input mask.  The\n";
    print "        input and output masks can be multi-flag and this utility can add one to that list.\n";
    print "   3.)  The input pts file that will supply the polygon.  It must be in 'pts' format.  See\n";
    print "        documentation elsewhere for details.\n";
    print "   4.)  The integer index of which polygon to use within the pts file.  The pts format allows\n";
    print "        for multiple polygons to be stored in sequential blocks.\n";
    print "   5.)  The output filename for the new raster mask.\n\n";

    exit(0);
  }

  include "library_mask.php";

//--------------------------------------------------------------------------------------
//   Read command line arguments.  No use of $argv after this point.
//--------------------------------------------------------------------------------------

  $mask = $argv[1];
  $flag = $argv[2];
  $pts  = $argv[3];
  $index = $argv[4];
  $known_blks = $argv[5];
  $output = $argv[6];

//--------------------------------------------------------------------------------------
//   Test for needed files.  Open input and output mask files and copy header over.
//--------------------------------------------------------------------------------------

  if(!file_exists($mask)) {  print "\nMask file not found: $mask\n\n";  exit(0);  }
  if(!file_exists($pts))  {  print "\nPolygon pts file not found: $pts\n\n";  exit(0);  }
  if(!file_exists($known_blks))  {  print "\nKnown blocks file not found: $known_blks\n\n";  exit(0);  }

  $inf = gzopen($mask,"rb");
  $header = ReadMaskHeader($inf);

  $outf = gzopen($output,"wb");
  CopyMaskHeader($outf,$header);

//--------------------------------------------------------------------------------------
//   Open the polygon pts file and read in the bounding box from the header and all the 
//   verticies.  Sample format below:
//--------------Sample start of pts file------------------------------------------------
//1
//0|12045|-106.59625|39.38292|-91.97331|33.38292|
//-102.79625 39.04542
//-102.79625 39.03915
//--------------------------------------------------------------------------------------

  $pts_fpt = fopen($pts,"r");

  fgets($pts_fpt);
  SkipNBlocks($pts_fpt,$index);
  $polygon_block = StoreBlock($pts_fpt);

  $fields = explode('|',$polygon_block[0]);
  $num_lines = $fields[1];
  $min_lon = $fields[2];
  $max_lat = $fields[3];
  $max_lon = $fields[4];
  $min_lat = $fields[5];

  $lon = Array();
  $lat = Array();
  for($i=0;$i<$num_lines;++$i)
  {
    $fields = preg_split('/ +/',$polygon_block[$i+1]);
    $lon[$i] = $fields[0];
    $lat[$i] = $fields[1];
  }

  fclose($pts_fpt);

//--------------------------------------------------------------------------------------
//   Open known blocks file and read contents.
//--------------------------------------------------------------------------------------

  $known = file($known_blks,FILE_IGNORE_NEW_LINES);
  $num_known = count($known);

  $known_flag = Array();
  $known_w = Array();
  $known_n = Array();
  $known_e = Array();
  $known_s = Array();
  for($i=0;$i<$num_known;++$i)
  {
    $fields = explode('|',$known[$i]);
    $known_flag[$i] = $fields[0];
    $known_w[$i] = $fields[1];
    $known_n[$i] = $fields[2];
    $known_e[$i] = $fields[3];
    $known_s[$i] = $fields[4];
  }

//--------------------------------------------------------------------------------------
//   Loop over all the mask points and apply the polygon point test to each one.  In this
//   low memory version, the input is read, tested and output, before moving on to the 
//   next point.  So very little of the mask file is in memory at one time.
//--------------------------------------------------------------------------------------

  for($y=0;$y<$header['num_y'];++$y)  //--The loop over latitudes
  {
    $test_lat = ($header['nlat']-$y*$header['del_lat']);

         //--If lat is outside polygon bounding box, then don't test whole row
    if($test_lat>$max_lat || $test_lat<$min_lat)
    {
      $binary_str = gzread($inf,$header['num_x']*2);
      gzwrite($outf,$binary_str);
      continue;
    }

    for($x=0;$x<$header['num_x'];++$x)  {  //--The loop over longitudes
      $test_lon = ($header['wlon']+$x*$header['del_lon']);

           //--If long is outside polygon bounding box, then don't test this point
      if($test_lon<$min_lon || $test_lon>$max_lon)
      {
        $binary_str = gzread($inf,2);
        gzwrite($outf,$binary_str);
        continue;
      }

           //--Now we'll check to see if this point is inside one of the 'known' blocks.
      $block_found = false;
      for($k=0;$k<$num_known;++$k)
      {
        if($test_lon>=$known_w[$k] && $test_lat<=$known_n[$k] && $test_lon<=$known_e[$k] && $test_lat>=$known_s[$k])
        {
          $binary_str = gzread($inf,2);   //--need to advance inf pointer whether we use this value or not.
          if($known_flag[$k]>0) { $binary_str = pack("s1",$known_flag[$k]); }  //--don't use inf value, set to known block value instead.
          gzwrite($outf,$binary_str);
          $block_found = true;
          break;
        }
      }
      if($block_found==true) { continue; }

           //--If we are still in this loop, then last two if's did not apply.  Now actually read the point.  If it is 
           //  already non-zero just copy it to output, if not we'll have to do the full polygon interior math test
      $binary_str = gzread($inf,2);
      $existing_flag = unpack("s1",$binary_str);

      if($existing_flag['1']>0) { gzwrite($outf,$binary_str); }
      else
      {
        $even_odd_test = 0;
        for($i=1;$i<$num_lines;++$i)
        { 
          if( ((($test_lat >= $lat[$i])&&($test_lat<$lat[$i-1])) || (($test_lat >= $lat[$i-1])&&($test_lat<$lat[$i]))) )
          { 
                 //---Satisfying the first 'if' above prevents a zero denominator in the second 'if' that follows below.
            if( ($test_lon < ($lon[$i] + ($test_lat-$lat[$i])*($lon[$i-1]-$lon[$i])/($lat[$i-1]-$lat[$i]))) ) { ++$even_odd_test; }
          }  
        }

        if($even_odd_test%2 == 0) { gzwrite($outf,$binary_str); }
        if($even_odd_test%2 == 1) { $binary_str = pack("s1",$flag);  gzwrite($outf,$binary_str); }
      }
    }
    print "row $y complete\n";  //--The nested lat/long/polygon loop can be very slow so report progress
  }

//--------------------------------------------------------------------------------------
//    Close both input and output mask files.
//--------------------------------------------------------------------------------------

  gzclose($inf);
  gzclose($outf);

?>
