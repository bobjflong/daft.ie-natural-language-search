<?php
  
  
  function search_index_view($view_model) {
    show_head();
    
    show_form();
    
    example_queries();
    
    show_footer();
  }
  
  function search_results_view($view_model) {
    show_head();
  
    echo("<h3>Here's what I send to Daft:</h3><ul>");
    
    foreach($view_model['query'] as $key => $val) {
      echo("<li>");
      echo $key.": ";
      if (is_array($val)) {
        echo("<ul>");
        foreach($val as $inner_val) {
          echo("<li>");
          echo("value: ");
          echo($inner_val);
          echo("</li>");
        }
        echo("</ul>");
      } else {
        echo $val;
      }
      echo "<br/>";
      echo("</li>");
    }
    
    echo("</ul>");
    
    if (isset($view_model['results']->ads)) {
      echo("<h4>Here's what I get back:</h4>");
      foreach($view_model['results']->ads as $ad)
      {
        printf(
          '<a href="%s">%s</a><br />'
          , $ad->daft_url
          , $ad->full_address
        );
      }
    }      
    show_footer();

  }
