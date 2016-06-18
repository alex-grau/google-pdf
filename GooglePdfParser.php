<?php

class GooglePdfParser {
    
    private $file_origin_content;
    private $txt_array;
    private $css;
    private $txt_final;
    private $txt_body;
    
    public function __construct($origin_content)
    {
        $this->file_origin_content = $origin_content;

        $this->css = [];
        $this->txt_final = [];
        $this->txt_body = [];

        $this->txt_array = explode(PHP_EOL, $this->file_origin_content);
    }
    
    public function transform() {
        $font_array = $this->textToFontTags();

       // dd($this->css);
        dd($font_array);
    }

    private function textToFontTags()
    {
        $new_page_pattern = '/<div style="position:absolute;top:([0-9]*);left:0"><hr><table border=0 width=100%><tr><td bgcolor=eeeeee align=right><font face=arial,sans-serif><a name=([0-9]*)><b>Page ([0-9]*)<\/b><\/a><\/font><\/td><\/tr><\/table><\/div>(.*)/';
        $new_page_rep_pattern = '${4}';

        $font_array = [];

        $inside_body = false;
        $inside_font = false;
        $font_pattern = '/<font /m';
        $end_font_pattern = '/^<\/span><\/font>/m';

        foreach ($this->txt_array as $i => $line)
        {
            if (!$inside_body) {
                preg_match('/<body /i', $line, $matches);
                if ($matches) {
                    $inside_body = true;
                }
                $this->txt_final[] = $line;

                continue;
            }

            // Neteja d'espais idiotes
            $line = preg_replace('/\s+<\/a>/', '</a> ', $line);

            // Page Break i remove de Google Pages
            preg_match($new_page_pattern,
                $line,
                $matches_new_page);
            if ($matches_new_page) {
                $line = preg_replace($new_page_pattern, $new_page_rep_pattern, $line);
                if (array_key_exists(count($font_array)-1, $font_array)) {
                    $font_array[count($font_array)-2]['page_break_after'] = true;
                }
            }

            if (!$inside_font) {
                preg_match($font_pattern, $line, $font_matches);
                if ($font_matches) {
                    $inside_font = true;
                    $actual_font = ['font_start_line' => $line, 'divs' => [], 'css' => $this->getCssClass($line)];
                }
            } else {
                preg_match($end_font_pattern, $line, $font_matches);
                if ($font_matches) {
                    $actual_font['font_end_line'] = $line;
                    $font_array[] = $actual_font;
                    $inside_font = false;
                } else {
                    $actual_font['divs'][] = $line;
                }
            }
        }

        return $font_array;
    }

    private function parseDivLine($css_id, $line) {
        $pattern = '/<div style="position:absolute;top:([0-9]*);left:([0-9]*)">(.*)/';
        $line_parsed = preg_replace_callback($pattern, function($matches) use ($css_id) {
            $top = $matches[1];
            $left = $matches[2];

            return '<div class="pdf_style_'.$css_id.'" style="margin-left:'.$left.'">'.$matches[3];
        }, $line);

        return $line_parsed;
    }

    private function getCssClass($line) {
        preg_match('/<span style="(.*)">/', $line, $matches);
        if ($matches) {
            $styles = $matches[1];
            if (substr($styles, -1) != ';') {
                $styles .= ';';
            }
            if (!array_key_exists(md5($styles), $this->css)) {
                $this->css[md5($styles)] = ['style' => $styles, 'id' => count($this->css)];
            }

            return $this->css[md5($styles)]['id'];
        }

        throw new Exception('No CSS on line '.$line);
    }
}
