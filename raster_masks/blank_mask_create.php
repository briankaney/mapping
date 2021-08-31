#!/usr/bin/php -q
<?php

//--------------------------------------------------------------------------------------
//   If run with no command line args, print out usage statement and bail
//--------------------------------------------------------------------------------------

  $argc = count($argv);

  if($argc==1)
  {
    print "\n\nUsage:\n";
    print "   blank_mask_create.php mask_file num_x num_y wlon nlat delta_lon delta_lat ll_scale\n\n";
    print "Examples:\n";
    print "   ./blank_mask_create.php ok-cherokee.100x100.blank.gz 100 100 -9550 3650 1 1 100\n";
    print "   ./blank_mask_create.php ok-cherokee.500x500.blank.gz 500 500 -95500 36500 2 2 1000\n\n";

    print "   This program creates a new raster mask file.  The first argument is the output filename.\n";
    print "   The next seven arguments correspond directly to the seven header fields in the file to\n";
    print "   be created.  All seven must be integer values (see the Notes.txt file for more information).\n";
    print "   The rest of the file is filled with zeros.\n\n";

    exit(0);
  }

//--------------------------------------------------------------------------------------
//   Read command line arguments.  No use of $argv after this point.
//--------------------------------------------------------------------------------------

  $mask     = $argv[1];
  $num_x    = $argv[2];
  $num_y    = $argv[3];
  $wlon     = $argv[4];
  $nlat     = $argv[5];
  $del_lat  = $argv[6];
  $del_lon  = $argv[7];
  $ll_scale = $argv[8];

//--------------------------------------------------------------------------------------
//   Open output file, write header and an array body full of zeroes.
//--------------------------------------------------------------------------------------

  $outf = gzopen($mask,"wb");

  $binary_str = pack("iiiiiii",$num_x,$num_y,$wlon,$nlat,$del_lat,$del_lon,$ll_scale);
  gzwrite($outf,$binary_str);

  for($i=0;$i<$num_y;++$i)
  {
    for($j=0;$j<$num_x;++$j)
    {
      $binary_str = pack("s",0);
      gzwrite($outf,$binary_str);
    }
  }

  gzclose($outf);

?>
