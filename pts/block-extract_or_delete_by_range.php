#!/usr/bin/php -q
<?php

//--------------------------------------------------------------------------------------
//   If run with no command line args, print out usage statement and bail
//--------------------------------------------------------------------------------------

  $argc = count($argv);

  if($argc==1)
  {
    print "\n\nUsage:\n";
    print "  block-extract_or_delete_by_range.php input_file action start_index end_index\n\n";

    print "Examples:\n";
    print "  ./block-extract_or_delete_by_range.php sample_data/sample1.pts extract 0 4\n";
    print "  ./block-extract_or_delete_by_range.php sample_data/sample1.pts delete 5 5\n\n";

    print "  A range of blocks in a pts file can be extracted into a new pts file or\n";
    print "  deleted from a pts file.  The indexes start at zero.  The extraction/deletion\n";
    print "  acts on both end points.  So an extraction with start and stop indexes of 5 and 6\n";
    print "  will make a new pts file with 2 blocks, namely the 6th and 7th blocks in the input\n";
    print "  file.  Low memory version for large files.  Output is captured via redirect.\n\n";

    exit(0);
  }

  if($argc!=5) { print "\nFatal Error: Wrong number of command line arguments\n\n";  exit(0); }

  include 'library_pts.php';

//--------------------------------------------------------------------------------------

  $infile = $argv[1];
  $action = $argv[2];
  $start = $argv[3];
  $end = $argv[4];

  if(!file_exists($infile)) { print "\nInput file not found: $infile\n\n";  exit(0); }
  $inf = fopen($infile,'r');
  $num_blocks = trim(fgets($inf));

  if($start>$end )        { print "\nInput error in index range.\n\n";  exit(0); }
  if($start<0 )           { print "\nInput error in index range.\n\n";  exit(0); }
  if($end>$num_blocks-1 ) { print "\nInput error in index range.\n\n";  exit(0); }

//--------------------------------------------------------------------------------------

  if($action=="extract")
  {
    $num_blocks_out = $end-$start+1;
    print "$num_blocks_out\n";

    SkipNBlocks($inf,$start);
    PrintNBlocksWithShiftedIndex($inf,$num_blocks_out,-1*$start);
  }

  if($action=="delete")
  {
    $num_blocks_removed = $end-$start+1;
    $num_blocks_out = $num_blocks-$num_blocks_removed;
    print "$num_blocks_out\n";

    PrintNBlocks($inf,$start);
    SkipNBlocks($inf,$num_blocks_removed);
    PrintNBlocksWithShiftedIndex($inf,$num_blocks-($end+1),-1*($end+1));
  }

  fclose($inf);

?>
