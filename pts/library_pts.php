<?php

  function SkipNBlocks($fp,$n)
  {
    for($i=0;$i<$n;++$i)
    {
      $line = trim(fgets($fp));
      $col = explode('|',$line);
      for($j=0;$j<$col[1];++$j) { fgets($fp); }
    }
  }

  function StoreBlock($fp)
  {
    $block = array();

    $block[0] = trim(fgets($fp));
    $col = explode('|',$block[0]);
    for($i=0;$i<$col[1];++$i) { $block[$i+1] = trim(fgets($fp)); }

    return $block;
  }

  function RewindToBlock($fp,$new)
  {
    rewind($fp);
    fgets($fp);
    SkipNBlocks($fp,$new);
  }

  function PrintNBlocks($fp,$n)
  {
    for($i=0;$i<$n;++$i)
    {
      $line = trim(fgets($fp));
      $col = explode('|',$line);
      print "$line\n";
      for($j=0;$j<$col[1];++$j)
      {   //---replaceable also
        $line = trim(fgets($fp));
        print "$line\n";
      }
    }
  }

  function PrintNBlocksWithShiftedIndex($fp,$n,$shift)
  {
    for($i=0;$i<$n;++$i)
    {
      $line = trim(fgets($fp));
      $col = explode('|',$line);
      $num_col = count($col)-1;

      $new_index = $col[0] + $shift;
      print "$new_index|";
      for($j=1;$j<$num_col;++$j) { print "$col[$j]|"; }
      print "\n";

      for($j=0;$j<$col[1];++$j)
      {  //--these four could be replaced with function below
        $line = trim(fgets($fp));
        print "$line\n";
      }
    }
  }

  function PrintJustPointsSingleBlock($fp,$num_points)
  {
    for($j=0;$j<$num_points;++$j)
    {
      $line = trim(fgets($fp));
      print "$line\n";
    }
  }

//--------------------------------------------------------------------------------------

  function PrintBlockFromMemory($block)
  {
    print "$block[0]\n";
    $col = explode('|',$block[0]);
    for($i=1;$i<=$col[1];++$i) { print "$block[$i]\n"; }
  }

  function GetBlockNumPoints($block)
  {
    $col = explode('|',$block[0]);
    return $col[1];
  }

  function GetBoundingBoxOfBlock($block)   //--reads it from header
  {
    $col = explode('|',$block[0]);
    $ret_bb[0]=$col[2];
    $ret_bb[1]=$col[3];
    $ret_bb[2]=$col[4];
    $ret_bb[3]=$col[5];

    return $ret_bb;
  }

  function FindBoundingBoxOfBlock($block)   //--finds it by looping thru points.  can be used to fix header if out of date
  {
    $ret_bb = array(180,-90,-180,90);
     
    $col = explode('|',$block[0]);
    for($i=1;$i<=$col[1];++$i)
    {
      $fields = explode(' ',$block[$i]);
      if($fields[0]<$ret_bb[0]) { $ret_bb[0]=$fields[0]; }
      if($fields[0]>$ret_bb[2]) { $ret_bb[2]=$fields[0]; }
      if($fields[1]>$ret_bb[1]) { $ret_bb[1]=$fields[1]; }
      if($fields[1]<$ret_bb[3]) { $ret_bb[3]=$fields[1]; }
    }

    return $ret_bb;
  }

  function ReverseBlockOrder($block)
  {
    $ret_block = array();
    $ret_block[0] = $block[0];

    $num_points = GetBlockNumPoints($block);
    for($i=0;$i<$num_points;++$i)
    {
      $ret_block[$i+1] = $block[$num_points-$i];
    }
    return $ret_block;
  }

  function GetSubBlock($start,$num,$block)
  {
    $ret_block = array();
    $ret_block[0] = ReplaceBlockHeaderField($block[0],1,$num);

    for($i=0;$i<$num;++$i)
    {
      $ret_block[1+$i] = $block[1+$start+$i];
    }

    $new_bb = FindBoundingBoxOfBlock($ret_block);
    $ret_block[0] = ReplaceBlockHeaderField($ret_block[0],2,$new_bb[0]);  //--new function to do this in one step
    $ret_block[0] = ReplaceBlockHeaderField($ret_block[0],3,$new_bb[1]);
    $ret_block[0] = ReplaceBlockHeaderField($ret_block[0],4,$new_bb[2]);
    $ret_block[0] = ReplaceBlockHeaderField($ret_block[0],5,$new_bb[3]);

    return $ret_block;
  }

  function AreBlocksDuplicates($block1,$block2)
  {
    if(!AreBoundingBoxesSame(GetBoundingBoxOfBlock($block1),GetBoundingBoxOfBlock($block2))) { return false; }

    $num_pts = GetBlockNumPoints($block1);
    if($num_pts!=GetBlockNumPoints($block2)) { return false; }

    for($i=0;$i<$num_pts;++$i) { if($block1[$i+1]!=$block2[$i+1]) { return false; }  }

    return true;
  }

  function AreBlocksRedundant($block1,$block2)
  {
    if(AreBlocksDuplicates($block1,$block2)==true) { return 1; }
    $test_block = ReverseBlockOrder($block2);
    if(AreBlocksDuplicates($block1,$test_block)==true) { return -1; }
    return 0;
  }

  function IsBlock2RedundantWithEitherEndOfBlock1($block1,$block2)
  {
    $num_pts1 = GetBlockNumPoints($block1);
    $num_pts2 = GetBlockNumPoints($block2);
    if($num_pts2>$num_pts1) { return false; }

    $test_block = GetSubBlock(0,$num_pts2,$block1);
    if(AreBlocksRedundant($test_block,$block2)!=0) { return true; }

    $test_block = GetSubBlock($num_pts1-$num_pts2,$num_pts2,$block1);
    if(AreBlocksRedundant($test_block,$block2)!=0) { return true; }

    return false;
  }

  function PrintJctBlockAsPtsBlockFromMemory($block)
  {
    print "$block[0]\n";
    $col = explode('|',$block[0]);
    for($i=1;$i<=$col[1];++$i)
    {
      $fields = preg_split('/ +/',$block[$i]);
      print "$fields[0] $fields[1]\n";
    }
  }

