<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Revo_Shine_Google_Service')) {
    class Revo_Shine_Google_Service
    {
        public function configureClient()
        {
            $client = new _PhpScoper01187d35592a\Google\Client();

            try {
                $fire_key = get_option('revo_shine_fire_key', null);

                if (!file_exists(REVO_SHINE_ABSPATH . "/storage/{$fire_key}.json")) {
                    throw new Exception('File not found');
                }

                $client->setAuthConfig(REVO_SHINE_ABSPATH . "/storage/{$fire_key}.json");
                $client->addScope(_PhpScoper01187d35592a\Google\Service\FirebaseCloudMessaging::CLOUD_PLATFORM);

                // retrieve the saved oauth token if it exists, you can save it on your database or in a secure place on your server
                $accessToken = get_option('revo_google_oauth_token', null);

                if (!is_null($accessToken)) {
                    // the token exists, set it to the client and check if it's still valid
                    $client->setAccessToken($accessToken);
                    if ($client->isAccessTokenExpired()) {
                        // the token is expired, generate a new token and set it to the client
                        $accessToken = $this->generateToken($client);
                        $client->setAccessToken($accessToken);
                    }
                } else {
                    // the token doesn't exist, generate a new token and set it to the client
                    $accessToken = $this->generateToken($client);
                    $client->setAccessToken($accessToken);
                }

                $accessToken = maybe_unserialize($accessToken);

                return $accessToken["access_token"];
                // the client is configured, now you can send the push notification using the $oauthToken.

            } catch (_PhpScoper01187d35592a\Google_Exception $e) {
                return $e->getMessage();
            }
        }

        private function generateToken($client)
        {
            $client->fetchAccessTokenWithAssertion();
            $accessToken = $client->getAccessToken();

            $tokenSerialize = serialize($accessToken);
            update_option('revo_google_oauth_token', $tokenSerialize);

            return $tokenSerialize;
        }
    }
}
