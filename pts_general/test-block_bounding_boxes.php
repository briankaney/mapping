#!/usr/bin/php -q
<?php

//--------------------------------------------------------------------------------------
//   If run with no command line args, print out usage statement and bail
//--------------------------------------------------------------------------------------

  $argc = count($argv);

  if($argc==1)
  {
    print "\n\nUsage:\n";
    print "  test-block_bounding_boxes.php infile\n\n";

    print "Examples:\n";
    print "  ./test-block_bounding_boxes.php sample_data/sample3.bad_bounding_boxes.pts\n\n";

    print "  Input file needs to be a valid pts file, except some of the bounding boxes\n";
    print "  could be wrong.  Very similar to the 'fix-block_bounding_boxes.php' tool\n";
    print "  except that one repairs bad bounding box entries, whereas this one just\n";
    print "  reports on whether errors or exist or not.  Low memory version.\n";
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

  for($i=0;$i<$num_blocks;++$i)
  {
    $block = StoreBlock($inf);
    $bb = FindBoundingBoxOfBlock($block);

    $col = explode('|',$block[0]);

    if($col[2]!=$bb[0] || $col[3]!=$bb[1] || $col[4]!=$bb[2] || $col[5]!=$bb[3])
    {
      $str = "Error Block ".$i.": ".$bb[0]."/".$bb[1]."/".$bb[2]."/".$bb[3]." vs ".$col[2]."/".$col[3]."/".$col[4]."/".$col[5];
      print "$str\n";
    }
  }

  fclose($inf);

?>
