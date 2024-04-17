<?php
// Create a new XMLWriter instance
$xmlWriter = new XMLWriter();

// Set the output to memory or a file
$xmlWriter->openMemory();
#$xmlWriter->openURI('xmlwriter_imagesitemap.xml');


// Set indentation and line breaks for readability
$xmlWriter->setIndent(true);
$xmlWriter->setIndentString('   '); // Adjust the number of spaces for indentation as desired


// Start the document with XML declaration and encoding
$xmlWriter->startDocument('1.0', 'UTF-8');




// Start the 'urlset' element with namespace and attributes
$xmlWriter->startElementNS(null, 'urlset', 'http://www.sitemaps.org/schemas/sitemap/0.9');
$xmlWriter->writeAttributeNS('xmlns', 'news', null, 'http://www.google.com/schemas/sitemap-video/1.1');


// Start the '<url>' element
$xmlWriter->startElement('url');

   // Write the '<loc>' element
   $xmlWriter->writeElement('loc', 'https://www.example.com/videos/some_video_landing_page.html');

      $xmlWriter->startElement('video:video'); // Start '<video:video>'

         // REQUIRED VIDEO ELEMENTS (5) - thumbnail_loc/title/description/content_loc/player_loc
         $xmlWriter->writeElement('video:thumbnail_loc', 'https://www.example.com/thumbs/345.jpg');
         $xmlWriter->writeElement('video:title', 'Grilling steaks for winter');
         $xmlWriter->writeElement('video:description', 'In the freezing cold, Roman shows you how to get perfectly done steaks every time.');
         $xmlWriter->writeElement('video:content_loc', 'http://streamserver.example.com/video345.mp4');
         $xmlWriter->writeElement('video:player_loc', 'https://www.example.com/videoplayer.php?video=345');
         

         ##########################
         # OPTIONAL VIDEO ELEMENTS
         ##########################
         // NOTICE - video:[price|restriction|uploader] follows a different, but same format for all three
         $xmlWriter->startElementNs('video', 'restriction', null);
            $xmlWriter->writeAttribute('relationship', 'allow');
            // Write the text content of the video:restriction element
            $xmlWriter->text('IE GB US CA');
         // Close the video:restriction element
         $xmlWriter->endElement();

         // NOTICE - video:[price|restriction|uploader] follows a different, but same format for all three
         $xmlWriter->startElementNs('video', 'price', null);
            $xmlWriter->writeAttribute('currency', 'EUR');
            // Write the text content of the video:restriction element
            $xmlWriter->text('1.99');
         // Close the video:price element
         $xmlWriter->endElement();

         // NOTICE - video:[price|restriction|uploader] follows a different, but same format for all three
         $xmlWriter->startElementNs('video', 'uploader', null);
            $xmlWriter->writeAttribute('info', 'https://www.example.com/users/grillymcgrillerson');
            // Write the text content of the video:uploader element
            $xmlWriter->text('GrillyMcGrillerson');
         // Close the video:uploader element


         // additional optional video elements
         $xmlWriter->writeElement('video:duration', '600');
         $xmlWriter->writeElement('video:expiration_date', '2021-11-05T19:20:30+08:00');
         $xmlWriter->writeElement('video:rating', '4.2');
         $xmlWriter->writeElement('video:view_count', '12345');
         $xmlWriter->writeElement('video:publication_date', '2007-11-05T19:20:30+08:00');
         $xmlWriter->writeElement('video:duration', '600');

         $xmlWriter->endElement();


      $xmlWriter->endElement(); // End the '</video:video>' element

   // End the '</loc>' element
   $xmlWriter->endElement();

// End the 'url' element
$xmlWriter->startElement('url');



// End the document (urlset)
$xmlWriter->endDocument();

// Output the XML content
echo '<pre>'.htmlspecialchars($xmlWriter->outputMemory(), ENT_XML1 | ENT_COMPAT, 'UTF-8', true);
#echo $xmlWriter->outputMemory();