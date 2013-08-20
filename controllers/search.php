<?php

  foreach (glob("../views/search/*.php") as $filename) {
      include_once $filename;
  }
  
  include_once '../config.php';
  include_once '../parsing/tokenizer.php';
  include_once '../parsing/fuzzy_matcher.php';
  include_once '../parsing/fuzzy2daft.php';

  function index() {
    search_index_view(array());
  }

  function do_search() {
    $tokenizer = new Tokenizer();
    
    /* Step 1: tokenize the string */
    $tokens = $tokenizer->tokenize($_GET['search_input']);
    
    /* Step 2: identify each token using FuzzyMatcher */
    $matcher     = new FuzzyMatcher();
    $atoms       = $matcher->tokenized2atoms($tokens);
    
    /* Step 3: produce concrete params using these atoms */
    $daft_params = fuzzy2daft($atoms);
    
    /* Step 4: contact Daft */
    $ads = get_search_results($daft_params);
    
    search_results_view(array('results' => $ads, 'query' => $daft_params));
    
  }
  
  function get_search_results($daft_params) {
    $DaftAPI = new SoapClient(
      "http://api.daft.ie/v2/wsdl.xml",
       array('features' => SOAP_SINGLE_ELEMENT_ARRAYS)
    );
    
    $parameters = array(
      'api_key' => DAFT_API_KEY,
      'query' => $daft_params
    );
    
    if (isset($daft_params['for_rent'])) {
      $response = $DaftAPI->search_rental($parameters);
    } else {
      $response = $DaftAPI->search_sale($parameters);
    }
    $results = $response->results;
    
    return $results;

  }
  
  /* Routes for this controller */
  $is_index = count($_GET) == 0;
  
  if ($is_index) {
    index();
  } else {
    switch ($_GET['action']) {
      case 'do_search':
        do_search();
        break;
    }
  }