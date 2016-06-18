<?php

if (! function_exists('collect')) {
    /**
     * Create a collection from the given value.
     *
     * @param  mixed  $value
     * @return \Illuminate\Support\Collection
     */
    function collect($value = null)
    {
        return new \Illuminate\Support\Collection($value);
    }
}

if (! function_exists('dd')) {
// Funcion debug
    function dd($arr, $stop = true)
    {
        echo '<pre>';
        print_r($arr);
        echo '</pre>';
        if ($stop) {
            exit();
        }
    }
}