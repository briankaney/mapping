#!/usr/bin/php -q
<?php

//--------------------------------------------------------------------------------------
//   If run with no command line args, print out usage statement and bail
//--------------------------------------------------------------------------------------

  $argc = count($argv);

  if($argc==1)
  {
    print "\n\nUsage:\n";
    print "  convert_shape_dump_to_pts_step2.php infile\n\n";
    print "Examples:\n";
    print "  ./convert_shape_dump_to_pts_step2.php sample_data/ocnms_py.step1\n\n";
    print "  Second of two step process to convert the raw text dump of binary shp files\n";
    print "  to a pts format file.  Low memory version (meaning the input text file is not\n";
    print "  loaded all at once, but is opened with a file pointer and processed line by\n";
    print "  line).  Will fail if input exceeds 100,000,000 lines.  Output is\n";
    print "  captured via redirect.\n\n";

    exit(0);
  }

//--------------------------------------------------------------------------------------
//   Read in single command line arg and run the directory listing and test result for failure
//--------------------------------------------------------------------------------------

  $infile = $argv[1];

  if(!file_exists($infile)) { print "\nInput file not found: $infile\n\n";  exit(0); }

  $inf = fopen($infile,"r");

  $total_parts = 0;
  $max_allowed_blocks = 100000000;

  for($i=0;$i<$max_allowed_blocks;++$i)
  {
    if( ($line=fgets($inf) )===false)  break;
    $line = trim($line);

    if(strpos($line,'* ')!==false)
    {
      $col = explode(' ',$line);
      $total_parts = $total_parts + $col[1];
    }
  }

  if($i==$max_allowed_blocks) { print "Max input file length exceeded.";  exit(0); }

  print "$total_parts\n";

//--------------------------------------------------------------------------------------

  rewind($inf);

  $n=0;
  $max_allowed_blocks = 100000000;
  for($i=0;$i<$max_allowed_blocks;++$i)
  {
    if( ($line=fgets($inf) )===false) { break; }
    $line = trim($line);

    if(strpos($line,'* ')!==false)
    {
      $col = explode(' ',$line);
      $num_parts = $col[1];
      $total_points = $col[2];

      $line = trim(fgets($inf));
      $part_start = explode(' ',$line);
      if($part_start[0]!=0) { print "Fatal error: first part index not zero\n\n";  exit(0); }
      if($num_parts!=count($part_start)) { print "Fatal error: num parts don't match start index list\n\n";  exit(0); }
      $num_points_per_part = array();
      for($j=0;$j<$num_parts-1;++$j) { $num_points_per_part[$j] = $part_start[$j+1]-$part_start[$j]; }
      $num_points_per_part[$num_parts-1] = $total_points - $part_start[$num_parts-1];

      for($j=0;$j<$num_parts;++$j)
      {
        $str =  $n."|".$num_points_per_part[$j]."|180|-90|-180|90|";
        print "$str\n";
        ++$n;
        for($k=0;$k<$num_points_per_part[$j];++$k)
        {
          $line = trim(fgets($inf));
          print "$line\n";
        }    
      }
    }
  }

  if($i==$max_allowed_blocks) { print "Max input file length exceeded.";  exit(0); }

  fclose($inf);


/*

//---------------------------
* 2 30
0 19 
-171.040486453 -11.082452138
-171.039398193 -11.083986282
//---------------------------

*/


