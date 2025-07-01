<?php

namespace App\Services\Posts\Enums;

enum Status: int
{
    case Published = 1;

    case Draft = 0;
}
