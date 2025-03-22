<?php

namespace gst\linetype;

class hiddengsttransaction extends gsttransaction
{
    public function __construct()
    {
        parent::__construct();

        $this->table = 'hiddentransaction';
    }
}
