<?php

namespace Anakadote\BAMLTReferrals;

use DateTime;
use DateInterval;

/**
 * Interface with the BAM Lead Tracker Customer Referrer web service.
 *
 * @version  1.0.0
 * @author   Taylor Collins <taylor@tcdihq.com>
 */
class BAMLTReferrals
{
    /**
     * Request a new Customer Referrer Token from BAMLT
     *
     * @param  string  $uri  BAMLT URI
     * @param  bool    $is_client_uri  true for a Client URI, false for a Store URI
     * @return string|bool
     */
    public function getReferrerToken($uri, $is_client_uri = false)
    {
        $xml = "<?xml version='1.0'?><root>";
        
        if($is_client_uri){
            $xml .= "<uri_client>" . $uri . "</uri_client>";
        } else {
            $xml .= "<uri>" . $uri . "</uri>";
        }
        
        $xml .= "</root>";
        
        $ch = curl_init("https://bamleadtracker.com/referrals/get-referral-token/");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, ['xml' => $xml]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);
        
        $xml = simplexml_load_string($output);
        if ($xml !== false) {
            if ($xml->response->code == 1) {
                return $xml->response->token;
            }
        }
        return false;
    }

    /**
     * Get all BAMLT referrals associated with a customer referrer token
     *
     * @param  string  $referrer_token
     * @return array|bool
     */
    public function getReferrals($referrer_token)
    {
        $xml  = "<?xml version='1.0'?><root>";  
        $xml .= "<token>" . $referrer_token . "</token>";
        $xml .= "</root>";

        $ch = curl_init("https://bamleadtracker.com/referrals/get-referrals/");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, ['xml' => $xml]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);
                
        $xml = simplexml_load_string(str_replace('&', 'and', $output));
            
        if ($xml !== false) {
            if ($xml->response->code == 1) {
                $leads = (array) $xml->response->leads;
                            
                if (is_array($leads['lead'])) {
                    return $leads['lead'];
                }
                return array($leads['lead']);
            }
        }
        return false;
    }

    /**
     * Get all BAMLT referrals associated with a customer referrer token
     * that converted *yesterday*.
     *
     * @param  string  $referrer_token
     * @return array|bool
     */
    public function getReferralConversions($referrer_token)
    {
        $yesterday = new DateTime();
        $yesterday->add(DateInterval::createFromDateString('yesterday'));
        
        $xml  = "<?xml version='1.0'?><root>";  
        $xml .= "<date>" . $yesterday->format('Y-m-d') . "</date>";
        $xml .= "<token>" . $referrer_token . "</token>";
        $xml .= "</root>";

        $ch = curl_init("https://bamleadtracker.com/referrals/get-conversions/");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, ['xml' => $xml]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);
            
        $xml = simplexml_load_string($output);
        if ($xml !== false) {
                            
            if ($xml->response->code == 1) {
                $appointments = (array) $xml->response->appointments;
                $transactions = (array) $xml->response->transactions;
                
                if (is_array($appointments['lead'])) {
                    $appointments = $appointments['lead'];
                }
                
                if (is_array($transactions['lead'])) {
                    $transactions = $transactions['lead'];
                }
                
                return [
                    'appointments' => $appointments,
                    'transactions' => $transactions,
                ];
            }
        }
        return false;
    }

    /**
     * Get all BAMLT referrals associated with a customer
     * that converted to an *Appointment* *yesterday*.
     *
     * @param  string  $referrer_token
     * @return array|bool
     */
    public function getReferralAppointmentConversions($referrer_token)
    {
        $yesterday = new DateTime();
        $yesterday->add(DateInterval::createFromDateString('yesterday'));
        
        $xml  = "<?xml version='1.0'?><root>";
        $xml .= "<date>" . $yesterday->format('Y-m-d') . "</date>";
        $xml .= "<token>" . $referrer_token . "</token>";
        $xml .= "</root>";

        $ch = curl_init("https://bamleadtracker.com/referrals/get-converted-appointments/");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, ['xml' => $xml]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);
            
        $xml = simplexml_load_string($output);
        if ($xml !== false) {
                    
            if ($xml->response->code == 1) {
                $leads = (array) $xml->response->leads;
                
                if (is_array($leads['lead'])) {
                    return $leads['lead'];
                }
                return [$leads['lead']];
            }
        }
        return false;
    }

    /**
     * Get all BAMLT referrals associated with a customer
     * that converted to a *Transaction* *yesterday*.
     *
     * @param  string  $referrer_token
     * @return array|bool
     */
    public function getReferralTransactionConversions($referrer_token)
    {
        $yesterday = new DateTime();
        $yesterday->add(DateInterval::createFromDateString('yesterday'));
        
        $xml  = "<?xml version='1.0'?><root>";
        $xml .= "<date>" . $yesterday->format('Y-m-d') . "</date>";
        $xml .= "<token>" . $referrer_token . "</token>";
        $xml .= "</root>";

        $ch = curl_init("https://bamleadtracker.com/referrals/get-converted-transactions/");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, ['xml' => $xml]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);
            
        $xml = simplexml_load_string($output);
        if ($xml !== false) {
                    
            if ($xml->response->code == 1) {
                $leads = (array) $xml->response->leads;
                
                if (is_array($leads['lead'])) {
                    return $leads['lead'];
                }
                return [$leads['lead']];
            }
        }
        return false;
    }
}
