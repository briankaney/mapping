<?php

//--------------------------------------------------------------------------------------
//   Function to read the 28 byte header section of a raster mask file.  File point is 
//   sent as the arg and an array of 7 integers is returned.
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



  function CopyMaskHeader($outf,$header)
  {
    $binary_str = pack("iiiiiii",$header['num_x'],$header['num_y'],$header['scale']*$header['wlon'],$header['scale']*$header['nlat'],
                  $header['scale']*$header['del_lon'],$header['scale']*$header['del_lat'],$header['scale']);
    gzwrite($outf,$binary_str);
  }



  function WriteMaskHeader($outf,$num_x,$num_y,$wlon,$nlat,$del_lon,$del_lat,$scale)
  {
   $binary_str = pack("iiiiiii",$num_x,$num_y,$wlon,$nlat,$del_lon,$del_lat,$scale);
    gzwrite($outf,$binary_str);
  }





?>
