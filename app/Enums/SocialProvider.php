<?php

namespace App\Enums;

enum SocialProvider: string
{
    case Github = 'github';
    case X = 'x';
    case Bluesky = 'bluesky';
}
