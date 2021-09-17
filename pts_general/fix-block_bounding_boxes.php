#!/usr/bin/php -q
<?php

//--------------------------------------------------------------------------------------
//   If run with no command line args, print out usage statement and bail
//--------------------------------------------------------------------------------------

  $argc = count($argv);

  if($argc==1)
  {
    print "\n\nUsage:\n";
    print "  fix-block_bounding_boxes.php infile\n\n";

    print "Examples:\n";
    print "  ./fix-block_bounding_boxes.php sample_data/sample3.bad_bounding_boxes.pts\n\n";

    print "  Input file will not be a valid pts file, but it needs to be close.\n";
    print "  The block bounding box values can be bad or empty.  This script will evaluate them\n";
    print "  based on the points present.  The file is copied out with just those fields fixed.\n";
    print "  Otherwise the rest of the required header entries need to be accurate already.\n";
    print "  Low memory version.  Capture output via redirect.\n\n";

    exit(0);
  }

  if($argc!=2) { print "\nFatal Error: Wrong number of command line arguments\n\n";  exit(0); }

  include 'library_pts.php';

//--------------------------------------------------------------------------------------

  $infile = $argv[1];

  if(!file_exists($infile)) { print "\nInput file not found: $infile\n\n";  exit(0); }

  $inf = fopen($infile,'r');
  $num_blocks = trim(fgets($inf));

  print "$num_blocks\n";

  for($i=0;$i<$num_blocks;++$i)
  {
    $block = StoreBlock($inf);
    $bb = FindBoundingBoxOfBlock($block);

    $col = explode('|',$block[0]);
    $num_col = count($col)-1;

    $new_header = $col[0]."|".$col[1]."|".$bb[0]."|".$bb[1]."|".$bb[2]."|".$bb[3]."|";
    for($j=6;$j<$num_col;++$j) { $new_header = $new_header.$col[$j]."|"; }
    print "$new_header\n";

    for($j=1;$j<=$col[1];++$j) { print "$block[$j]\n"; }
  }

  fclose($inf);

?>
