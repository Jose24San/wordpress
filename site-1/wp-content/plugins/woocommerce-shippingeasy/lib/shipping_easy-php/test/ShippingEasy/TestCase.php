<?php

/**
 * Base class for ShippingEasy test cases, provides some utility methods for creating
 * objects.
 */
abstract class ShippingEasyTestCase extends UnitTestCase
{

  /**
   * Create a valid test customer.
   */
  protected static function createTestCustomer(array $attributes = array())
  {
    authorizeFromEnv();

    // return ShippingEasy_Customer::create(
    //   $attributes + array(
    //     'card' => array(
    //       'number'    => '4242424242424242',
    //       'exp_month' => 5,
    //       'exp_year'  => date('Y') + 3,
    //     ),
    //   ));
  }

  /**
   * Generate a random 8-character string. Useful for ensuring
   * multiple test suite runs don't conflict
   */
  protected static function randomString()
  {
    $chars = "abcdefghijklmnopqrstuvwxyz";
    $str = "";
    for ($i = 0; $i < 10; $i++) {
      $str .= $chars[rand(0, strlen($chars)-1)];
    }
    return $str;
  }

  /**
   * Verify that a plan with a given ID exists, or create a new one if it does
   * not.
   */
  protected static function retrieveOrCreatePlan($id)
  {
    authorizeFromEnv();

    try {
      $plan = ShippingEasy_Plan::retrieve($id);
    } catch (ShippingEasy_InvalidRequestError $exception) {
      $plan = ShippingEasy_Plan::create(
        array(
          'id'        => $id,
          'amount'    => 0,
          'currency'  => 'usd',
          'interval'  => 'month',
          'name'      => 'Gold Test Plan',
        ));
    }
  }

}
