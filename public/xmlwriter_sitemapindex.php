<?php
// Create a new XMLWriter instance
$xmlWriter = new XMLWriter();

// Set the output to memory or a file
$xmlWriter->openMemory();
//$xmlWriter->openURI('sitemap.xml');


// Set indentation and line breaks for readability
$xmlWriter->setIndent(true);
$xmlWriter->setIndentString('   '); // Adjust the number of spaces for indentation as desired


// Start the document with XML declaration and encoding
$xmlWriter->startDocument('1.0', 'UTF-8');

// Start the 'sitemapindex' element with namespace and attributes
$xmlWriter->startElementNS(null, 'sitemapindex', 'http://www.sitemaps.org/schemas/sitemap/0.9');
$xmlWriter->writeAttributeNS('xmlns', 'xsi', null, 'http://www.w3.org/2001/XMLSchema-instance');
$xmlWriter->writeAttributeNS('xsi', 'schemaLocation', null, 'http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd');


// Start the 'sitemap' element
$xmlWriter->startElement('sitemap');

// Write the 'loc' element
$xmlWriter->writeElement('loc', 'http://www.mydomain.com/someurl/sitemap1.xml.gz');
$xmlWriter->writeElement('lastmod', date('Y-m-d\TH:i:s+00:00'));

// End the 'sitemap' element
$xmlWriter->endElement();



// Start the 'sitemap' element
$xmlWriter->startElement('sitemap');

// Write the 'loc' element
$xmlWriter->writeElement('loc', 'http://www.mydomain.com/someurl/sitemap2.xml.gz');
$xmlWriter->writeElement('lastmod', date('Y-m-d\TH:i:s+00:00'));

// End the 'sitemap' element
$xmlWriter->endElement();



// End the document (sitemapindex)
$xmlWriter->endDocument();

// Output the XML content
echo '<pre>'.htmlspecialchars($xmlWriter->outputMemory(), ENT_XML1 | ENT_COMPAT, 'UTF-8', true);
#echo $xmlWriter->outputMemory();