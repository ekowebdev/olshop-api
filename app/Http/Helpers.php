<?php

function calculate_rating($rating)
{
    $rounded_rating = round($rating * 2) / 2;
    return number_format($rounded_rating, 1);
}

function format_money($number)
{
    return 'Rp. ' . number_format($number, 2, ",", ".");
}

function is_multidimensional_array($array) {
    if (!is_array($array)) {
        return false;
    }

    foreach ($array as $element) {
        if (is_array($element)) {
            return true;
        }
    }

    return false;
}