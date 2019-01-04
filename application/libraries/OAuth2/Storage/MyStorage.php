<?php
/**
 * Created by PhpStorm.
 * User: vsergiu
 * Date: 12/4/17
 * Time: 4:09 PM
 */

require_once (__DIR__."/../../../third_party/OAuth2-Server/src/OAuth2/Autoloader.php");
OAuth2\Autoloader::register();


/**
 * Class MyStorage
 * @property CI_DB_query_builder db
 */

class MyStorage implements
    \OAuth2\Storage\AccessTokenInterface,
    \OAuth2\Storage\UserCredentialsInterface,
    \OAuth2\Storage\ClientCredentialsInterface,
    \OAuth2\Storage\RefreshTokenInterface
{

    private $db;
    private $tables;

    function __construct ($paras)
    {
        $this->db = $paras["db"];
        $this->tables = $paras["tables"];
    }

    static function init(CI_DB_query_builder $dbDriver,$tables){
        return new MyStorage([
            "db"=>$dbDriver,
            "tables"=>$tables
        ]);
    }

    /**
     * Look up the supplied oauth_token from storage.
     *
     * We need to retrieve access token data as we create and verify tokens.
     *
     * @param string $oauth_token - oauth_token to be check with.
     *
     * @return array|null - An associative array as below, and return NULL if the supplied oauth_token is invalid:
     * @code
     *     array(
     *         'expires'   => $expires,   // Stored expiration in unix timestamp.
     *         'client_id' => $client_id, // (optional) Stored client identifier.
     *         'user_id'   => $user_id,   // (optional) Stored user identifier.
     *         'scope'     => $scope,     // (optional) Stored scope values in space-separated string.
     *         'id_token'  => $id_token   // (optional) Stored id_token (if "use_openid_connect" is true).
     *     );
     * @endcode
     *
     * @ingroup oauth2_section_7
     */
    public function getAccessToken ($oauth_token)
    {
        $q = $this->db->get_where($this->tables["access_tokens"],["access_token"=>$oauth_token]);
        if($q->num_rows())
            return $q->row_array();
        return null;
    }

    /**
     * Store the supplied access token values to storage.
     *
     * We need to store access token data as we create and verify tokens.
     *
     * @param string $oauth_token - oauth_token to be stored.
     * @param mixed $client_id - client identifier to be stored.
     * @param mixed $user_id - user identifier to be stored.
     * @param int $expires - expiration to be stored as a Unix timestamp.
     * @param string $scope - OPTIONAL Scopes to be stored in space-separated string.
     *
     * @ingroup oauth2_section_4
     */
    public function setAccessToken ($oauth_token, $client_id, $user_id, $expires, $scope = null)
    {
        $tokenData = [
            "access_token"         => $oauth_token,
            "client_id"     => $client_id,
            "user_id"       => $user_id,
            "expires"       => $expires,
            "scope"         => $scope
        ];
        $this->db->insert($this->tables["access_tokens"],$tokenData);
    }

    /**
     * Grant access tokens for basic user credentials.
     *
     * Check the supplied username and password for validity.
     *
     * You can also use the $client_id param to do any checks required based
     * on a client, if you need that.
     *
     * Required for OAuth2::GRANT_TYPE_USER_CREDENTIALS.
     *
     * @param $username
     * Username to be check with.
     * @param $password
     * Password to be check with.
     *
     * @return boolean
     * TRUE if the username and password are valid, and FALSE if it isn't.
     * Moreover, if the username and password are valid, and you want to
     *
     * @see http://tools.ietf.org/html/rfc6749#section-4.3
     *
     * @ingroup oauth2_section_4
     */
    public function checkUserCredentials ($username, $password)
    {
        $q = $this->db->get_where($this->tables["users"],["username"=>$username,"active"=>1]);

        if($q->num_rows()!==1)
            return false;
        $user = $q->row();
        return password_verify($password,$user->passwordhash);
    }

    /**
     * @param string $username - username to get details for
     * @return array|false     - the associated "user_id" and optional "scope" values
     *                           This function MUST return FALSE if the requested user does not exist or is
     *                           invalid. "scope" is a space-separated list of restricted scopes.
     * @code
     *     return array(
     *         "user_id"  => USER_ID,    // REQUIRED user_id to be stored with the authorization code or access token
     *         "scope"    => SCOPE       // OPTIONAL space-separated list of restricted scopes
     *     );
     * @endcode
     */
    public function getUserDetails ($username)
    {
        $q = $this->db->get_where($this->tables["users"],["username"=>$username,"active"=>1]);
        if($q->num_rows()!=1)
            return false;
        $user = $q->row();

        return [
            "user_id"   => $user->username
        ];
    }

    /**
     * Make sure that the client credentials is valid.
     *
     * @param $client_id
     * Client identifier to be check with.
     * @param $client_secret
     * (optional) If a secret is required, check that they've given the right one.
     *
     * @return bool
     * TRUE if the client credentials are valid, and MUST return FALSE if it isn't.
     * @endcode
     *
     * @see http://tools.ietf.org/html/rfc6749#section-3.1
     *
     * @ingroup oauth2_section_3
     */
    public function checkClientCredentials ($client_id, $client_secret = null)
    {
        //log_message("debug",json_encode(["client_id"=>$client_id,"client_secret"=>$client_secret]));
        $q = $this->db->get_where($this->tables["clients"],["name"=>$client_id,"secret"=>$client_secret]);
        //log_message("debug","Clients: ".$q->num_rows());
        return $q->num_rows()==1;
    }

    /**
     * Determine if the client is a "public" client, and therefore
     * does not require passing credentials for certain grant types
     *
     * @param $client_id
     * Client identifier to be check with.
     *
     * @return boolean TRUE if the client is public, and FALSE if it isn't.
     * @endcode
     *
     * @see http://tools.ietf.org/html/rfc6749#section-2.3
     * @see https://github.com/bshaffer/oauth2-server-php/issues/257
     *
     * @ingroup oauth2_section_2
     */
    public function isPublicClient ($client_id)
    {
        return true;
    }

    /**
     * Get client details corresponding client_id.
     *
     * OAuth says we should store request URIs for each registered client.
     * Implement this function to grab the stored URI for a given client id.
     *
     * @param $client_id
     * Client identifier to be check with.
     *
     * @return array|boolean
     *               Client details. The only mandatory key in the array is "redirect_uri".
     *               This function MUST return FALSE if the given client does not exist or is
     *               invalid. "redirect_uri" can be space-delimited to allow for multiple valid uris.
     *               <code>
     *               return array(
     *                  "redirect_uri" => REDIRECT_URI,      // REQUIRED redirect_uri registered for the client
     *                  "client_id"    => CLIENT_ID,         // OPTIONAL the client id
     *                  "grant_types"  => GRANT_TYPES,       // OPTIONAL an array of restricted grant types
     *                  "user_id"      => USER_ID,           // OPTIONAL the user identifier associated with this client
     *                  "scope"        => SCOPE,             // OPTIONAL the scopes allowed for this client
     *               );
     *               </code>
     *
     * @ingroup oauth2_section_4
     */
    public function getClientDetails ($client_id)
    {
        $q = $this->db->get_where($this->tables["clients"],["name"=>$client_id]);
        if($q->num_rows()!=1)
            return false;
        $client = $q->row_array();
        $client["client_id"] = $client["name"];
        unset($client["client_secret"]);
        return $client;
    }

    /**
     * Get the scope associated with this client
     *
     * @param string $client_id
     * @return string|bool
     * STRING the space-delineated scope list for the specified client_id* STRING the space-delineated scope list for the specified client_id
     */
    public function getClientScope ($client_id)
    {

        $q = $this->db->get_where($this->tables["clients"],["name"=>$client_id]);
        if($q->num_rows()!=1)
            return false;
        $client = $q->row();
        return $client->scope;
    }

    /**
     * Check restricted grant types of corresponding client identifier.
     *
     * If you want to restrict clients to certain grant types, override this
     * function.
     *
     * @param $client_id
     * Client identifier to be check with.
     * @param $grant_type
     * Grant type to be check with
     *
     * @return boolean
     * TRUE if the grant type is supported by this client identifier, and
     * FALSE if it isn't.
     *
     * @ingroup oauth2_section_4
     */
    public function checkRestrictedGrantType ($client_id, $grant_type)
    {
        return true;
    }

    /**
     * Grant refresh access tokens.
     *
     * Retrieve the stored data for the given refresh token.
     *
     * Required for OAuth2::GRANT_TYPE_REFRESH_TOKEN.
     *
     * @param $refresh_token
     * Refresh token to be check with.
     *
     * @return array
     * An associative array as below, and NULL if the refresh_token is
     * invalid:
     * - refresh_token: Refresh token identifier.
     * - client_id: Client identifier.
     * - user_id: User identifier.
     * - expires: Expiration unix timestamp, or 0 if the token doesn't expire.
     * - scope: (optional) Scope values in space-separated string.
     *
     * @see http://tools.ietf.org/html/rfc6749#section-6
     *
     * @ingroup oauth2_section_6
     */
    public function getRefreshToken ($refresh_token)
    {

        $q = $this->db->get_where($this->tables["refresh_tokens"],["refresh_token"=>$refresh_token]);
        if($q->num_rows()!=1)
            return null;
        $token = $q->row_array();
        return $token;
    }

    /**
     * Take the provided refresh token values and store them somewhere.
     *
     * This function should be the storage counterpart to getRefreshToken().
     *
     * If storage fails for some reason, we're not currently checking for
     * any sort of success/failure, so you should bail out of the script
     * and provide a descriptive fail message.
     *
     * Required for OAuth2::GRANT_TYPE_REFRESH_TOKEN.
     *
     * @param $refresh_token
     * Refresh token to be stored.
     * @param $client_id
     * Client identifier to be stored.
     * @param $user_id
     * User identifier to be stored.
     * @param $expires
     * Expiration timestamp to be stored. 0 if the token doesn't expire.
     * @param $scope
     * (optional) Scopes to be stored in space-separated string.
     *
     * @ingroup oauth2_section_6
     */
    public function setRefreshToken ($refresh_token, $client_id, $user_id, $expires, $scope = null)
    {
        $tokenData = [
            "refresh_token"  => $refresh_token,
            "client_id"     => $client_id,
            "user_id"       => $user_id,
            "expires"       => $expires,
            "scope"         => $scope
        ];
        $this->db->insert($this->tables["refresh_tokens"],$tokenData);
    }


    /**
     * Expire a used refresh token.
     *
     * This is not explicitly required in the spec, but is almost implied.
     * After granting a new refresh token, the old one is no longer useful and
     * so should be forcibly expired in the data store so it can't be used again.
     *
     * If storage fails for some reason, we're not currently checking for
     * any sort of success/failure, so you should bail out of the script
     * and provide a descriptive fail message.
     *
     * @param $refresh_token
     * Refresh token to be expired.
     *
     * @ingroup oauth2_section_6
     */
    public function unsetRefreshToken ($refresh_token)
    {
        $this->db->delete($this->tables["refresh_tokens"], ["refresh_token"=>$refresh_token]);
    }

    /**
     * Expire an access token.
     *
     * This is not explicitly required in the spec, but if defined in a draft RFC for token
     * revoking (RFC 7009) https://tools.ietf.org/html/rfc7009
     *
     * @param $access_token
     * Access token to be expired.
     *
     * @return BOOL true if an access token was unset, false if not
     * @ingroup oauth2_section_6
     *
     * @todo v2.0 include this method in interface. Omitted to maintain BC in v1.x
     */
    public function unsetAccessToken ($access_token)
    {
        // TODO: Implement unsetAccessToken() method.
        $this->db->delete($this->tables["access_tokens"], ["access_token"=>$access_token]);
        return true;
    }
}