<?php

function calculate_rating($rating)
{
    $rounded_rating = round($rating * 2) / 2;
    return number_format($rounded_rating, 1);
}