#!/usr/bin/php -q
<?php

//--------------------------------------------------------------------------------------
//   If run with no command line args, print out usage statement and bail
//--------------------------------------------------------------------------------------

  $argc = count($argv);

  if($argc==1)
  {
    print "\n\nUsage:\n";
    print "  remove_duplicate_interior_points.php infile\n\n";

    print "Examples:\n";
    print "  ./remove_duplicate_interior_points.php sample_data/duplicates.pts\n\n";

    print "  Input must be in 'pts' format.  If two consecutive lines are identical, then only one is\n";
    print "  printed out.  This is done in pair-wise fashion so 3 or more consecutive identical lines\n";
    print "  will also reduce to 1.  All header info is either adjusted or is just still valid.\n";
    print "  Capture output via redirect.\n\n";

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

    $head_col = explode('|',$block[0]);
    $num_head_col = count($head_col)-1;
    $orig_num_points = $head_col[1];

    if($orig_num_points<2)
    {
      for($j=0;$j<=$orig_num_points;++$j) { print "$block[$j]\n"; }
      continue;
    }

    $keep_flag = array();
    $keep_flag[0] = 0;    //--don't keep header as it will be redone
    for($j=1;$j<=$orig_num_points;++$j) { $keep_flag[$j]=1; }    //--flag all points as keepers intially

    for($j=1;$j<$orig_num_points;++$j) {
      if($block[$j] == $block[$j+1]) { $keep_flag[$j]=0; }
    }

    $new_num_points = 0;
    for($j=0;$j<=$orig_num_points;++$j) { if($keep_flag[$j]==1) ++$new_num_points; }

    $new_header = $head_col[0]."|".$new_num_points."|".$head_col[2]."|".$head_col[3]."|".$head_col[4]."|".$head_col[5]."|";
    for($j=6;$j<$num_head_col;++$j) { $new_header = $new_header.$head_col[$j]."|"; }
    print "$new_header\n";

    for($j=0;$j<=$orig_num_points;++$j) {
      if($keep_flag[$j]==1) { print "$block[$j]\n"; }
    }
  }

  fclose($inf);

?>
