<?php

namespace CultuurNet\ProjectAanvraag;

interface ApiMessageInterface
{
    const API_MESSAGE_TYPE_SUCCESS = 'success';
    const API_MESSAGE_TYPE_WARNING = 'warning';
    const API_MESSAGE_TYPE_ERROR = 'danger';
    const API_MESSAGE_TYPE_INFO = 'info';
}
