<?php
/*
Filename:         google_xml_sitemap.php
Author:           Francis Tsao
Date Created:     03/06/2024
Purpose:          MySQL PDO database connection script
History:          
*/


// MySQL PDO DB connection
include_once 'db_connect.inc.php';
?>
<!DOCTYPE html>
<html>
<head>
   <title>PHP Google XML Sitemap App</title>
   <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Kanit">
   <style type="text/css">
   <!--
   body {
   font-family: "Noto Sans", sans-serif;
   font-size: 16px;
   }

   h1 {
      font-family: Kanit, sans-serif;
      font-size: 40px;
   }

   #rcorners2 {
      border-radius: 25px;
      border: 5px solid #73AD21;
      padding: 20px;
      width: 95%;
      height: 150px;
   }
   -->
   </style>
</head>

<body id="rcorners2">

<h1>PHP Google XML Sitemap App</h1>
<p>Simple(?) application that will pull records from your MySQL database to create a list of URLs from your site. This 
should generally be the same format for each URL, but could be different.</p>

</body>
</html> 