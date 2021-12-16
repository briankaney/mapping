#!/usr/bin/php -q
<?php
  $argc = count($argv);

  if($argc==1)
  {
    print "\n\nUsage:\n";
    print "  raw_dump_shapefile_data.php input_file\n\n";
    print "Examples:\n";
    print "  ./raw_dump_shapefile_data.php sample_data/ocnms_py.shp\n\n";
    print "  Extracts the lat/long data in a binary 'shp' file and dumps it out as plain text.\n";
    print "  Output is captured via redirect.  Format is awkward and calls for more processing.\n\n";

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

  fseek($inf,32,SEEK_CUR);  //---max and min values of Z (3D space) and M (user defineable 4th dimension)

  $total_offset = 100;    //---header is a fixed 24+12+32+32 = 100 bytes long total

  if($header['type']==3 || $header['type']==5)
  {
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

      printf("* %d %d\n",$record_info['num_parts'],$record_info['num_pts']);

      for($i=0;$i<$record_info['num_parts'];++$i)
      {
        $binary_str = fread($inf,4);
        $part_info = unpack("V1part_index",$binary_str);
        printf("%d ",$part_info['part_index']);
      }
      print "\n";

      $binary_str = fread($inf,$record_info['num_pts']*16);
      $format = "d".(2*$record_info['num_pts'])."pts";
      $pts = unpack($format,$binary_str);
      print_r($pts);

      $total_offset = $total_offset + 52 + $record_info['num_parts']*4 + $record_info['num_pts']*16;
    }
  }

  if($header['type']==1)
  {
    while($total_offset < 2*$header['len'])
    {
      $binary_str = fread($inf,8);
      $record_head = unpack("N1index/N1len",$binary_str);

      $binary_str = fread($inf,4);
      $record_type = unpack("V1type",$binary_str);

      $binary_str = fread($inf,16);
      $point = unpack("d1x/d1y",$binary_str);
      print_r($point);

      $total_offset = $total_offset + 12 + 16;
    }
  }

  fclose($inf);

?>
