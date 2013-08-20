<?php

  /*
  
  Transforms a tokenized string into a list of distinct atoms.
  Each atom contains a description telling us what the keyword refers to
  
  */
  class FuzzyMatcher {
    
    public $areas = NULL;
    public $counties = NULL;
    
    /* Load the area data directly from daft or use our local cache? */
    public $load_areas_from_daft = true;
    
    function produce_atom($str, $desc, $regex, $extra_info = "none") {
      if (preg_match($regex, $str)) {
        return array(
          "description" => $desc,
          "value"       => $str,
          "extra_info"  => $extra_info 
        );
      } else {
        return false;
      }
    }
    
    function is_rent_type($str) {
      $sell_regex = "/\brents?\b|\brentables?\b|\brentals?\b|\blet\b|\blettings?\b/";
      return $this->produce_atom($str, "rent type", $sell_regex);

    }
    
    function is_sell_type($str) {
      
      $sell_regex = "/\b\bsell\b|\bsales?\b|\bbuy\b|\bbuying\b/i";
      
      return $this->produce_atom($str, "sell type", $sell_regex);
    }
    
    function is_bedroom_number($str) {
      
      //Let's assume no more than 9 bedrooms!
      $bedroom_regex = "/\b[0-9]{1,1}\b/";
      
      return $this->produce_atom($str, "bedroom identifier", $bedroom_regex);
    }
    
    function is_price($str) {
      $price_regex = "/\b[0-9]{2,}\b/";
      
      return $this->produce_atom($str, "price identifier", $price_regex);
    }
    
    /* Convert a string to a regex with word boundaries */
    function str2regex($str) {
      return "/\b".$str."\b/i";
    }
    
    function is_area($str) {
      
      /* Ideally we would use a cascade here, going from general to specific */
      
      /*
         ie. initially search for common words like Dublin, Galway etc.
         then use those terms to help focus our search for matches for tokens like "O'Connell Street" etc.
        ... this would improve accuracy and performance.
      */
      
      /* 
       * For the purposes of this, I will focus on the 'area' portion of the API
      */
      if ($this->load_areas_from_daft && !$this->areas) {
        $DaftAPI = new SoapClient("http://api.daft.ie/v2/wsdl.xml");
        
        $parameters = array(
            'api_key'   =>  DAFT_API_KEY,
            'area_type' =>  "area"
        );
        
        $response = $DaftAPI->areas($parameters);
        $this->areas = $response->areas;
      } else if (!$this->areas) {
        $this->areas = unserialize(file_get_contents('../parsing/data/areas.txt'));
      }
      
      /* Ideally we'd be using a proper search tool like solr... */
      foreach($this->areas as $area) {
        if ($atom = $this->produce_atom($str, 'area identifier', $this->str2regex($area->name), $area->id)) {
          return $atom;
        }
      }
      
      return false;
    }
    
    /* The above comments apply here too */
    function is_county($str) {
      
      /* Abstract out is_area and is_county to reduce repetitive code? */
      if ($this->load_areas_from_daft && !$this->counties) {
        $DaftAPI = new SoapClient("http://api.daft.ie/v2/wsdl.xml");
        
        $parameters = array(
            'api_key'   =>  DAFT_API_KEY,
            'area_type' =>  "county"
        );
        
        $response = $DaftAPI->areas($parameters);
        $this->counties = $response->areas;
      } else if (!$this->counties) {
        $this->counties = unserialize(file_get_contents('../parsing/data/counties.txt'));
      }
      foreach($this->counties as $county) {
        $county_name = str_replace("Co. ", "", $county->name);
        if ($atom = $this->produce_atom($str, 'county identifier', $this->str2regex($county_name), $county->id)) {
          return $atom;
        }
      }
      return false;
    }
    
    /* Use the identifier functions to map tokens -> atoms */
    function tokenized2atoms($tokens) {
      
      $result = array( );
      
      $identifiers = array('is_sell_type', 'is_rent_type', 'is_bedroom_number', 'is_price', 'is_area', 'is_county');
            
      foreach ($tokens as $token) {
        
        foreach ($identifiers as $identifier) {
          if ($atom = $this->$identifier($token)) {
            array_push($result, $atom);
          }
        }
      }
      return $result;
    }
    
    
  }