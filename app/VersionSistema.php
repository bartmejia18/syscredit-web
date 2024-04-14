<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VersionSistema extends Model
{
    protected $table = 'version_sistema';
	protected $fillable = ['version'];
}
