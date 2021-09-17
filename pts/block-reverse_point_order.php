#!/usr/bin/php -q
<?php

//--------------------------------------------------------------------------------------
//   If run with no command line args, print out usage statement and bail
//--------------------------------------------------------------------------------------

  $argc = count($argv);

  if($argc==1)
  {
    print "\n\nUsage:\n";
    print "  block-reverse_point_order..php input_file index\n\n";

    print "Examples:\n";
    print "  ./block-reverse_point_order.php sample_data/sample1.pts 9\n\n";

    print "  Just reverses the order of the points in the one block specified by the given index.\n";
    print "  Low memory version for large files.  Output is captured via redirect.\n\n";

    exit(0);
  }

  if($argc!=3) { print "\nFatal Error: Wrong number of command line arguments\n\n";  exit(0); }

  include 'library_pts.php';

//--------------------------------------------------------------------------------------

  $infile = $argv[1];
  $index = $argv[2];

  if(!file_exists($infile)) { print "\nInput file not found: $infile\n\n";  exit(0); }
  $inf = fopen($infile,'r');
  $num_blocks = trim(fgets($inf));

  print "$num_blocks\n";

  PrintNBlocks($inf,$index);

  $block = StoreBlock($inf);
  print "$block[0]\n";
  $col = explode('|',$block[0]);
  for($i=0;$i<$col[1];++$i)
  {
    $j = $col[1]-1-$i+1;
    print "$block[$j]\n";
  }

  PrintNBlocks($inf,$num_blocks-$index-1);

  fclose($inf);

?>
