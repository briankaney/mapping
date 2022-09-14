#!/usr/bin/php -q
<?php

//--------------------------------------------------------------------------------------
//   If run with no command line args, print out usage statement and bail
//--------------------------------------------------------------------------------------

  $argc = count($argv);

  if($argc==1)
  {
    print "\n\nUsage:\n";
    print "  replace_header_lines_from_file.php pts_file header_lines\n\n";

    print "Examples:\n";
    print "  ./replace_header_lines_from_file.php sample_data/KS.FourSquareCounties.pts sample_data/new_headers.txt\n\n";

    print "  Input a pts file and replace all the block headers with the lines from another specified file.\n";
    print "  Care should be take: The replacement lines should contain the correct standard 6 fields as the\n";
    print "  original lines will be completely overwriten.  The header line file should have the correct number\n";
    print "  of entries.  This utility is useful for editing just the header lines.  A grep '|' file > out.txt\n";
    print "  can be used to extract just the header lines to a file, then they can be edited in that compact\n";
    print "  form and returned to source pts file.\n\n";

    exit(0);
  }

  if($argc!=3) { print "\nFatal Error: Wrong number of command line arguments\n\n";  exit(0); }

//--------------------------------------------------------------------------------------
//   Read in single command line arg and run the directory listing and test result for failure
//--------------------------------------------------------------------------------------

  $ptsfile = $argv[1];
  $headfile = $argv[2];

  if(!file_exists($ptsfile))  { print "\nInput pts file not found: $ptsfile\n\n";  exit(0); }
  if(!file_exists($headfile)) { print "\nInput header line file not found: $headfile\n\n";  exit(0); }

//--------------------------------------------------------------------------------------
//  Open both files and read the full contents of the header file.     
//--------------------------------------------------------------------------------------

  $headers = file("$headfile",FILE_IGNORE_NEW_LINES);

  $inf = fopen($ptsfile,'r');

  $num_blocks = intval(trim(fgets($inf)));
  print "$num_blocks\n";

  $i = 0;
  while($line=fgets($inf))
  {
    $line = trim($line);

    if(strpos($line,'|')===false) { print "$line\n";  continue; }

    $str = $headers[$i];
    print "$str\n";
    ++$i;
  }

  fclose($inf);

?>
