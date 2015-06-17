<?php

class ChargeBee_Invoice extends ChargeBee_Model
{

  protected $allowed = array('id', 'subscriptionId', 'recurring', 'status', 'startDate', 'endDate', 'amount',
'paidOn', 'nextRetry', 'subTotal', 'lineItems', 'discounts');



  # OPERATIONS
  #-----------

  public static function addCharge($id, $params, $env = null)
  {
    return ChargeBee_Request::send(ChargeBee_Request::POST, "/invoices/$id/add_charge", $params, $env);
  }

  public static function addAddonCharge($id, $params, $env = null)
  {
    return ChargeBee_Request::send(ChargeBee_Request::POST, "/invoices/$id/add_addon_charge", $params, $env);
  }

  public static function all($params = array(), $env = null)
  {
    return ChargeBee_Request::send(ChargeBee_Request::GET, "/invoices", $params, $env);
  }

  public static function invoicesForSubscription($id, $params = array(), $env = null)
  {
    return ChargeBee_Request::send(ChargeBee_Request::GET, "/subscriptions/$id/invoices", $params, $env);
  }

  public static function retrieve($id, $env = null)
  {
    return ChargeBee_Request::send(ChargeBee_Request::GET, "/invoices/$id", array(), $env);
  }

  public static function collect($id, $env = null)
  {
    return ChargeBee_Request::send(ChargeBee_Request::POST, "/invoices/$id/collect", array(), $env);
  }

  public static function charge($params, $env = null)
  {
    return ChargeBee_Request::send(ChargeBee_Request::POST, "/invoices/charge", $params, $env);
  }

  public static function chargeAddon($params, $env = null)
  {
    return ChargeBee_Request::send(ChargeBee_Request::POST, "/invoices/charge_addon", $params, $env);
  }

 }

?>