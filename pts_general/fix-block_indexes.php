#!/usr/bin/php -q
<?php

//--------------------------------------------------------------------------------------
//   If run with no command line args, print out usage statement and bail
//--------------------------------------------------------------------------------------

  $argc = count($argv);

  if($argc==1)
  {
    print "\n\nUsage:\n";
    print "  fix-block_indexes.php infile\n\n";

    print "Examples:\n";
    print "  ./fix-block_indexes.php sample_data/sample3.bad_indexes.pts\n\n";

    print "  Input file will not be a valid pts file, but it needs to be close.\n";
    print "  The num_blocks entry on line 1 should be correct.  The block header lines\n";
    print "  should have all the desired fields but the block indexes in the\n";
    print "  first column can be bad and they will be fixed in the output.  The num_points\n";
    print "  value in the second column of the block headers must be correct.  The bounding\n";
    print "  box and any optional fields are not used and just passed along to the output.\n";
    print "  Low memory and capture output via redirect.\n\n";

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
    $line = trim(fgets($inf));
    $col = explode('|',$line);
    $num_col = count($col)-1;

    print "$i|";
    for($j=1;$j<$num_col;++$j) { print "$col[$j]|"; }
    print "\n";

    PrintJustPointsSingleBlock($inf,$col[1]);
  }

  fclose($inf);

?>
