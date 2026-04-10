<?php

namespace app\Models;

enum ERole: string {
    case REGULAR = 'User';
    case ADMIN = 'Admin';
}