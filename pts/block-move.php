#!/usr/bin/php -q
<?php

//--------------------------------------------------------------------------------------
//   If run with no command line args, print out usage statement and bail
//--------------------------------------------------------------------------------------

  $argc = count($argv);

  if($argc==1)
  {
    print "\n\nUsage:\n";
    print "  block-move.php input_file index shift\n\n";

    print "Examples:\n";
    print "  ./block-move.php sample_data/sample1.pts 2 4\n";
    print "  ./block-move.php sample_data/sample1.pts 4 -1\n\n";

    print "  A pts file is given and a single block is shifted to a new location.\n";
    print "  The index arg is the original block index of the block to be moved.\n";
    print "  The shift index tells how far it will be moved relative to its start.\n";
    print "  So a shift of -1 will swap a block with the one prior to it.  A shift of +1\n";
    print "  will swap a block with the one after it.  Low memory version for large files.\n";
    print "  Output is captured via redirect.\n\n";

    exit(0);
  }

  if($argc!=4) { print "\nFatal Error: Wrong number of command line arguments\n\n";  exit(0); }

  include 'library_pts.php';

//--------------------------------------------------------------------------------------

  $infile = $argv[1];
  $index = $argv[2];
  $shift = $argv[3];

  if(!file_exists($infile)) { print "\nInput file not found: $infile\n\n";  exit(0); }
  $inf = fopen($infile,'r');
  $num_blocks = trim(fgets($inf));

  if($shift==0 ) { print "\nNo change - no action taken.\n\n";  exit(0); }
  if(($shift+$index)<0 || ($shift+$index)>$num_blocks-1) { print "\nShift would move block outside allowed range.\n\n";  exit(0); }

//--------------------------------------------------------------------------------------

  print "$num_blocks\n";

  if($shift>0)
  {
    PrintNBlocks($inf,$index);
    $block = StoreBlock($inf);
    $block[0] = ReplaceBlockHeaderField($block[0],0,$index+$shift);
    PrintNBlocksWithShiftedIndex($inf,$shift,-1);
    PrintBlockFromMemory($block);
    PrintNBlocks($inf,$num_blocks-$index-$shift-1);
  }

  if($shift<0)
  {
    PrintNBlocks($inf,$index+$shift);
    SkipNBlocks($inf,-1*$shift);
    $block = StoreBlock($inf);
    $block[0] = ReplaceBlockHeaderField($block[0],0,$index+$shift);
    RewindToBlock($inf,$index+$shift);
    PrintBlockFromMemory($block);
    PrintNBlocksWithShiftedIndex($inf,-1*$shift,1);
    SkipNBlocks($inf,1);
    PrintNBlocks($inf,$num_blocks-$index-1);
  }

  fclose($inf);

?>
