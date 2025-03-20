<?php

namespace App\Controller\Enum\HttpStatusCode;

enum SuccessStatus: int
{
    case OK = 200;
    case CREATED = 201;
    case ACCEPTED = 202;
    case NO_CONTENT = 204;
}
