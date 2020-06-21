<?php $hsdgh = file_get_contents('aircraft.txt');
$hsdgh = json_decode($hsdgh, true);

function getAllAircraft() {
  return $hsdgh;
}

function getAircraftName($id) {
  $hsdgh = file_get_contents('aircraft.txt');
  $hsdgh = json_decode($hsdgh, true);
  foreach ($hsdgh as $item) {
    if ($item["LiveryId"] == $id) {
      return $item;
      break;
    }
  }
}