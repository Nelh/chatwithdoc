<?php

namespace App\Services;

use DocuSign\eSign\Client\ApiClient;
use DocuSign\eSign\Configuration;
use DocuSign\Controllers\Auth\DocuSign;
use Throwable;

class DocusignService
{
    const TOKEN_REPLACEMENT_IN_SECONDS = 600; # 10 minutes
    protected static $expires_in;
    protected static $access_token;
    protected static $expiresInTimestamp;
    protected static $account;
    protected static ApiClient $apiClient;

    public function __construct()
    {
        $config = new Configuration();
        self::$apiClient = new ApiClient($config);
    }

    /**
     * Checker for the JWT token
     */
    public function checkToken()
    {
        if (session()->has('docusign.ds_access_token') || (time() + self::TOKEN_REPLACEMENT_IN_SECONDS) > (int) session()->get('docusign.ds_expiration')) {
            $this->login();
        }
    }


    /**
     * DocuSign login handler
     * @throws \DocuSign\eSign\Client\ApiException
     */
    public function login()
    {
        self::$access_token = $this->configureJwtAuthorizationFlowByKey();

        if(self::$access_token['error'] == true) {
            return redirect()->away(self::$access_token['url']);
        }


        self::$expiresInTimestamp = time() + self::$expires_in;
        if (is_null(self::$account)) {
            self::$account = self::$apiClient->getUserInfo(self::$access_token->getAccessToken());
        }

        $this->authCallback();

    }

    /**
     * Get JWT auth by RSA key
     */
    private function configureJwtAuthorizationFlowByKey()
    {
        self::$apiClient->getOAuth()->setOAuthBasePath(config('docusign.jwt_config.authorization_server'));
        $privateKey = file_get_contents(
            base_path(config('docusign.jwt_config.private_key_file')),
            true
        );

        $scope = 'signature';
        $jwt_scope = $scope;

        try {
            $response = self::$apiClient->requestJWTUserToken(
                config('docusign.jwt_config.ds_client_id'),
                config('docusign.jwt_config.ds_impersonated_user_id'),
                $privateKey,
                $jwt_scope
            );

            session()->put('docusign.code', true);

            return $response[0];

        }
        catch (Throwable $th) {
            if (strpos($th->getMessage(), "consent_required") !== false) {
                $authorizationURL = 'https://account-d.docusign.com/oauth/auth?response_type=code&'
                . http_build_query(
                    [
                        'scope' => $jwt_scope . "+impersonation",
                        'client_id' => config('docusign.jwt_config.ds_client_id'),
                        'redirect_uri' => 'http://localhost'
                    ]
                );

                session()->put('docusign.code', false);

                return ['error' => true, 'url' => $authorizationURL];
            }
        }
    }


    /**
     * DocuSign login handler
     * @param $redirectUrl
     */
    public function authCallback(): void
    {
        if (!self::$access_token) {
            if (session()->get('docusign.code') == true) {
                $this->login();

            } else {
                throw new \Exception('Invalid JWT state');
            }
        } else {
            try {
                session([
                    'docusign.ds_access_token' => self::$access_token->getAccessToken(),
                    'docusign.ds_expiration' => time() + (self::$access_token->getExpiresIn() * 60),
                    'docusign.ds_user_name' => self::$account[0]->getName(),
                    'docusign.ds_user_email' => self::$account[0]->getEmail(),
                ]);

                $account_info = self::$account[0]->getAccounts();
                $base_uri_suffix = '/restapi';

                session([
                    'docusign.ds_account_id' => $account_info[0]->getAccountId(),
                    'docusign.ds_account_name' => $account_info[0]->getAccountName(),
                    'docusign.ds_base_path' => $account_info[0]->getBaseUri() . $base_uri_suffix
                ]);

            } catch (\Exception $e) {
                throw new \Exception($e->getMessage());
            }
        }
    }
}
