<?php

/* 
 * This file contains function definitions that convert fuzzy idenfiers into concrete params that
 * can be passed to daft
 */

/* 
  Do some post-processing, like sort between min/max (the semantics for which could be tighter)
  This will produce a set of params ready for Daft
*/
function fuzzy2daft($atoms) {
    
  //default to sale
  $for_sale = true;
  $for_rent = false;
  
  $daft_params = array();
  
  $min_price = 0;
  $max_price = NULL;
  
  $min_rooms = NULL;
  $max_rooms = NULL;
  
  $counties  = array();
  $areas     = array();
  
  foreach($atoms as $atom) {
    
    /* Determine how the user has structured price info */
    if ($atom['description'] == 'price identifier') {
      if (!$max_price) {
        $max_price = intval($atom['value']);
      } else {
        $current_max = intval($max_price);
        $new_possible_max = intval($atom['value']);
        
        if ($new_possible_max > $current_max) {
          $min_price = $current_max;
          $max_price = $new_possible_max;
        } else {
          $min_price = $new_possible_max;
        }
      }
      
    /* Determine how the user has structured bedroom info */
    } else if ($atom['description'] == 'bedroom identifier') {
      /* Identical semantics to above? abstract out code to reduce repetition */
      if (!$max_rooms) {
        $max_rooms = intval($atom['value']);
      } else {
        $current_max = intval($max_rooms);
        $new_possible_max = intval($atom['value']);
        
        if ($new_possible_max > $current_max) {
          $min_rooms = $current_max;
          $max_rooms = $new_possible_max;
        } else {
          $min_rooms = $new_possible_max;
        }
      }
    
    /* Keep track of areas, counties, and sell/rent type */
    } else if ($atom['description'] == 'county identifier') {
      array_push($counties, intval($atom['extra_info']));
    } else if ($atom['description'] == 'area identifier') {
      array_push($areas, intval($atom['extra_info']));
    } else if ($atom['description'] == 'sell type') {
      if (!$for_rent)
        $for_sale = true;
    } else if ($atom['description'] == 'rent type') {
      $for_rent = true;
      $for_sale = false;
    }
  }
  
  /* Plug in the daft formatted params */
  if ($max_rooms && !$min_rooms) {
    /* we have exact rooms */
    $daft_params['bedrooms'] = $max_rooms;
  } else if ($max_rooms && $min_rooms ) {
    $daft_params['min_bedrooms'] = $min_rooms;
    $daft_params['max_bedrooms'] = $max_rooms;
  }
  if ($max_price) {
    $daft_params['max_price'] = $max_price;
  }
  if ($min_price) {
    $daft_params['min_price'] = $min_price;
  }
  if (!empty($counties)) {
    $daft_params['counties']  = $counties;
  }
  if (!empty($areas)) {
    $daft_params['areas']  = $areas;
  }
  
  /* This just lets me know what API action to use */
  if ($for_rent) {
    $daft_params['for_rent'] = true;
  } else {
    $daft_params['for_sale'] = true;
  }
  
  return $daft_params;
}