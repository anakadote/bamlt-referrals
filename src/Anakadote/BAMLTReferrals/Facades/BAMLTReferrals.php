<?php

namespace Anakadote\BAMLTReferrals\Facades;
 
use Illuminate\Support\Facades\Facade;
 
class BAMLTReferrals extends Facade {
 
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'bamlt-referrals'; }
    
}
