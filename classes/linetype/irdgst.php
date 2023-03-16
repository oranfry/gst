<?php

namespace gst\linetype;

class irdgst extends plaintransaction
{
    public function __construct()
    {
        parent::__construct();

        $this->table = 'irdgst';
    }
}
