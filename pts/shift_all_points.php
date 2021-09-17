#!/usr/bin/php -q
<?php

//--------------------------------------------------------------------------------------
//   If run with no command line args, print out usage statement and bail
//--------------------------------------------------------------------------------------

  $argc = count($argv);

  if($argc==1)
  {
    print "\n\nUsage:\n";
    print "  shift_all_points.php infile long_shift lat_shift\n\n";

    print "Examples:\n";
    print "  ./shift_all_points.php sample_data/sample1.pts 0 0.02\n\n";

    print "  Input file must be a valid pts file.  All the longitude and latitudes in the entire\n";
    print "  file will be subject to a uniform constant additive shift supplied by the user.  Both\n";
    print "  shift arguments must be given so use '0' for no shift.  The header line bounding boxes\n";
    print "  will also be shifted.  Low memory version.  Capture output via redirect.\n\n";

    exit(0);
  }

  if($argc!=4) { print "\nFatal Error: Wrong number of command line arguments\n\n";  exit(0); }

  include 'library_pts.php';

//--------------------------------------------------------------------------------------

  $infile = $argv[1];
  $long_shift = $argv[2];
  $lat_shift  = $argv[3];

  if(!file_exists($infile)) { print "\nInput file not found: $infile\n\n";  exit(0); }

  $inf = fopen($infile,'r');
  $num_blocks = trim(fgets($inf));

  print "$num_blocks\n";

  for($i=0;$i<$num_blocks;++$i)
  {
    $block = StoreBlock($inf);

    $head_col = explode('|',$block[0]);
    $num_head_col = count($head_col)-1;
    $num_points = $head_col[1];

    for($j=1;$j<=$num_points;++$j)
    {
      $col = explode(' ',$block[$j]);
      $lon = $col[0];
      $lat = $col[1];

      $lon = $lon + $long_shift;
      $lat = $lat + $lat_shift;
      $block[$j] = $lon." ".$lat;
    }

    $bb = FindBoundingBoxOfBlock($block);

    $new_header = $head_col[0]."|".$num_points."|".$bb[0]."|".$bb[1]."|".$bb[2]."|".$bb[3]."|";
    for($j=6;$j<$num_head_col;++$j) { $new_header = $new_header.$head_col[$j]."|"; }
    print "$new_header\n";

    for($j=1;$j<=$num_points;++$j) { print "$block[$j]\n"; }
  }

  fclose($inf);

?>
