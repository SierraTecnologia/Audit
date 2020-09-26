<?php

namespace Audit\Models;

use DB;
use App;
use URL;
use Facilitador;
use Event;
use Config;
use Session;
use SupportURL;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Log;

abstract class Base extends Eloquent
{

    //---------------------------------------------------------------------------
    // Overrideable properties
    //---------------------------------------------------------------------------
    


    /**
     * Specify columns that shouldn't be duplicated by Bkwld\Cloner.  Include
     * slug by default so that Sluggable will automatically generate a new one.
     *
     * @var array
     */
    protected $clone_exempt_attributes = ['slug'];

    /**
     * Relations to follow when models are duplicated
     *
     * @var array
     */
    protected $cloneable_relations;

    /**
     * If populated, these will be used instead of the files that are found
     * automatically by getCloneableFileAttributes()
     *
     * @var array
     */
    protected $cloneable_file_attributes;

    /**
     * If populated, these will ignore the override mutators in admin that are
     * in hasGetMutator() and hasSetMutator()
     *
     * @var array
     */
    protected $admin_mutators = [];


}
