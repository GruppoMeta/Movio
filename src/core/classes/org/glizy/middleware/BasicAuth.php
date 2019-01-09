<?php
class org_glizy_middleware_BasicAuth implements org_glizy_middleware_IMiddleware
{
    protected $baPassword;
    protected $baUsername;
    protected $httpAuth;

    public function beforeProcess($pageId, $pageType)
    {
        $this->baUsername = __Config::get('BASICAUTH_USERNAME');
        $this->baPassword = __Config::get('BASICAUTH_PASSWORD');
        $this->httpAuth = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION']) ? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] : '');

        if (!$this->baUsername && !$this->baPassword) return;
        $this->basicAuth();
    }

    public function afterRender($content)
    {
    }

    private function basicAuth()
    {
        if ($_SERVER['DOCUMENT_ROOT'] != null) {

            $logged = false;
            if ($this->httpAuth && preg_match('/Basic\s+(.*)$/i', $this->httpAuth, $matches)) {

                list($name, $password) = explode(':', base64_decode($matches[1]));
                $_SERVER['PHP_AUTH_USER'] = strip_tags($name);
                $_SERVER['PHP_AUTH_PW'] = strip_tags($password);
            }

            if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {

                if ($_SERVER['PHP_AUTH_USER'] == $this->baUsername && $_SERVER['PHP_AUTH_PW'] == $this->baPassword) {

                    $logged = true;
                }
            }

            if (!$logged) {
                header('WWW-Authenticate: Basic realm="'.__Config::get('APP_NAME').'"');
                header('HTTP/1.0 401 Unauthorized');
                echo "Login failed!\n";
                exit;
            }
        }
    }
}
