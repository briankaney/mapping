#!/usr/bin/php -q
<?php

//--------------------------------------------------------------------------------------
//   If run with no command line args, print out usage statement and bail
//--------------------------------------------------------------------------------------

  $argc = count($argv);

  if($argc==1)
  {
    print "\n\nUsage:\n";
    print "  fix_num_blocks.php infile\n\n";

    print "Examples:\n";
    print "  ./fix_num_blocks.php sample_data/sample3.bad_num_blocks.pts\n\n";

    print "  Input file will not be a valid pts file.  The util does assume that the first\n";
    print "  line of the file is not a block header line and whatever is on this line will be\n";
    print "  be replaced with the new num_block count.  This util assumes that any line with a\n";
    print "  '|' character is a block header line and it just counts those.  And then rewrites\n";
    print "  the file with that count as the 'num_blocks' entry replacing the old line 1.  Other\n";
    print "  problems such as block indexes out of order, number of points per block being\n";
    print "  wrong, or empty blocks are passed on to the output without modification.  Low\n";
    print "  memory version (but will fail if number of file lines exceeds 100,000,000).\n";
    print "  Capture output via redirect.\n\n";

    exit(0);
  }

  include 'library_pts.php';

//--------------------------------------------------------------------------------------

  $infile = $argv[1];

  if(!file_exists($infile)) {  print "\nInput file not found: $infile\n\n";  exit(0);  }

  $inf = fopen($infile,'r');

  $num_blocks = 0;
  $max_allowed_lines = 100000000;
  for($i=0;$i<$max_allowed_lines;++$i)
  {
    if( ($line=fgets($inf) )===false)  break;
    $line = trim($line);

    if(strpos($line,'|')!==false)  ++$num_blocks;
  }

  if($i==$max_allowed_lines) { print "Max input file length exceeded.";  exit(0); }

  rewind($inf);
  fgets($inf);   //--skip over the old first line

  print "$num_blocks\n";

  $max_allowed_lines = 100000000;
  for($i=0;$i<$max_allowed_lines;++$i)
  {
    if( ($line=fgets($inf) )===false)  break;
    print "$line";
  }

  fclose($inf);

?>
