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
$xmlWriter->writeAttributeNS('xmlns', 'image', null, 'http://www.google.com/schemas/sitemap-image/1.1');


// Start the '<url>' element
$xmlWriter->startElement('url');

// Write the '<loc>' element
$xmlWriter->writeElement('loc', 'https://example.com/sample1.html');

$xmlWriter->startElement('image:image'); // Start '<image:image>'
$xmlWriter->writeElement('image:loc', 'https://example.com/one_image_on_page_sample.jpg');
$xmlWriter->endElement(); // End the '</image:image>' element

// End the '</loc>' element
$xmlWriter->endElement();



// Start another '<url>' element
$xmlWriter->startElement('url');

// Write the '<loc>' element
$xmlWriter->writeElement('loc', 'https://example.com/anotherpage.html');

$xmlWriter->startElement('image:image'); // Start '<image:image>'
$xmlWriter->writeElement('image:loc', 'https://example.com/multi_image_headline.jpg');
$xmlWriter->endElement(); // End the '</image:image>' element

$xmlWriter->startElement('image:image'); // Start '<image:image>'
$xmlWriter->writeElement('image:loc', 'https://example.com/multi_image_photo.jpg');
$xmlWriter->endElement(); // End the '</image:image>' element

// End the '</url>' element
$xmlWriter->endElement();


// End the document (urlset)
$xmlWriter->endDocument();

// Output the XML content
echo '<pre>'.htmlspecialchars($xmlWriter->outputMemory(), ENT_XML1 | ENT_COMPAT, 'UTF-8', true);
#echo $xmlWriter->outputMemory();