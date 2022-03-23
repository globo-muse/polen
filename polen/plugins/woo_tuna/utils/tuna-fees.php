<?php

function get_amount_simple_fee($order_total, $fee)
{
    return $fee * $order_total;
}

function get_installment_amount($order_total, $fee, $number_of_parcels)
{
    $installment_amount = get_amount_simple_fee($order_total, $fee);

    $parcel_value = ($order_total + $installment_amount) / $number_of_parcels;
    $parcel_value_rounded = number_format($parcel_value, 2, '.', '');

    $installment_amount = $parcel_value_rounded * $number_of_parcels - $order_total;

    if ($fee > 0) {
      return ($installment_amount) > 0 ? $installment_amount : 0 ;
    }

    return $installment_amount;
}

function get_parcel_value_with_fee($order_total, $fee, $number_of_parcels)
{
    $new_order_total = $order_total + get_installment_amount($order_total, $fee, $number_of_parcels);
    $parcel_value = $new_order_total / $number_of_parcels;

    return $parcel_value;
}

function get_parcel_message($parcel_count, $parcel_value, $order_total, $fee)
{
    $parcel_description = ' (R$ ' . $order_total . ')';

    return $parcel_count . 'x R$ ' . $parcel_value . $parcel_description;
}

function get_installment_options($order_total, $fees, $installment_params)
{
    $max_parcels_number = $installment_params[0];
    $min_parcel_value = $installment_params[1];

    $installment_options = array();

    for ($parcel_count = 1; $parcel_count <= $max_parcels_number; $parcel_count++) {
        if ($fees[$parcel_count - 1] == 0) {
            $fee = 0;
            $parcel_value = $order_total / $parcel_count;
        } else {
            $fee = $fees[$parcel_count - 1] / 100;
            $parcel_value = get_parcel_value_with_fee($order_total, $fee, $parcel_count);
        }

        if ($parcel_value > $min_parcel_value) {
            $fee_formatted = number_format(- $fee * 100, 2, ',', '.');
            $order_total_formatted = number_format($parcel_value * $parcel_count, 2, ',', '.');
            $parcel_value_formatted = number_format($parcel_value, 2, ',', '.');
            $installment_options[$parcel_count - 1] = get_parcel_message($parcel_count, $parcel_value_formatted, $order_total_formatted, $fee_formatted );
        } else {
            break;
        }
    }

    return json_encode($installment_options);
}
