#!/usr/bin/php -q
<?php

//--------------------------------------------------------------------------------------
//   If run with no command line args, print out usage statement and bail
//--------------------------------------------------------------------------------------

  $argc = count($argv);

  if($argc==1)
  {
    print "\n\nUsage:\n";
    print "  cross_ref_db_with_list.php db_file list_file\n\n";
    print "Examples:\n";
    print "  ./cross_ref_db_with_list.php sample_data/province.db.txt sample_data/province.list_output.txt \n\n";
    print "  Does a cross referencing between the shapefile database and the list dump from report util.\n";
    print "  The shapefile contains records, which can then contain parts.  The 'pts' format does not\n";
    print "  have this two tier system.  It will just contain a bunch of point blocks.  This output\n";
    print "  matches values or ranges of pts blocks with ID's in the database.\n\n";
    print "  NOTE:  The columns in the database files are not uniform across all maps.  And different outputs\n";
    print "  may be desired (numerical order of some database column ID for sorting).  The easiest approach\n";
    print "  is to just hand edit this util for the desired result.  There is a single line of code where\n";
    print "  'print_str' is defined that should be modified as needed.  Output is captured via redirect.\n\n";

    exit(0);
  }

//--------------------------------------------------------------------------------------
//   Read in single command line arg and run the directory listing and test result for failure
//--------------------------------------------------------------------------------------

  $dbfile = $argv[1];
  $listfile = $argv[2];

  if(!file_exists($dbfile))   { print "\nInput db file not found: $dbfile\n\n";  exit(0); }
  if(!file_exists($listfile)) { print "\nInput list file not found: $listfile\n\n";  exit(0); }

  $db = file($dbfile,FILE_IGNORE_NEW_LINES);
  $num_db = count($db);

  $list = file($listfile,FILE_IGNORE_NEW_LINES);
  $num_list = count($list);

/*--------------------------------------------------------------------------------------

   Sample database file:

//------------------
CODE|NAME
CA01|Alberta
CA11|Saskatchewan
CA03|Manitoba
CA05|Newfoundland & Labrador
CA09|Prince Edward Island
...
//------------------

   Sample list output file:

//------------------
File length: 668960
Version: 1000
Content type: 5

File Bounding Rect: -141.002859,41.971497    -52.543746,83.148086

List of all records after header
number(from 1) length(in bytes) type_code bounding_rect(wlon nlat elon slat) num_parts total_num_points

1 7872 5 -120.000000 48.993095 -109.983243 60.000000 1 489
2 1616 5 -110.000000 49.000000 -101.349373 60.000000 1 98
3 3476 5 -102.000000 48.996243 -88.960880 60.008697 2 214
4 108508 5 -67.804527 46.614659 -52.543746 60.411519 76 6760
5 1568 5 -64.463408 45.973942 -62.066603 47.049363 1 95
//------------------

   The first file has a one line header and the list file has a nine line header.  After that the number of lines 
must match exactly.  Check this requirement first.  Then parse through the blocks and match database NAME's to 
ranges of output polylines according to the list file.

--------------------------------------------------------------------------------------*/

  if(($num_db+8)!=$num_list) { print "\nFatal error: mismatch in number of record blocks in two files.\n\n";  exit(0); }

  $num_blocks = $num_db-1;

  $start_index = 0;
  $end_index = -1;
  for($i=0;$i<$num_blocks;++$i)
  {
    $db_col = explode('|',$db[$i+1]);
    $list_col = explode(' ',$list[$i+9]);

    $start_index = $end_index+1;
    $end_index = $start_index+$list_col[7]-1;

    if($end_index==$start_index)  $index_str = $start_index;
    else                          $index_str = $start_index."-".$end_index;

    $print_str = $index_str."|".$db_col[1]."|";   //---used for province
//    $print_str = $index_str."|".$db_col[2]."|";    //---used for rfc's
//    $print_str = $index_str."|".$db_col[0]."|".$db_col[2]."|";    //---used for counties
//    $print_str = $db_col[0]."|".$index_str."|".$db_col[2]."|";    //---also used for counties
//    $print_str = $db_col[0]."|";    //---also used for counties

    print "$print_str\n";
  }

?>
