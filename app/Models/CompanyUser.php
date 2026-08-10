<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyUser extends Model
{
    // Pivot model for the many-to-many company-user relation.
    protected $table = 'company_user';

    //
}
