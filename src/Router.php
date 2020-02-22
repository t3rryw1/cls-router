<?php
/**
 * Created by PhpStorm.
 * User: terry
 * Date: 2019-01-04
 * Time: 10:26
 */

namespace Laura\Lib\Request;


use Laura\Lib\Base\Log;
use Laura\Lib\Base\Utility;

class Router
{

    protected static $instance;
    private $blockList;
    /**
     * @var IRequestHandler
     */
    private $handler;
    /**
     * @var IRequestValidator
     */
    private $validator;
    /**
     * @var IAuthenticator
     */
    private $authenticator;
    /**
     * @var IRequestTransformer
     */
    private $requestTransformer;
    /**
     * @var IResponseFormatter
     */
    private $outputFormatter;

    /**
     * @return Router
     */
    public static function getInstance()
    {
        return self::$instance;
    }

    public static function init($prefix,
                                $blockList,
                                $authenticator,
                                $inputTransformer = ObjectifyTransformer::class,
                                $outputTransformer = DefaultResponseFormatter::class)
    {
        if (!self::$instance) {
            self::$instance = new Router($prefix, $blockList, $authenticator, $inputTransformer, $outputTransformer);
        }
    }

    /**
     * @var array
     */
    private $headers;

    /**@var array */
    protected $services;

    protected $prefix;

    /**
     * Router constructor.
     * @param string $prefix
     * @param array $blockList
     * @param $authenticator
     * @param $inputTransformer
     * @param $outputTransformer
     */
    public function __construct($prefix, $blockList, $authenticator, $inputTransformer, $outputTransformer)
    {
        $this->prefix = $prefix;
        $this->authenticator = objectify($authenticator);
        $this->requestTransformer = objectify($inputTransformer);
        $this->outputFormatter = objectify($outputTransformer);
        $this->blockList = $blockList;
        $this->services = array('GET' => array(), 'POST' => array(), 'PUT' => array(), 'DELETE' => array());
    }

    private function defaultConfig()
    {
        date_default_timezone_set("UTC");
        header('X-Frame-Options: DENY');
        header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 10));
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s \G\M\T', time()));
    }

    public function run()
    {
        $this->defaultConfig();
        $ip = Utility::getClientIp();

        if (isset($this->blockList[$ip]) && $this->blockList[$ip] == 1) {
            $this->errorOut(403, "403 Forbidden");
        }
        if (substr_count($_SERVER['REQUEST_URI'], '?') >= 2) {
            $this->errorOut(400, "Bad Request");
        }

        $headers = apache_request_headers();
        $this->headers = array_change_key_case($headers, CASE_UPPER);

        $uri = $_SERVER['REQUEST_URI'];
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $this->parseGetParams($uri);
        $uri = substr($uri, strlen($this->prefix)); // remove prefix from the uri.

        Utility::access($uri);
        session_start();

        $uris = explode('/', $uri);

        $version = @$this->headers['X-API-VERSION'] ?? null;

        $this->match($uris, $method, $version);

        $this->errorOut(404, "route_not_found");

    }

    function parseGetParams($uri)
    {
        $gets = explode('?', $uri);

        $getParams = explode('&', $gets[1]);
        foreach ($getParams as $getParam) {
            $pair = explode('=', $getParam);
            if (sizeof($pair) == 2) {
                $_GET[$pair[0]] = $pair[1];
            }
        }

        return $gets[0];
    }

    /**
     * @param $method
     * @param $path
     * @param $params
     */
    public function register($method, $path, ...$params)
    {
        $handlerParam = reset($params);

        $handler = objectify($handlerParam);

        if (!$handler) {
            die("wrong in register route!");
        }

        if ($handler instanceof IRequestProcessor) {
            $version = @next($params) ?? "DEFAULT";
            $this->services[$method][$path] = array_merge(
                @$this->services[$method][$path] ?? [],
                [$version => $handler, $handler]
            );
        } else if ($handler instanceof IRequestHandler) {
            $validatorParam = next($params);


            $validator = objectify($validatorParam);

            if (!$validator) {
                $version = $validatorParam;
                $validator = null;

            } else {
                $version = @next($params) ?? "DEFAULT";
            }
            $this->services[$method][$path] = array_merge(
                @$this->services[$method][$path] ?? [],
                [$version => array($handler, $validator)]);

        }

    }

    private function mergeRequest($params)
    {
        $input = Utility::getJsonRequestData();
        !empty($_POST) &&
        $params = array_merge($params, $_POST);

        !empty($input) &&
        $params = array_merge($params, $input);

        !empty($_GET) &&
        $params = array_merge($params, $_GET);
        return $params;
    }

    private function findMatch($method, $key, $requestVersion)
    {
        $matchArray = $this->services[$method][$key];
        krsort($matchArray);
        foreach ($matchArray as $version => $item) {
            if ($requestVersion >= $version) {
                $this->handler = $item[0];
                $this->validator = $item[1];
                return;
            }
        }

    }


    /**
     * @param array $uris
     * @param string $method
     * @param string $requestVersion
     */
    public function match($uris, $method, $requestVersion = "DEFAULT")
    {

        $this->validator = $this->handler = null;

        foreach ($this->services[$method] as $key => $val) {
            $keys = explode('/', $key);
            if (sizeof($uris) !== sizeof($keys))
                continue;
            $request = array();
            foreach ($uris as $ind => $elem) {
                if (strpos($keys[$ind], ':') !== false) {
                    $index = substr($keys[$ind], 1);
                    $request[$index] = $elem;
                } else if ($elem != $keys[$ind]) {
                    continue 2;
                }
            }

            $request = $this->mergeRequest($request);

            $this->findMatch($method, $key, $requestVersion);

            $this->handler->setAuthenticator($this->authenticator);
            if (isset($this->validator)) {
                $this->validator->setAuthenticator($this->authenticator);

                !$this->authenticator->beforeAuthenticate($request, $this->headers)
                && $this->errorOut(
                    $this->validator->getErrorCode() ?? $this->validator->getDefaultErrorCode(),
                    $this->validator->getErrorDescription() ?? $this->validator->getDefaultErrorDescription());

                $this->validator->shouldAuthenticate()
                && !$this->authenticator->isAuthenticated()
                && !$this->authenticator->authenticate(
                    $request,
                    $this->headers)
                && $this->errorOut(
                    $this->authenticator->getErrorCode(),
                    $this->authenticator->getErrorDescription());

                !$this->validator->validate(
                    $request,
                    $this->headers)
                && $this->errorOut(
                    $this->validator->getErrorCode() ?? $this->validator->getDefaultErrorCode(),
                    $this->validator->getErrorDescription() ?? $this->validator->getDefaultErrorDescription());
            }

            if ($this->handler instanceof IRequestObjectProcessable) {
                $request = $this->requestTransformer->transform(
                    $request,
                    $this->handler->getRequestClass());
            }
            //now do not transform at all
            $this->dataOut(
                $this->handler->execute($request, $this->headers));

        }
    }

    private function dataOut($data)
    {
        Utility::setResponseStatusCode(200);

        header('Content-type: application/json');

        $output = $this->outputFormatter->formatData(
            $data);

        Log::info($output);

        echo $output;

        exit;

    }


    /**
     * this method can be override to allow different error out format
     * @param $code
     * @param $description
     */
    private function errorOut($code, $description)
    {
        Utility::setResponseStatusCode($code);

        header('Content-type: application/json');

        $output = $this->outputFormatter->formatError($code,
            $description);

        Log::info($output);

        echo $output;
        exit;
    }

    public function getHeaders()
    {
        return $this->headers;

    }


}
