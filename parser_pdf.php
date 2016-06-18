<?php
include 'vendor/autoload.php';
include 'helpers.php';
include 'GooglePdfParser.php';

$original_file = file_get_contents(realpath(dirname(__FILE__)).'/'.'rtc_o.htm');
$final_file = realpath(dirname(__FILE__)).'/'.'rtc_f.html';

$txt_final = [];

$parser = new GooglePdfParser($original_file);
$parser->transform();
exit();


dd($font_array);

// Breaks page:
foreach ($txt as $line)
{
    // Neteja d'espais idiotes
    $line = preg_replace('/\s+<\/a>/', '</a> ', $line);

    // New page
    $new_page_pattern = '/<div style="position:absolute;top:([0-9]*);left:0"><hr><table border=0 width=100%><tr><td bgcolor=eeeeee align=right><font face=arial,sans-serif><a name=([0-9]*)><b>Page ([0-9]*)<\/b><\/a><\/font><\/td><\/tr><\/table><\/div>(.*)/';
    $new_page_rep_pattern = '<!-- PDF: BEGIN_PAGE ${2} -->'.PHP_EOL.'<div style="position:absolute;top:${1};left:0"><hr><table border=0 width=100%><tr><td bgcolor=eeeeee align=right><font face=arial,sans-serif><a name=${2}><b>Page ${3}</b></a></font></td></tr></table></div><p class="pdf-page-break"></p>'.PHP_EOL.'<!-- PDF: END_PAGE ${2} -->'.PHP_EOL.'${4}';

    $new_page_rep_pattern = '${4}<div class="pdf-page-break"></div>';
    preg_match($new_page_pattern,
        $line,
        $matches_new_page);
    if ($matches_new_page) {
        $line = preg_replace($new_page_pattern, $new_page_rep_pattern, $line);
    }

    // Top
    $pattern = '/<div style="position:absolute;top:([0-9]*);left:([0-9]*)">(.*)/';
    $top = null;
    $left = null;
    $line = preg_replace_callback($pattern, function($matches) use (&$top, &$left) {
        $top = $matches[1];
        $left = $matches[2];
       // return '<div style="margin-left:'.$matches[2].'">'.$matches[3];
        return '<div style="position:absolute;top:'.$top.';left:'.$matches[2].'">'.$matches[3];
    }, $line);

    $txt_final[] = $line;
}

file_put_contents($final_file, implode($txt_final));
