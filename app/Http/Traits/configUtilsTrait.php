<?php

namespace App\Http\Traits;

use App\Creditos;
use App\VersionSistema;

trait configUtilsTrait {
    public function getVersionSystem() {
        return VersionSistema::first()->version;
    }
}
