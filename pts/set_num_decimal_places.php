#!/usr/bin/php -q
<?php

//--------------------------------------------------------------------------------------
//   If run with no command line args, print out usage statement and bail
//--------------------------------------------------------------------------------------

  $argc = count($argv);

  if($argc==1)
  {
    print "\n\nUsage:\n";
    print "  set_num_decimal_places.php infile num_decimal_places\n\n";

    print "Examples:\n";
    print "  ./set_num_decimal_places.php sample_data/sample1.pts 3\n\n";

    print "  Input must be 'pts' format.  Output is valid 'pts' format with the same number of\n";
    print "  lines, but there may be many duplicate points now.  Another script is needed to fix\n";
    print "  that.  Bounding boxes will reflect the new resolution.  Low memory version.\n";
    print "  Capture output via redirect.\n\n";

    exit(0);
  }

  if($argc!=3) { print "\nFatal Error: Wrong number of command line arguments\n\n";  exit(0); }

  include 'library_pts.php';

//--------------------------------------------------------------------------------------

  $infile = $argv[1];
  $num_dec_pl = $argv[2];

  if(!file_exists($infile)) {  print "\nInput file not found: $infile\n\n";  exit(0);  }

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

      $lon = round($lon,$num_dec_pl);
      $lat = round($lat,$num_dec_pl);
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
