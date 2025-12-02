<?php

namespace App\Models;

class Sale
{
    public $id;
    public $date_time;
    public $customer;
    public $lines = [];

    public function load(array $relations)
    {
        // Stubbed load method for compatibility in this simplified environment.
        return $this;
    }
}
