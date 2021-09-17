#!/usr/bin/php -q
<?php

//--------------------------------------------------------------------------------------
//   If run with no command line args, print out usage statement and bail
//--------------------------------------------------------------------------------------

  $argc = count($argv);

  if($argc==1)
  {
    print "\n\nUsage:\n";
    print "  test_format_integrity.php input_file\n\n";

    print "Examples:\n";
    print "  ./test_format_integrity.php sample_data/sample1.pts \n\n";

    print "  Input a pts format file to test.  Checks for basic format conformity.  Reports a variety of errors\n";
    print "  along with some basic stats.  The type of errors found can limit the level of processeing that occurs.\n";
    print "  For instance, if the total number of blocks field in the first line is bad (blank, non-numeric, negative)\n";
    print "  that is reported and the script exits there.  Fixing that and rerunning would allow for later errors to\n";
    print "  be found.  The script reports when the format passes as well as fails, so it is clear what was checked.\n";
    print "  The script does not check the accuracy of the bounding box fields, only that they exist.  Low memory\n";
    print "  version for large files. Output is captured via redirect.\n\n";

    exit(0);
  }

  if($argc!=2) { print "\nFatal Error: Wrong number of command line arguments\n\n";  exit(0); }

//--------------------------------------------------------------------------------------
//   Read in single command line arg and run the directory listing and test result for failure
//--------------------------------------------------------------------------------------

  $infile = $argv[1];

  if(!file_exists($infile)) { print "\nInput file not found: $infile\n\n";  exit(0); }
  $inf = fopen($infile,'r');

  print "\n\nInformation for $infile\n\n";

  $raw_num_blocks = trim(fgets($inf));
  $num_blocks = intval($raw_num_blocks);

  print "Number of blocks = $raw_num_blocks\n";
  if(!is_numeric($raw_num_blocks)) { print "Fatal Error:  Number of blocks is not numeric.\n\n";  exit(0); }
  if($num_blocks!=$raw_num_blocks) { print "Fatal Error:  Number of blocks not an integer.\n\n";  exit(0); }
  if($num_blocks<1) { print "Fatal Error:  Number of blocks is integer less than 1.\n\n";  exit(0); }
  if($num_blocks>1000000) { print "Warning:  Number of blocks is very high.\n\n";  exit(0); }
  print "\n";

//--------------------------------------------------------------------------------------
//   Set up some basic arrays.
//--------------------------------------------------------------------------------------

  $pos_in_file = Array();
  $num_fields = Array();
  $header_index = Array();
  $num_points = Array();
  $wlon = Array();
  $nlat = Array();
  $elon = Array();
  $slat = Array();

//--------------------------------------------------------------------------------------
//  Step through file only looking for header lines.  Try to read the 6 required fields
//  and get a count of the optional ones.
//--------------------------------------------------------------------------------------

  $i = 1;
  $b = 0;
  while($line=fgets($inf))
  {
    $line = trim($line);

    if(strpos($line,'|')===false) { ++$i;  continue; }

    $col = explode('|',$line);

    $num_col = count($col);
    if($num_col<6) { ++$i;  print "Fatal Error:  Line $i is not a proper header line\n   $line\n\n";  exit(0); }
    $pos_in_file[$b] = $i;
    $num_fields[$b] = $num_col;
    $header_index[$b] = $col[0];
    $num_points[$b] = $col[1];
    $wlon[$b] = $col[2];
    $nlat[$b] = $col[3];
    $elon[$b] = $col[4];
    $slat[$b] = $col[5];

    ++$i;
    ++$b;
  }
  $num_lines = $i;

  $num_headers = count($pos_in_file);
  if($num_headers!=$num_blocks) { print "Fatal Error:  Number of header lines found ($num_headers) does not match number expected.\n\n";  exit(0); }
  print "Correct number of header lines found\n\n";

//--------------------------------------------------------------------------------------
//  Check that the block index and num of points per block were accurate
//--------------------------------------------------------------------------------------

  for($b=0;$b<$num_blocks;++$b) {
    if($header_index[$b]!=$b)  {  print "Fatal Error:  Incorrect block index.  Block $b has an index of $header_index[$b].\n\n";  exit(0);  }
  }

  for($b=0;$b<$num_blocks;++$b) {
    if($b<$num_blocks-1) { $header_gap = $pos_in_file[$b+1] - $pos_in_file[$b] - 1; }
    else { $header_gap = $num_lines - $pos_in_file[$b] - 1; }

    if($header_gap!=$num_points[$b]) { print "Fatal Error:  Block $b claims $num_points[$b] points but contains $header_gap.\n\n";  exit(0); }
  }

  print "Block header lines have correct indexes and numbers of points.\n";

//--------------------------------------------------------------------------------------
//   Check that the number of header fields is consistent.
//--------------------------------------------------------------------------------------

  $min_num_fields = $num_fields[0];
  $max_num_fields = $num_fields[0];
  for($b=1;$b<$num_blocks;++$b) {
    if($num_fields[$b]<$min_num_fields) { $min_num_fields = $num_fields[$b]; }
    if($num_fields[$b]>$max_num_fields) { $max_num_fields = $num_fields[$b]; }
  }

  $min_num_opt = $min_num_fields - 6;
  $max_num_opt = $max_num_fields - 6;

  if($min_num_opt<6) { print "Some headers do not contain all six required fields\n"; }

  if($min_num_opt==$max_num_opt) { print "Number of optional header fields is consistent and equals $min_num_opt.\n\n"; }
  else { print "Warning: Number of optional header fields is not consistent.  Range is $min_num_opt to $max_num_opt.\n\n"; }

//--------------------------------------------------------------------------------------
//   Print out a couple line totals
//--------------------------------------------------------------------------------------

  print "Total number of file lines = $num_lines\n";

  $num_geo_pts = $num_lines - $num_blocks - 1;
  print "Total number of geographic points = $num_geo_pts\n\n";
  fclose($inf);

?>
