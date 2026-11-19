<?php
namespace App\Http\Controllers;

use App\Entities\PaketTravel_Entity;

class MasterData_Kontrol extends Controller
{
    public function dashboard(PaketTravel_Entity $entity)
    {
        return view('admin.dashboard', $entity->getAllDataMaster());
    }
}
