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
$xmlWriter->writeAttributeNS('xmlns', 'news', null, 'http://www.google.com/schemas/sitemap-news/0.9');


// Start the '<url>' element
$xmlWriter->startElement('url');

   // Write the '<loc>' element
   $xmlWriter->writeElement('loc', 'http://www.example.org/business/article55.html');

      $xmlWriter->startElement('news:news'); // Start '<news:news>'

         $xmlWriter->startElement('news:publication');
            $xmlWriter->writeElement('news:name', 'The Example Times');
            $xmlWriter->writeElement('news:language', 'en');
         $xmlWriter->endElement();

         $xmlWriter->writeElement('news:publication_date', '2008-12-23');
         $xmlWriter->writeElement('news:title', 'Companies A, B in Merger Talks');
      $xmlWriter->endElement(); // End the '</news:news>' element

   // End the '</loc>' element
   $xmlWriter->endElement();

// End the 'url' element
$xmlWriter->startElement('url');



// End the document (urlset)
$xmlWriter->endDocument();

// Output the XML content
echo '<pre>'.htmlspecialchars($xmlWriter->outputMemory(), ENT_XML1 | ENT_COMPAT, 'UTF-8', true);
#echo $xmlWriter->outputMemory();