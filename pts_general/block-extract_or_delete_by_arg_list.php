#!/usr/bin/php -q
<?php

//--------------------------------------------------------------------------------------
//   If run with no command line args, print out usage statement and bail
//--------------------------------------------------------------------------------------

  $argc = count($argv);

  if($argc==1)
  {
    print "\n\nUsage:\n";
    print "  block-extract_or_delete_by_arg_list.php input_file action index1 index2 index3 ...\n\n";

    print "Examples:\n";
    print "  ./block-extract_or_delete_by_arg_list.php sample_data/sample1.pts extract 1 2 5\n";
    print "  ./block-extract_or_delete_by_arg_list.php sample_data/sample1.pts delete 0 4 5 7\n\n";

    print "  A list of blocks in a pts file can be extracted into a new pts file or deleted\n";
    print "  from a pts file.  The indexe values start with zero.  But need not appear in the\n";
    print "  arg list in any particular order.  Low memory version for large files.\n";
    print "  Output is captured via redirect.\n\n";

    exit(0);
  }

  include 'library_pts.php';

//--------------------------------------------------------------------------------------

  $infile = $argv[1];
  $action = $argv[2];

  $index = array();

  $num_indexes = $argc - 3;
  for($i=0;$i<$num_indexes;++$i) { $index[$i] = $argv[$i+3]; }

  if(!file_exists($infile)) { print "\nInput file not found: $infile\n\n";  exit(0); }
  $inf = fopen($infile,'r');
  $num_blocks = trim(fgets($inf));

//--------------------------------------------------------------------------------------

  $keep_flag = array();

  if($action=="extract")
  {
    $num_blocks_out = $num_indexes;
    for($i=0;$i<$num_blocks;++$i) { $keep_flag[$i]=0; }
    for($i=0;$i<$num_indexes;++$i) { $keep_flag[$index[$i]]=1; }
  }
  if($action=="delete")
  {
    $num_blocks_out = $num_blocks - $num_indexes;
    for($i=0;$i<$num_blocks;++$i) { $keep_flag[$i]=1; }
    for($i=0;$i<$num_indexes;++$i) { $keep_flag[$index[$i]]=0; }
  }

  print "$num_blocks_out\n";

  $shift = 0;
  for($i=0;$i<$num_blocks;++$i)
  {
    if($keep_flag[$i]==0) {
      SkipNBlocks($inf,1);
      --$shift;
    }
    if($keep_flag[$i]==1) {
      PrintNBlocksWithShiftedIndex($inf,1,$shift);
    }
  }

  fclose($inf);

?>
