<?php

namespace App\Services\Pages\Enums;

enum Status: int
{
    case Published = 1;

    case Draft = 0;
}
