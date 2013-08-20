<?php
  
  function show_head() {
    echo "<!doctype html>
            <head>
              <title>Daft Natural Language Search</title>
              <link rel='stylesheet' type='text/css' href='../static/styles/main.css'/>
            </head>
            <body>
          ";
  }
   
  function show_footer() {       
    echo "<h4 class='footer'>
             thanks for visiting!
          </h4>
          <a href='search'>[Home]</a>
          </body>
          </html>";
  }
  
  function show_form() {
    echo "<form action=''>
            <input type='hidden' name='action' value='do_search' />
            <input type='text' name='search_input' placeholder='Type your query...'/>
            <input type='submit' value='Search'/>
          </form>";
  }
  
  function example_queries() {
    echo "<h4>Examples</h4>
            <ul>
              <li>for sale in Clonsilla</li>
              <li>5 bed rent</li>
              <li>Castleknock 3 bedroom for sale</li>
              <li>2 bed apartment to let Dublin</li>
              <li>3 or 4 bed house to rent in Dundrum for 1000 per month</li>
            </ul>";
  }
