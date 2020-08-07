<?php

namespace Audit\Http\Controllers;

use App;
use URL;
use View;
use Facilitador;
use Event;
use Former;
use Request;
use SupportURL;
use Redirect;
use Response;
use stdClass;
use Validator;
use Illuminate\Support\Str;
use Support\Template\Input\Search;
use Bkwld\Library\Utils\File;
use Facilitador\Input\Sidebar;
use Support\Elements\Fields\Listing;
use Translation\Template\Localize;
use Facilitador\Input\Position;
use Facilitador\Input\NestedModels;
use Facilitador\Input\ModelValidator;
use Facilitador\Models\Base as BaseModel;
use Facilitador\Http\Controllers\Admin\Base as Controller;
use Facilitador\Exceptions\ValidationFail;
use Bkwld\Library\Laravel\Validator as BkwldLibraryValidator;

/**
 * The base controller is gives Decoy most of the magic/for-free mojo
 * It's not abstract because it can't be instantiated with PHPUnit like that
 */
class Base extends Controller
{
    
}
