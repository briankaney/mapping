#!/usr/bin/php -q
<?php

//--------------------------------------------------------------------------------------
//   If run with no command line args, print out usage statement and bail
//--------------------------------------------------------------------------------------

  $argc = count($argv);

  if($argc==1)
  {
    print "\n\nUsage:\n";
    print "  block-extract_inside_bb.php input_file mode wlon nlat elon slat\n\n";

    print "Examples:\n";
    print "  ./block-extract_inside_bb.php sample_data/KS.FourSquareCounties.pts full -97 39 -95.3 37.6\n";
    print "  ./block-extract_inside_bb.php sample_data/KS.FourSquareCounties.pts partial -97 39 -95.6 37.6\n\n";

    print "A set of blocks in a pts file can be extracted to a new pts file via a lat/long\n";
    print "bounding box.  There are two modes of operation determined by the second arg.\n";
    print "Using 'full' will only extract those blocks that are wholly inside the bounding box given.\n";
    print "Whereas 'partial' will extract all blocks that are even partially inside (or intersect\n";
    print "with) the bounding box given.  The 'full' file is smaller and will be a subset of the\n";
    print "'partial' option file.\n\n";
    print "Low memory version for huge files (meaning the input is not read all at once, but read\n";
    print "using a file pointer and processed line by line).  Output is captured via redirect.\n\n";

    exit(0);
  }

  if($argc!=7) { print "\nFatal Error: Wrong number of command line arguments\n\n";  exit(0); }

  include 'library_pts.php';

//--------------------------------------------------------------------------------------

  $infile = $argv[1];
  $mode = $argv[2];
  $wlon = $argv[3];
  $nlat = $argv[4];
  $elon = $argv[5];
  $slat = $argv[6];

  if(!file_exists($infile)) { print "\nInput file not found: $infile\n\n";  exit(0); }
  $inf = fopen($infile,'r');
  $num_blocks = trim(fgets($inf));

//--------------------------------------------------------------------------------------

  $keep_block = array();
  for($i=0;$i<$num_blocks;++$i) { $keep_block[$i] = 0; }

  for($i=0;$i<$num_blocks;++$i)
  {
    $block = StoreBlock($inf);
    $bb = FindBoundingBoxOfBlock($block);

    if($mode=="full") {
      if($bb[0]>=$wlon && $bb[1]<=$nlat && $bb[2]<=$elon && $bb[3]>=$slat) { $keep_block[$i] = 1; }
    }
    if($mode=="partial") {
      if(!($bb[0]>$elon || $bb[1]<$slat || $bb[2]<$wlon || $bb[3]>$nlat)) { $keep_block[$i] = 1; }
    }
  }

  rewind($inf);
  fgets($inf);
 
  $num_blocks_kept = 0;
  for($i=0;$i<$num_blocks;++$i) {
    if($keep_block[$i]==1) { ++$num_blocks_kept; }
  }

  print "$num_blocks_kept\n";

  $shift = 0;
  for($i=0;$i<$num_blocks;++$i)
  {
    if($keep_block[$i]==0) {
      SkipNBlocks($inf,1);
      --$shift;
    }
    if($keep_block[$i]==1) {
      PrintNBlocksWithShiftedIndex($inf,1,$shift);
    }
  }

  fclose($inf);

?>
