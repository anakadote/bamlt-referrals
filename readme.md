# BAM Lead Tracker Service Class

Interface with the BAM Lead Tracker Customer Referrer web service.

Begin by installing this package through Composer. Edit your project's `composer.json` file to require `anakadote/bamlt-referrals`.

	"require": {
		"anakadote/bamlt-referrals": "dev-master"
	}

Next, update Composer from the Terminal:

    composer update


## Usage / Methods

getReferrerToken(string $uri, bool $is_client_uri = false)

- **$uri**  - (required) BAM Lead Tracker URI
- **$is_client_uri**  - true for a Client URI, false for a Store URI

        (new BAMLTReferrals)->getReferrerToken(BAMLT_URI);

    
**Other methods:**

    (new BAMLTReferrals)->submit($customer_info, $input, $referrer_token);
    (new BAMLTReferrals)->getReferrals($referral_token);
    (new BAMLTReferrals)->getReferralConversions($referral_token);
    (new BAMLTReferrals)->getReferralAppointmentConversions($referral_token);
    (new BAMLTReferrals)->getReferralTransactionConversions($referral_token);



### Laravel

To use with Laravel, add the service provider. Open `config/app.php` and add a new item to the providers array.

    Anakadote\BAMLTReferrals\BAMLTReferralsServiceProvider::class

This package is also accessible via a Laravel Facade so to use simply call its methods on the Facade "BAMLTReferrals":  

    BAMLT::send(env('BAMLT_URI'), env('BAMLT_URI_IS_CLIENT_URI'))
