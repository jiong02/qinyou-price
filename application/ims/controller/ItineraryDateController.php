<?php

namespace app\ims\controller;

use app\ims\model\ItineraryDateModel;

class ItineraryDateController extends PrivilegeController
{
    public function BatchAddData($data)
    {
        return ItineraryDateModel::saveAll($data);
    }
}
