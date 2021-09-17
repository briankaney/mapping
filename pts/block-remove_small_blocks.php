#!/usr/bin/php -q
<?php

//--------------------------------------------------------------------------------------
//   If run with no command line args, print out usage statement and bail
//--------------------------------------------------------------------------------------

  $argc = count($argv);

  if($argc==1)
  {
    print "\n\nUsage:\n";
    print "  block-remove_small_blocks.php infile mode\n\n";

    print "Examples:\n";
    print "  ./block-remove_small_blocks.php sample_data/sample3.bad_empty_blocks.pts 0\n";
    print "  ./block-remove_small_blocks.php sample_data/sample3.bad_empty_blocks.pts 1\n";
    print "  ./block-remove_small_blocks.php sample_data/sample3.bad_empty_blocks.pts 2\n";
    print "  ./block-remove_small_blocks.php sample_data/sample3.bad_empty_blocks.pts all\n\n";

    print "  Input file does need to be a valid pts file.  So all the counts have to be correct.\n";
    print "  But if any blocks have just a single point then those will be removed from the output\n";
    print "  The overall num_blocks count and all the block indexes will be adjusted accordingly.\n";
    print "  The bounding box fields just get copied and need not be set.  Low memory version.\n";
    print "  Capture output via redirect.\n\n";

    exit(0);
  }

  if($argc!=3) { print "\nFatal Error: Wrong number of command line arguments\n\n";  exit(0); }

  include 'library_pts.php';

//--------------------------------------------------------------------------------------

  $infile = $argv[1];
  $mode   = $argv[2];

  if(!file_exists($infile)) { print "\nInput file not found: $infile\n\n";  exit(0); }

  $inf = fopen($infile,'r');
  $num_blocks = trim(fgets($inf));

  $new_num_blocks = $num_blocks;
  for($i=0;$i<$num_blocks;++$i)
  {
    $line = trim(fgets($inf));
    $col = explode('|',$line);
    if(($mode==0 || $mode=="all") && $col[1]==0) { --$new_num_blocks; }
    if(($mode==1 || $mode=="all") && $col[1]==1) { --$new_num_blocks; }
    if(($mode==2 || $mode=="all") && $col[1]==2) { --$new_num_blocks; }
    for($n=0;$n<$col[1];++$n) { fgets($inf); }
  }

  rewind($inf);
  fgets($inf);   //--skip over the old first line

  print "$new_num_blocks\n";

  $j=0;
  for($i=0;$i<$num_blocks;++$i)
  {
    $line = trim(fgets($inf));
    $col = explode('|',$line);
    if(($mode==0 || $mode=="all") && $col[1]==0) { continue; }
    if(($mode==1 || $mode=="all") && $col[1]==1) { fgets($inf);  continue; }
    if(($mode==2 || $mode=="all") && $col[1]==2) { fgets($inf);  fgets($inf);  continue; }

    $new_header = ReplaceBlockHeaderField($line,0,$j);
    print "$new_header\n";
    PrintJustPointsSingleBlock($inf,$col[1]);
    ++$j;
  }

  fclose($inf);

?>
