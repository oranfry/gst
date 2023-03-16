<?php

namespace gst\linetype;

class peergst extends plaintransaction
{
    public function __construct()
    {
        parent::__construct();

        $this->table = 'peergst';
    }
}
