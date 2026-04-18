<?php

namespace Modules\AdminManagement\Repository\User;

use Modules\AdminManagement\Http\Requests\DoctorRequest;
use Modules\AdminManagement\Http\Requests\UpdateDoctorRequest;
use Modules\AdminManagement\Http\Requests\UpdateStatusAminRequest;

interface DoctorInterface
{
    public function index();

    public function store(DoctorRequest $request);

    public function update(UpdateDoctorRequest $request);

    public function activateDeActivate(UpdateStatusAminRequest $request);
}
