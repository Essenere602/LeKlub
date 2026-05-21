<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

enum SystemNotificationType: string
{
    case ReportRejected = 'report_rejected';
    case ReportAccepted = 'report_accepted';
    case Warning = 'warning';
    case Suspension = 'suspension';
}
