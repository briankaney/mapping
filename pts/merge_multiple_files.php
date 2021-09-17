#!/usr/bin/php -q
<?php

//--------------------------------------------------------------------------------------
//   If run with no command line args, print out usage statement and bail
//--------------------------------------------------------------------------------------

  $argc = count($argv);

  if($argc==1)
  {
    print "\n\nUsage:\n";
    print "  merge_multiple_files.php infile1 infile2 infile3 ... infile8 outfile\n\n";

    print "Examples:\n";
    print "  ./merge_multiple_files.php sample_data/sample1.pts sample_data/sample2.pts sample_data/sample4.pts test.pts\n\n";

    print "  Merges multiple pts files into one file in the order given on the command line.\n";
    print "  System calls made to 'merge_files.php'.  Some temp files will be written and\n";
    print "  deleted in the current directory with the name pattern './temp-merge-work.*'.\n";
    print "  Existing files with that name will be stepped on.  Output is NOT captured via\n";
    print "  redirect but is last argument supplied by user.\n\n";

    exit(0);
  }

//--------------------------------------------------------------------------------------
//   Read command input file names from args and test for their presence
//--------------------------------------------------------------------------------------

  $infiles = array();

  for($i=1;$i<$argc-1;++$i)
  {
    $infiles[$i-1] = $argv[$i];
    if(!file_exists($argv[$i])) { print "\nInput file not found: $argv[$i]\n\n";  exit(0); }
  }

  $outfile = $argv[$argc-1];
  $num_files = count($infiles);

//--------------------------------------------------------------------------------------
//   Loop through file mergers.  Print statements provide some progress reporting   
//--------------------------------------------------------------------------------------

  $command = "cp ".$infiles[0]." temp-merge-work.next";
  print "\n\n$command\n";
  system($command);

  for($i=1;$i<$num_files;++$i)
  {
    $command = "./merge_two_files.php temp-merge-work.next ".$infiles[$i]." > temp-merge-work.tem";
    print "$command\n";
    system($command);
    $command = "mv temp-merge-work.tem temp-merge-work.next";
    print "$command\n";
    system($command);
  }

  print "mv temp-merge-work.next $outfile\n\n";
  system("mv temp-merge-work.next $outfile");

?>
