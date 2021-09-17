#!/usr/bin/php -q
<?php

//--------------------------------------------------------------------------------------
//   If run with no command line args, print out usage statement and bail
//--------------------------------------------------------------------------------------

  $argc = count($argv);

  if($argc==1)
  {
    print "\n\nUsage:\n";
    print "  info-global_bounding_box.php infile\n\n";

    print "Examples:\n";
    print "  ./info-global_bounding_box.php sample_data/KS.FourSquareCounties.pts\n\n";

    print "  Input file will be a valid pts file.  This util reads all the bounding boxes, then\n";
    print "  determines and outputs an overall bounding boxes for the whole file.\n\n";

    exit(0);
  }

  include 'library_pts.php';

//--------------------------------------------------------------------------------------

  $infile = $argv[1];

  if(!file_exists($infile)) { print "\nInput file not found: $infile\n\n";  exit(0); }

  $inf = fopen($infile,'r');
  $num_blocks = trim(fgets($inf));

  $wlon = 180;
  $nlat = -90;
  $elon = -180;
  $slat = 90;

  for($i=0;$i<$num_blocks;++$i)
  {
    $block = StoreBlock($inf);
    $bb = FindBoundingBoxOfBlock($block);

    if($bb[0]<$wlon) { $wlon = $bb[0]; }
    if($bb[1]>$nlat) { $nlat = $bb[1]; }
    if($bb[2]>$elon) { $elon = $bb[2]; }
    if($bb[3]<$slat) { $slat = $bb[3]; }
  }

  print "$wlon $nlat $elon $slat\n";

  fclose($inf);

?>
