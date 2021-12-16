#!/usr/bin/php -q
<?php

//--------------------------------------------------------------------------------------
//   If run with no command line args, print out usage statement and bail
//--------------------------------------------------------------------------------------

  $argc = count($argv);

  if($argc==1)  {
    print "\n\nUsage:\n";
    print "  merge_two_files.php infile1 infile2\n\n";

    print "Examples:\n";
    print "  ./merge_two_files.php sample_data/sample1.pts sample_data/sample2.pts\n\n";

    print "  Merges two pts file into one in the order given on the command line.\n";
    print "  Low memory version for large files.  Output is captured via redirect.\n\n";

    exit(0);
    }

  if($argc!=3) { print "\nFatal Error: Wrong number of command line arguments\n\n";  exit(0); }

  include 'library_pts.php';

//--------------------------------------------------------------------------------------

  $infile1 = $argv[1];
  $infile2 = $argv[2];

  if(!file_exists($infile1)) { print "\nInput file not found: $infile1\n\n";  exit(0); }
  if(!file_exists($infile2)) { print "\nInput file not found: $infile2\n\n";  exit(0); }

  $inf1 = fopen($infile1,'r');
  $num_blocks1 = trim(fgets($inf1));

  $inf2 = fopen($infile2,'r');
  $num_blocks2 = trim(fgets($inf2));

  $total_blocks = $num_blocks1 + $num_blocks2;
  print "$total_blocks\n";

  PrintNBlocks($inf1,$num_blocks1);
  fclose($inf1);

  PrintNBlocksWithShiftedIndex($inf2,$num_blocks2,$num_blocks1);
  fclose($inf2);

?>
