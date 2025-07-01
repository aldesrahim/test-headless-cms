<?php

namespace App\Enums;

enum DateFormat: string
{
    case ReadableDate = 'M j, Y';

    case ReadableDateTime = 'M j, Y H:i:s';
}
