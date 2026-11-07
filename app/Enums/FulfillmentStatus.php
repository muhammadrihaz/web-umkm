<?php

namespace App\Enums;

enum FulfillmentStatus: string
{
    case Unfulfilled = 'unfulfilled';
    case Preparing = 'preparing';
    case ReadyForPickup = 'ready_for_pickup';
    case PickedUp = 'picked_up';
    case OutForDelivery = 'out_for_delivery';
    case Delivered = 'delivered';
}