//--------------------------------------------------------------------------------------

  function ReplaceBlockHeaderField($old_line,$index,$new_value)
  {
    $col = explode('|',$old_line);
    $num_col = count($col)-1;

    $new_line = "";
    for($i=0;$i<$index;++$i) { $new_line = $new_line.$col[$i]."|"; }
    $new_line = $new_line.$new_value."|";
    for($i=$index+1;$i<$num_col;++$i) { $new_line = $new_line.$col[$i]."|"; }

    return $new_line;
  }

//--------------------------------------------------------------------------------------

  function getBlockNumPointsArray($file_lines)
  {
    $ret_array = array();

    $num_blocks = $file_lines[0];

    $j=0;
    ++$j;
    $fields = explode('|',$file_lines[$j]);
    $ret_array[0] = $fields[1];

    $j = $j + 1 + $ret_array[0];
    for($b=1;$b<$num_blocks;++$b)
    {
      $fields = explode('|',$file_lines[$j]);
      $ret_array[$b] = $fields[1];
      $j = $j + 1 + $fields[1];
    }

    return $ret_array;
  }

  function getStartBlockIndexArray($file_lines)
  {
    $ret_array = array();

    $num_blocks = $file_lines[0];

    $ret_array[0] = 1;
    for($b=0;$b<$num_blocks-1;++$b)
    {
      $fields = explode('|',$file_lines[$ret_array[$b]]);
      $ret_array[$b+1] = $ret_array[$b] + 1 + $fields[1];
    }

    return $ret_array;
  }

  function extractBlockFromFileLines($index,$file_lines)
  {
    $start_block = getStartBlockIndexArray($file_lines);
    $num_points = getBlockNumPointsArray($file_lines);
    
    $block = array();
    for($i=0;$i<$num_points[$index]+1;++$i)
    {
      $block[$i] = $file_lines[$start_block[$index]+$i];
    }

    return $block;
  }

  function getSpecificHeaderFieldAsArray($file_lines,$index)
  {
    $ret_array = array();

    $num_blocks = $file_lines[0];
    $start_block = getStartBlockIndexArray($file_lines);

    for($b=0;$b<$num_blocks;++$b)
    {
      $fields = explode('|',$file_lines[$start_block[$b]]);
      $ret_array[$b] = $fields[$index];
    }

    return $ret_array;
  }

//--------------------------------------------------------------------------------------

  function GetFieldFromHeaderLine($header_line,$index)
  {     
    $col = explode('|',$header_line);
    return $col[$index];
  }

//--------------------------------------------------------------------------------------

  function GetBoundingBoxFromHeaderLine($header_line)
  {
    $ret_bb = array(0,0,0,0);
     
    $col = explode('|',$header_line);
    $ret_bb[0] = $col[2];
    $ret_bb[1] = $col[3];
    $ret_bb[2] = $col[4];
    $ret_bb[3] = $col[5];

    return $ret_bb;
  }

