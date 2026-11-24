<?php

namespace App\Enums;

enum DelaySeverity: string
{
    case NONE = 'NONE';
    case LOW = 'LOW';
    case MEDIUM = 'MEDIUM';
    case HIGH = 'HIGH';
}