#!/usr/bin/php -q
<?php
  $argc = count($argv);

  if($argc==1)
  {
    print "\n\nUsage:\n";
    print "  list_shapefile_records.php input_file\n\n";
    print "Examples:\n";
    print "  ./list_shapefile_records.php sample_data/ocnms_py.shp\n\n";
    print "  Extracts info on the records contained in a binary 'shp' file.\n";
    print "  Output is captured via redirect.\n\n";

    exit(0);
  }

//--------------------------------------------------------------------------------------

  $shpfile = $argv[1];

  $inf = fopen($shpfile,"r");
  fseek($inf,24);   //----fixed file type code and 20 unused bytes/no info here

  $binary_str = fread($inf,12);
  $header = unpack("N1len/V1ver/V1type",$binary_str);

  $binary_str = fread($inf,32);
  $mbr = unpack("d1min_x/d1min_y/d1max_x/d1max_y",$binary_str);

  printf("File length: %d\n",2*$header['len']);
  printf("Version: %s\n",$header['ver']);
  printf("Content type: %d\n\n",$header['type']);

  printf("File Bounding Rect: %f,%f    %f,%f\n\n",$mbr['min_x'],$mbr['min_y'],$mbr['max_x'],$mbr['max_y']);

  fseek($inf,32,SEEK_CUR);  //---not in a simple shape - max & min values of Z (3D space) and M (user defineable 4th dim)

  $total_offset = 100;   //---header is a fixed 24+12+32+32 = 100 bytes long total

  print "List of all records after header\n";
  print "number(from 1) length(in bytes) type_code bounding_rect(wlon nlat elon slat) num_parts total_num_points\n\n";

  while($total_offset < 2*$header['len'])
  {
    $binary_str = fread($inf,8);
    $record_head = unpack("N1index/N1len",$binary_str);

    $binary_str = fread($inf,4);
    $record_type = unpack("V1type",$binary_str);

    $binary_str = fread($inf,32);
    $mbr = unpack("d1min_x/d1min_y/d1max_x/d1max_y",$binary_str);

    $binary_str = fread($inf,8);
    $record_info = unpack("V1num_parts/V1num_pts",$binary_str);

    printf("%d %d %d %f %f %f %f %d %d\n",$record_head['index'],2*$record_head['len'],$record_type['type'],$mbr['min_x'],$mbr['min_y'],$mbr['max_x'],$mbr['max_y'],$record_info['num_parts'],$record_info['num_pts']);

    fseek($inf,$record_info['num_parts']*4+$record_info['num_pts']*16,SEEK_CUR);
    $total_offset = $total_offset + 52 + $record_info['num_parts']*4 + $record_info['num_pts']*16;
  }

  fclose($inf);

?>
