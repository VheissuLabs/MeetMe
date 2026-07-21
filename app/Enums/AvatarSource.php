<?php

namespace App\Enums;

enum AvatarSource: string
{
    case Github = 'github';
    case X = 'x';
    case Gravatar = 'gravatar';
}
