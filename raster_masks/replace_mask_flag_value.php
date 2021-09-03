#!/usr/bin/php -q
<?php

//--------------------------------------------------------------------------------------
//   If run with no command line args, print out usage statement and bail
//--------------------------------------------------------------------------------------

  $argc = count($argv);

  if($argc==1)
  {
    print "\n\nUsage:\n";
    print "   replace_mask_flag_value.php input_mask old_flag_value new_flag_value output_file\n\n";
    print "Examples:\n";
    print "   ./replace_mask_flag_value.php sample_data/ok-adair-cherokee.750x500.mask.gz 2 1 ok-merged.750x500.mask.gz\n\n";

    print "   Takes an input mask file and replaces one of the flags (if present) with another value.  The\n";
    print "   four arguments are straight forward: original mask file name, old flag value to be replaced,\n";
    print "   the new flag value to replace it with and the new output mask file name.  Uses would include\n";
    print "   merging regions by making their flags equal.  Squeezing out unused flag values.  Preparing a\n";
    print "   mask for a merger with another mask.\n\n";

    exit(0);
  }

  include "library_mask.php";

//--------------------------------------------------------------------------------------
//   Read command line arguments.  No use of $argv after this point.
//--------------------------------------------------------------------------------------

  $mask = $argv[1];
  $old_flag = $argv[2];
  $new_flag = $argv[3];
  $output = $argv[4];

//--------------------------------------------------------------------------------------
//   Test for input mask file.  Open input and output mask files and copy header over.
//--------------------------------------------------------------------------------------

  if(!file_exists($mask)) {  print "\nMask file not found: $mask\n\n";  exit(0);  }

  $inf = gzopen($mask,"rb");
  $header = ReadMaskHeader($inf);

  $outf = gzopen($output,"wb");
  CopyMaskHeader($outf,$header);

//--------------------------------------------------------------------------------------
//   Loop over all the mask points and test for the old flag value.  In this low 
//   memory version, the input is read, tested and output, before moving on to the 
//   next point.  So very little of the mask file is in memory at one time.
//--------------------------------------------------------------------------------------

  for($y=0;$y<$header['num_y'];++$y)  //--The loop over latitudes
  {
    for($x=0;$x<$header['num_x'];++$x)  //--The loop over longitudes
    {
      $binary_str = gzread($inf,2);
      $value = unpack("s1",$binary_str);

      if($value['1'] == $old_flag) { $binary_str = pack("s1",$new_flag); }
      gzwrite($outf,$binary_str);
    }
  }

//--------------------------------------------------------------------------------------
//    Close both input and output mask files.
//--------------------------------------------------------------------------------------

  gzclose($inf);
  gzclose($outf);

?>