//--------------------------------------------------------------------------------------

  function IsPtInBoundBox($lon,$lat,$bb)
  {
    if($lon<$bb[0] || $lon>$bb[2] || $lat>$bb[1] || $lat<$bb[3]) { return false; } 
    return true;
  }

  function AreBoundingBoxesSame($bb1,$bb2)
  {
    if($bb1[0]==$bb2[0] && $bb1[1]==$bb2[1] && $bb1[2]==$bb2[2] && $bb1[3]==$bb2[3]) { return true; }
    return false;
  }

  function IsBoundBoxSubsetOfBoundBox($bb1,$bb2)
  {
    if(IsPtInBoundBox($bb2[0],$bb1) && IsPtInBoundBox($bb2[1],$bb1) && IsPtInBoundBox($bb2[2],$bb1) && IsPtInBoundBox($bb2[3],$bb1)) {
      return 2;
    }
    if(IsPtInBoundBox($bb1[0],$bb2) && IsPtInBoundBox($bb1[1],$bb2) && IsPtInBoundBox($bb1[2],$bb2) && IsPtInBoundBox($bb1[3],$bb2)) {
      return 1;
    }
    return 0;    //--might modify to handle case of a tie (both subs of the other)
  }

//--------------------------------------------------------------------------------------

  function pix_to_equator_from_lat($pix_radius,$lat)
  {
    $deg_to_rad = (2*M_PI)/360;

    return $pix_radius*log((1+sin($lat*$deg_to_rad))/cos($lat*$deg_to_rad));
  }

  function yPixFromLatOnMap($zoom,$clat,$lat)
  {
    $pix_radius = 256*pow(2,$zoom)/(2*M_PI);

    $pix_to_equator_clat = pix_to_equator_from_lat($pix_radius,$clat);
    $pix_to_equator_lat = pix_to_equator_from_lat($pix_radius,$lat);

    return ($pix_to_equator_clat-$pix_to_equator_lat);
  }

  function xPixFromLonOnMap($zoom,$clon,$lon)
  {
    $pixel_per_deg_lon = 256*pow(2,$zoom)/360;

    return -1*$pixel_per_deg_lon*($clon-$lon);
  }

//--------------------------------------------------------------------------------------

  function angleSubtendedBetweenTwoSphCoordPts($lon1,$lat1,$lon2,$lat2)
  {
    $lon1 = deg2rad($lon1);
    $lat1 = deg2rad($lat1);
    $lon2 = deg2rad($lon2);
    $lat2 = deg2rad($lat2);

    return acos(sin($lat1)*sin($lat2) + cos($lat1)*cos($lat2)*cos($lon2-$lon1));
  }

  function distanceBetweenTwoLatLonPts($lon1,$lat1,$lon2,$lat2,$units)
  {
    $EarthRadiusKm = 6371;

    $angle_subtended = angleSubtendedBetweenTwoSphCoordPts($lon1,$lat1,$lon2,$lat2);

    if($units=="km")  return $EarthRadiusKm*$angle_subtended;
    if($units=="m")   return $EarthRadiusKm*1000*$angle_subtended;
    if($units=="mi")  return $EarthRadiusKm*0.621371*$angle_subtended;
    if($units=="ft")  return $EarthRadiusKm*3280.84*$angle_subtended;

    return -999;
  }

  function bearingBetweenTwoFarLatLonPts($lon1,$lat1,$lon2,$lat2,$units)
  {
    $lon1 = deg2rad($lon1);
    $lat1 = deg2rad($lat1);
    $lon2 = deg2rad($lon2);
    $lat2 = deg2rad($lat2);

    $x = cos($lat2)*sin($lon2-$lon1);
    $y = cos($lat1)*sin($lat2) - sin($lat1)*cos($lat2)*cos($lon2-$lon1);
    $bearing = atan2($y,$x);

    if($units=="rad")  return $bearing;
    if($units=="deg")  return $bearing*180/M_PI;

    return -999;
  }

  function bearingBetweenTwoCloseLatLonPts($lon1,$lat1,$lon2,$lat2,$units)
  {
    $mid_lat = deg2rad(($lat2+$lat1)/2);

    $x = ($lon2-$lon1)/cos($mid_lat);
    $y = $lat2-$lat1;
    $bearing = atan2($y,$x);

    if($units=="rad")  return $bearing;
    if($units=="deg")  return $bearing*180/M_PI;

    return -999;
  }

?>
