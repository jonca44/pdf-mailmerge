<?php

class ChargeBee_Addon extends ChargeBee_Model
{

  protected $allowed = array('id', 'name', 'invoiceName', 'type', 'chargeType', 'price', 'unit', 'status',
'archivedAt');



  # OPERATIONS
  #-----------

  public static function all($params = array(), $env = null)
  {
    return ChargeBee_Request::send(ChargeBee_Request::GET, "/addons", $params, $env);
  }

  public static function retrieve($id, $env = null)
  {
    return ChargeBee_Request::send(ChargeBee_Request::GET, "/addons/$id", array(), $env);
  }

 }

?>