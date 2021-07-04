<?php

class TailwindController extends Controller
{
    public function get_pirep($id)
    {
        $user = new User;
        $this->authenticate($user);
        $data = new stdClass;
        $data->user = $user;
        $data->pirep = Pirep::find($id, $user->hasPermission('pirepmanage') ? null : $user->data()->id);
        if ($data->pirep === FALSE) $this->notFound();
        $data->aircraft = $user->getAvailableAircraft();

        $this->render('pireps_pirep', $data);
    }
}
