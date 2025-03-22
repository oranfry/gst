<?php

namespace gst\linetype;

class hiddenorigtransaction extends origtransaction
{
    public function __construct()
    {
        parent::__construct();

        $this->table = 'hiddentransaction';
    }
}
