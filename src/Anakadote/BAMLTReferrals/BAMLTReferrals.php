<?php

namespace Anakadote\BAMLTReferrals;

use DateTime;
use DateInterval;

/**
 * Interface with the BAM LeadTracker Customer Referrer web service.
 *
 * @version  1.1.1
 * @author   Taylor Collins <hello@endif.io>
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
        
        if ($is_client_uri){
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
     * Submit a New Referral
     *
     * @param  array   $customer_info
     * @param  array   $input
     * @param  string  $referrer_token
     * @return bool
     */
    public function submit($customer_info, $input, $referrer_token)
    {
        if (! is_array($input)) return false;
        
        $allowed_inputs = [
            'first_name', 'last_name', 'email', 'phone', 'phone_ext', 'address', 'address_2', 'city', 'state', 'zip', 'interest', 'comments', 
            'lead_generator', 'delivery_source', 'media_type',
        ];
        
        // Allow a "name" input
        if (isset($input['name'])) {
            $name = explode(' ', preg_replace('/\s+/', ' ', (trim($input['name']))));
            $input['first_name'] = isset($name[0]) ? $name[0] : '';
            $input['last_name']  = isset($name[1]) ? $name[1] : '';
        }

        // Comments
        $input['comments'] = isset($input['comments']) ? $input['comments'] : '';
        
        // Include customer info
        if (! empty($customer_info['name'])) {
            $input['comments'] .= "\n\nReferral Customer: " . ($customer_info['name']) . (! empty($input['comments']) ? "\n" . $input['comments'] : '');
        }
        if (! empty($customer_info['first_name']) && ! empty($customer_info['last_name'])) {
            $input['comments'] .= "\n\nReferral Customer: " . ($customer_info['first_name'] . ' ' . $customer_info['last_name']) . (! empty($input['comments']) ? "\n" . $input['comments'] : '');
        }
        if (! empty($customer_info['account_number'])) {
            $input['comments'] .= "\n\nReferral Customer Account Number: " . $customer_info['account_number'] . (! empty($input['comments']) ? "\n" . $input['comments'] : '');
        }
    
        // The XML
        $xml = "<?xml version='1.0'?><root>";
        $xml .= "<source>" . $this->cleanForXML($_SERVER['HTTP_REFERER']) . "</source>";
        $xml .= "<delivery_source>Referral Tracker</delivery_source>";
        $xml .= "<referrer_token>" . $referrer_token . "</referrer_token>";
                
        // Loop through supplied data and take allowed values
        foreach ($input as $key => $value) {
            if (in_array($key, $allowed_inputs)) {
                $xml .= "<" . $key . ">" . $this->cleanForXML($value) . "</" . $key . ">";
            }
        }
        
        $xml .= "</root>";
                
        $ch = curl_init("https://bamleadtracker.com/track/");
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
                
                if (! isset($leads['lead'])) {
                    return [];
                }
                
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
    
    /**
     * Clean string for XML
     * 
     * @param  string  $string
     * @return string
     */
    private function cleanForXML($string)
    {
        $string = strip_tags($string);
        $string = htmlentities($string, ENT_QUOTES, "UTF-8");
        $string = preg_replace("/&#?[a-z0-9]{2,8};/i", "", $string);
        return $string;
    }
}
