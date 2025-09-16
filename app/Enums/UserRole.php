<?php

namespace App\Enums;

enum UserRole: string
{
    case User = 'user';
    case Provider = 'provider';
    case Admin = 'admin';
}
