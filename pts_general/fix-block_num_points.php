#!/usr/bin/php -q
<?php

//--------------------------------------------------------------------------------------
//   If run with no command line args, print out usage statement and bail
//--------------------------------------------------------------------------------------

  $argc = count($argv);

  if($argc==1)
  {
    print "\n\nUsage:\n";
    print "  fix-block_num_points.php infile\n\n";

    print "Examples:\n";
    print "  ./fix-block_num_points.php sample_data/sample3.bad_num_points.pts\n\n";

    print "  Input file will not be a valid pts file, but it needs to be close.\n";
    print "  The num_blocks entry on line 1 should be correct.  The block header lines\n";
    print "  should have all the desired fields but the number of points per block in the\n";
    print "  second column can be bad and they will be fixed in the output.  The block\n";
    print "  indexes can be good or bad and will get fixed as a side-effect.  The bounding\n";
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

  $num_points = array();

  fgets($inf);   //--get past the first block header line
  $max_allowed_num_points = 10000000;
  for($i=0;$i<$num_blocks;++$i)
  {
    $num_points[$i] = 0;

    for($j=0;$j<$max_allowed_num_points;++$j)
    {
      if( ($line=fgets($inf) )===false)  break;
      $line = trim($line);
      if(strpos($line,'|')!==false)  break;
      else                           ++$num_points[$i];
    }
    if($j==$max_allowed_num_points) { print "Max number points per block exceeded.";  exit(0); }
  }

  rewind($inf);
  fgets($inf);   //--get past the first num_block line
  print "$num_blocks\n";

  for($i=0;$i<$num_blocks;++$i)
  {
    $line = trim(fgets($inf));

    $new_header = ReplaceBlockHeaderField($line,0,$i);
    $new_header = ReplaceBlockHeaderField($new_header,1,$num_points[$i]);
    print "$new_header\n";
    PrintJustPointsSingleBlock($inf,$num_points[$i]);
  }

  fclose($inf);

?>
