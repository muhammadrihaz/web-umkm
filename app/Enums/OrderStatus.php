<?php

namespace App\Enums;

enum OrderStatus: string
{
    case AwaitingPayment = 'awaiting_payment';
    case AwaitingConfirmation = 'awaiting_confirmation';
    case Confirmed = 'confirmed';
    case Processing = 'processing';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Exception = 'exception';
}
