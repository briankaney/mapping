<?php

//--------------------------------------------------------------------------------------
//   Function to read the 28 byte header section of a raster mask file.  File pointer is 
//   sent as the arg and an associative array of 7 integers is returned.
//--------------------------------------------------------------------------------------

  function ReadMaskHeader($inf)
  {
    $header = ARRAY();

    $binary_str = gzread($inf,7*4);
    $values = unpack("i7",$binary_str);

    $header['num_x'] = $values['1'];
    $header['num_y'] = $values['2'];
    $header['wlon'] = $values['3']/$values['7'];
    $header['nlat'] = $values['4']/$values['7'];
    $header['del_lon'] = $values['5']/$values['7'];
    $header['del_lat'] = $values['6']/$values['7'];
    $header['scale'] = $values['7'];

    return $header;
  }

//--------------------------------------------------------------------------------------
//   Function to read the body of a raster mask file.  File pointer is sent as the arg.
//   1D integer array of mask flags is returned.  PHP unpack creates an associative array 
//   but this is translated to an indexed array before being returned.
//--------------------------------------------------------------------------------------

  function ReadMaskBody($inf)
  {
    $header = ReadMaskHeader($inf);

    $num_pts = $header['num_x']*$header['num_y'];
    $binary_str = gzread($inf,$num_pts*2);
    $format = "s".$num_pts;
    $values = unpack($format,$binary_str);

    $flags = Array();
    for($i=1;$i<=$num_pts;++$i)
    {
      $str = strval($i);
      $flags[$i-1] = $values[$str];
    }

    return $flags;
  }

//--------------------------------------------------------------------------------------
//   Function to write out a raster map header.  Copies info from a given associative 
//   array header that must already exist and be passed in.
//--------------------------------------------------------------------------------------

  function CopyMaskHeader($outf,$header)
  {
    $binary_str = pack("iiiiiii",$header['num_x'],$header['num_y'],$header['scale']*$header['wlon'],$header['scale']*$header['nlat'],
                  $header['scale']*$header['del_lon'],$header['scale']*$header['del_lat'],$header['scale']);
    gzwrite($outf,$binary_str);
  }

//--------------------------------------------------------------------------------------
//   Function to write out a raster map header by specifying the field values directly.
//--------------------------------------------------------------------------------------

  function WriteMaskHeader($outf,$num_x,$num_y,$wlon,$nlat,$del_lon,$del_lat,$scale)
  {
   $binary_str = pack("iiiiiii",$num_x,$num_y,$wlon,$nlat,$del_lon,$del_lat,$scale);
    gzwrite($outf,$binary_str);
  }

//--------------------------------------------------------------------------------------
//   Function to write out the flag body of a raster map from an indexed array of integers.
//--------------------------------------------------------------------------------------

  function WriteMaskBody($outf,$flag_array)
  {
    $num = count($flag_array);
    for($i=0;$i<$num;++$i)
    {
      $binary_str = pack("s",$flag_array[$i]);
      gzwrite($outf,$binary_str);
    }
  }

//--------------------------------------------------------------------------------------
//   The following functions are exact copies and borrowed from the library_pts.php file.
//   That library for dealing with the 'pts' format contains much we don't need here, so 
//   the decision was made not to force its inclusion in these utilities but instead to  
//   copy the tiny bit we need.
//--------------------------------------------------------------------------------------

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

?>
