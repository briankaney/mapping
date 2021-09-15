#!/usr/bin/php -q
<?php

//--------------------------------------------------------------------------------------
//   If run with no command line args, print out usage statement and bail
//--------------------------------------------------------------------------------------

  $argc = count($argv);

  if($argc==1)
  {
    print "\n\nUsage:\n";
    print "   output_mask_info.php input_file\n\n";
    print "Examples:\n";
    print "   ./output_mask_info.php sample_data/ok-adair-cherokee.751x501.mask.gz\n\n";

    print "   Given a mask file this utility outputs some information about the file.  This starts\n";
    print "   with an output of the dimensions, resolution and lat/long bounds of the mask.  The\n";
    print "   Utility then counts the number of occurrences of each flag value that is used in the\n";
    print "   file (the number of possible flags is capped at 32,766).  The utility then outputs a\n";
    print "   list of the counts of the flags used.\n\n";

    exit(0);
  }

  include "library_mask.php";

//--------------------------------------------------------------------------------------
//   Read command line arguments.  No use of $argv after this point.  Test for existence
//   of input file.
//--------------------------------------------------------------------------------------

  $mask = $argv[1];

  if(!file_exists($mask)) { print "Error: $mask file not found\n\n";  exit(0); }

//--------------------------------------------------------------------------------------
//   Set up a fixed length array of bin_counts and initialize to zeros.
//--------------------------------------------------------------------------------------

  $bin_count = Array();
  for($i=0;$i<32767;++$i) { $bin_count[$i] = 0; }

//--------------------------------------------------------------------------------------
//   Read input file and increment the bin counts.
//--------------------------------------------------------------------------------------

  $inf = gzopen($mask,"rb");

  $header = ReadMaskHeader($inf);

  for($i=0;$i<$header['num_y'];++$i)
  {
    $binary_str = gzread($inf,$header['num_x']*2);
    $format = "s".$header['num_x'];
    $values = unpack($format,$binary_str);

    for($j=1;$j<=$header['num_x'];++$j)
    {
      if($values[$j]<32767) { ++$bin_count[$values[$j]]; }
    }
  }

  gzclose($inf);

//--------------------------------------------------------------------------------------
//   Print out header and bin counts.
//--------------------------------------------------------------------------------------

  print "\n";
  $str = "Dimensions: ".$header['num_x']."x".$header['num_y'];
  print "$str\n";
  $str = "Bounding Box: ".$header['wlon'].",".$header['nlat'].",".($header['wlon']+($header['num_x']-1)*$header['del_lon']).
         ",".($header['nlat']-($header['num_y']-1)*$header['del_lat']);
  print "$str\n\n";

  $n=0;
  for($i=0;$i<32767;++$i)
  {
    if($bin_count[$i]>0)
    {
      ++$n;
      printf("%-3d  Flag: %-3d   Count: %-8d\n",$n,$i,$bin_count[$i]);
    }
  }
  print "\n";

?>
