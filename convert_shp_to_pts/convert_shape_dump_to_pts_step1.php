#!/usr/bin/php -q
<?php

//--------------------------------------------------------------------------------------
//   If run with no command line args, print out usage statement and bail
//--------------------------------------------------------------------------------------

  $argc = count($argv);

  if($argc==1)
  {
    print "\n\nUsage:\n";
    print "  convert_shape_dump_to_pts_step1.php infile\n\n";
    print "Examples:\n";
    print "  ./convert_shape_dump_to_pts_step1.php sample_data/ocnms_py.raw_dump\n\n";
    print "  First of two step process to convert the raw text dump of binary shp files\n";
    print "  to a pts format file.  Low memory version (meaning the input text file is not\n";
    print "  loaded all at once, but is opened with a file pointer and processed line by\n";
    print "  line).  Will fail if input exceeds 100,000,000 lines.  Output is captured\n";
    print "  via redirect.\n\n";

    exit(0);
  }

//--------------------------------------------------------------------------------------
//   Read in single command line arg and run the directory listing and test result for failure
//--------------------------------------------------------------------------------------

  $infile = $argv[1];

  if(!file_exists($infile)) { print "\nInput file not found: $infile\n\n";  exit(0); }

  $inf = fopen($infile,"r");

  $max_allowed_lines = 100000000;
  for($i=0;$i<$max_allowed_lines;++$i)
  {
    if( ($line=fgets($inf) )===false) { break; }
    $line = trim($line);

    if(strpos($line,'* ')!==false)
    {
      $next_line = trim(fgets($inf));
      print "$line\n";
      print "$next_line\n";
      continue;
    }

    if(strpos($line,'Array')!==false) { continue; }
    if(strpos($line,'(')!==false)     { continue; }
    if(strpos($line,')')!==false)     { continue; }

    if(strpos($line,'pts')!==false)
    {
      $next_line = trim(fgets($inf));
      $col1 = preg_split('/ +/',$line);
      $col2 = preg_split('/ +/',$next_line);

      print "$col1[2] $col2[2]\n";
      continue;
    }

    if(strpos($line,'x')!==false)
    {
      $next_line = trim(fgets($inf));
      $col1 = preg_split('/ +/',$line);
      $col2 = preg_split('/ +/',$next_line);

      print "* 1\n";
      print "$col1[3] $col2[3]\n";
      continue;
    }
  }

  if($i==$max_allowed_lines) { print "Max input file length exceeded.";  exit(0); }

  fclose($inf);

/*

//----------------------
* 2 30
0 19 
Array
(
    [pts1] => -105.356697
    [pts2] => 38.364201
    [pts3] => -105.358398
    [pts4] => 38.362598
    [pts5] => -105.3601


    [pts59] => -171.048248291
    [pts60] => -11.084536552
)
* 6 5080
0 942 2612 2793 2862 2879 
Array
(
    [pts1] => -169.612749
    [pts2] => -14.155326


    [pts10] => 38.284801
    [pts11] => -95.883202
    [pts12] => 38.284801
)

  or

Array
(
    [x] => -111.84120914468
    [y] => 33.218529228868
)
//----------------------
*/

?>
