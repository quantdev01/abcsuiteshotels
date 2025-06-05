<?php
/**
 *  Please read the terms of the CLUF license attached to this module(cf "licences" folder).
 *
 * @author    Línea Gráfica E.C.E. S.L.
 * @copyright Lineagrafica.es - Línea Gráfica E.C.E. S.L. all rights reserved.
 * @license   https://www.lineagrafica.es/licenses/license_en.pdf
 *            https://www.lineagrafica.es/licenses/license_es.pdf
 *            https://www.lineagrafica.es/licenses/license_fr.pdf
 */
class PluglinClient
{
    public const SERVER = 'https://app.pluglin.com/api/';
    // public const SERVER = 'https://web/api/';

    public const URL_VALIDATE = self::SERVER.'validate';
    public const URL_ORGANIZATION = self::SERVER.'organization';
    public const URL_CONTENTS = self::SERVER.'contents';

    public const URL_LANGS = self::SERVER.'languages';
    public const URL_PAIRS = self::SERVER.'pairs';

    public const URL_PROJECTS = self::SERVER.'projects';

    public const URL_UPLOAD = self::SERVER.'upload';
    public const URL_DOWNLOAD = self::SERVER.'project';

    public const URL_SUPPORT = self::SERVER.'support';

    public const URL_LOG = self::SERVER.'log';

    public $last_request = '';
    public $last_response = '';
    public $last_response_raw = '';
    public $last_error = '';

    /** @return bool */
    public function hasErrors()
    {
        return !is_array($this->last_response) ||
            (isset($this->last_response['error']) &&
            '' != $this->last_response['error']);
    }

    public function __call($method, $data)
    {
        if ('validate' == $method) {
            $params = $this->validateValidate($data);

            return $this->callValidate($params);
        }

        if ('organization' == $method) {
            $params = $this->validateOrganization($data);

            return $this->callOrganization($params);
        }

        if ('langs' == $method) {
            $params = $this->validateLangs($data);

            return $this->callLangs($params);
        }

        if ('pairs' == $method) {
            $params = $this->validatePairs($data);

            return $this->callPairs($params);
        }

        if ('getProject' == $method) {
            $params = $this->validateGetProject($data);

            return $this->callGetProject($params);
        }

        if ('newProject' == $method) {
            $params = $this->validatePostProject($data);

            return $this->callPostProject($params);
        }

        if ('updProject' == $method) {
            $params = $this->validatePostProject($data);

            return $this->callPostProject($params);
        }

        if ('delProject' == $method) {
            $params = $this->validateDelProject($data);

            return $this->callDelProject($params);
        }

        if ('contents' == $method) {
            $params = $this->validateContents($data);

            return $this->callContents($params);
        }

        if ('postContent' == $method) {
            $params = $this->validatePostContent($data);

            return $this->callPostContent($params);
        }

        if ('support' == $method) {
            $params = $this->validateSupport($data);

            return $this->callSupport($params);
        }

        if ('upload' == $method) {
            $params = $this->validateUpload($data);

            return $this->callUpload($params);
        }

        if ('download' == $method) {
            $params = $this->validateDownload($data);

            return $this->callDownload($params);
        }

        if ('getDownload' == $method) {
            $params = $this->validateGetDownload($data);

            return $this->callGetDownload($params);
        }

        if ('log' == $method) {
            $validationResult = $this->validateLog($data);
            if (!$validationResult) {
                return null;
            }

            return $this->callLog($data);
        }
    }

    private function validateValidate($data)
    {
        $params = [];
        if (isset($data['token']) && '' != $data['token']) {
            $params['token'] = $data['token'];
        } else {
            return null;
        }

        return $params;
    }

    private function validateOrganization($data)
    {
        $params = [];
        if (isset($data['token']) && '' != $data['token']) {
            $params['token'] = $data['token'];
        } else {
            return null;
        }

        return $params;
    }

    private function validateLangs($data)
    {
        $params = [];
        if (isset($data['token']) && '' != $data['token']) {
            $params['token'] = $data['token'];
        } else {
            return null;
        }

        return $params;
    }

    private function validatePairs($data)
    {
        $params = [];
        if (isset($data['token']) && '' != $data['token'] && isset($data['source']) && '' != $data['source']) {
            $params['token'] = $data['token'];
            $params['source'] = $data['source'];
        } else {
            return null;
        }

        return $params;
    }

    private function validateGetProject($data)
    {
        $params = [];
        if (isset($data['token']) && '' != $data['token'] && isset($data['id']) && (int) $data['id'] > 0) {
            $params['token'] = $data['token'];
            $params['id'] = $data['id'];
        } else {
            return null;
        }

        return $params;
    }

    private function validatePostProject($data)
    {
        $params = [];
        if (isset($data['token']) && '' != $data['token'] &&
            isset($data['name']) && '' != $data['name'] &&
            isset($data['source']) && '' != $data['source'] &&
            isset($data['destinations']) && is_array($data['destinations']) && count($data['destinations']) > 0) {
            $params['token'] = $data['token'];
            if (isset($data['id']) && (int) $data['id']) {
                $params['id'] = $data['id'];
            }

            $params['name'] = substr($data['name'], 0, 240);
            $params['source'] = $data['source'];
            $params['autoSync'] = $data['autoSync'];
            $params['autoSyncWebhook'] = $data['autoSyncWebhook'];
            $params['integration'] = $data['integration'];
            $params['destinations'] = json_encode($data['destinations']);

            return $params;
        }

        return null;
    }

    private function validateDelProject($data)
    {
        $params = [];
        if (isset($data['token']) && '' != $data['token'] && isset($data['id']) && (int) $data['id'] > 0) {
            $params['token'] = $data['token'];
            $params['id'] = $data['id'];
        } else {
            return null;
        }

        return $params;
    }

    private function validateContents($data)
    {
        $params = [];
        if (isset($data['token']) && '' != $data['token']) {
            $params['token'] = $data['token'];
            $params['id'] = $data['id'];
        } else {
            return null;
        }

        return $params;
    }

    private function validatePostContent($data)
    {
        $params = [];
        if (isset($data['token']) && '' != $data['token'] &&
            isset($data['name']) && '' != $data['name'] &&
            isset($data['project']) && '' != $data['project']) {
            $params['token'] = $data['token'];
            $params['name'] = substr($data['name'], 0, 240);
            $params['project'] = $data['project'];
        } else {
            return null;
        }

        return $params;
    }

    private function validateSupport($data)
    {
        $params = [];
        if (isset($data['token']) && '' != $data['token'] &&
            isset($data['message']) && '' != $data['message']) {
            $params['token'] = $data['token'];
            $params['message'] = $data['message'];
        } else {
            return null;
        }

        return $params;
    }

    private function validateUpload($data)
    {
        $params = [];
        if (isset($data['token']) && '' != $data['token'] &&
            isset($data['project']) && '' != $data['project'] &&
            isset($data['url']) && '' != $data['url']) {
            $params['token'] = $data['token'];
            $params['project'] = $data['project'];
            $params['url'] = $data['url'];
        } else {
            return null;
        }

        return $params;
    }

    private function validateDownload($data)
    {
        $params = [];
        if (isset($data['token']) && '' != $data['token'] &&
            isset($data['project']) && '' != $data['project']) {
            $params['token'] = $data['token'];
            $params['project'] = $data['project'];
            $params['callback'] = $data['callback'];
            if (!empty($data['from_date'])) {
                $params['from_date'] = $data['from_date'];
            }
        } else {
            return null;
        }

        return $params;
    }

    private function validateGetDownload($data)
    {
        $params = [];
        if (isset($data['token']) && '' != $data['token'] &&
            isset($data['project']) && '' != $data['project'] &&
            isset($data['downloadId']) && '' != $data['downloadId']) {
            $params['token'] = $data['token'];
            $params['project'] = $data['project'];
            $params['downloadId'] = $data['downloadId'];
        } else {
            return null;
        }

        return $params;
    }

    private function validateLog($params): bool
    {
        if (!isset($params['token']) || '' == $params['token']) {
            return false;
        }

        if (!isset($params['level']) || !isset($params['message'])) {
            return false;
        }

        $level = $params['level'];
        if (!in_array(strtolower($level), ['info', 'error', 'debug'])) {
            return false;
        }

        return true;
    }

    private function callValidate($params)
    {
        $url = self::URL_VALIDATE;

        $data = [];

        $result = $this->remoteCall($url, 'GET', $data, $params['token']);

        return $result;
    }

    private function callOrganization($params)
    {
        $url = self::URL_ORGANIZATION;

        $data = [];
        $data['token'] = $params['token'];
        $result = $this->remoteCall($url, 'GET', $data);

        return $result;
    }

    private function callLangs($params)
    {
        $url = self::URL_LANGS;

        $data = [];

        $result = $this->remoteCall($url, 'GET', $data, $params['token']);

        return $result;
    }

    private function callPairs($params)
    {
        $url = self::URL_PAIRS;

        $data = [];

        $result = $this->remoteCall($url.'/'.$params['source'], 'GET', $data, $params['token']);

        return $result;
    }

    private function callGetProject($params)
    {
        $url = self::URL_PROJECTS;

        $data = [];

        $result = $this->remoteCall($url.'/'.$params['id'], 'GET', $data, $params['token']);

        return $result;
    }

    private function callPostProject($params)
    {
        $url = self::URL_PROJECTS;

        if (isset($params['id']) && (int) $params['id']) {
            $url .= '/'.$params['id'];
        }
        $result = $this->remoteCall($url, 'POST', $params, $params['token']);

        return $result;
    }

    private function callDelProject($params)
    {
        $url = self::URL_PROJECTS;

        $result = $this->remoteCall($url.'/'.$params['id'], 'DELETE', $params, $params['token']);

        return $result;
    }

    private function callContents($params)
    {
        $url = self::URL_CONTENTS;

        $data = [];
        $data['token'] = $params['token'];
        $data['id'] = $params['id'];

        $result = $this->remoteCall($url, 'GET', $data);

        return $result;
    }

    private function callPostContent($params)
    {
        $url = self::URL_CONTENTS;

        $data = [];
        $data['token'] = $params['token'];
        $data['name'] = substr($params['name'], 0, 240);
        $data['type'] = 'folder';
        $data['project'] = $params['project'];

        $result = $this->remoteCall($url, 'POST', $data, $params['token']);

        return $result;
    }

    private function callSupport($params)
    {
        $url = self::URL_SUPPORT;

        $data = [];
        $data['token'] = $params['token'];
        $data['message'] = $params['message'];

        $result = $this->remoteCall($url, 'POST', $data, $params['token']);

        return $result;
    }

    private function callUpload($params)
    {
        $url = self::URL_UPLOAD;

        $data = [];
        $data['token'] = $params['token'];
        $data['project'] = $params['project'];
        $data['url'] = $params['url'];

        return $this->remoteCall($url, 'POST', $data, $params['token']);
    }

    private function callDownload($params)
    {
        $url = self::URL_DOWNLOAD;

        $data = [];
        $data['token'] = $params['token'];
        $url .= '/'.$params['project'].'/download';
        $data['callback'] = $params['callback'];

        return $this->remoteCall($url, 'POST', $data, $params['token']);
    }

    private function callGetDownload($params)
    {
        $url = self::URL_DOWNLOAD;

        $data = [];
        $data['token'] = $params['token'];
        $url .= '/'.$params['project'].'/download/'.$params['downloadId'];

        $result = $this->remoteCall($url, 'GET', $data);

        return $result;
    }

    private function callLog($params)
    {
        $data = [];
        $data['level'] = $params['level'];
        $data['message'] = $params['message'];
        $data['data'] = (isset($params['data']) ? json_encode($params['data']) : '');

        $result = $this->remoteCall(self::URL_LOG, 'POST', $data, $params['token']);

        return $result;
    }

    /* GENERAL */

    private function remoteCall($url, $method = 'GET', $data = [], $token_header = null)
    {
        if ('GET' == $method && count($data) > 0) {
            $url .= '?'.http_build_query($data);
        }

        try {
            $ch = curl_init();

            if ($_ENV['IS_DOCKER']) {
                // remove ssl checks
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            }

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_TIMEOUT_MS, 2000000);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

            if ($token_header) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'token: '.$token_header,
                ]);
            }
            if (count($data)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            }
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $content = curl_exec($ch);
            $errorMessage = '';
            $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if (curl_errno($ch)) {
                $errorMessage = curl_error($ch);
            }

            curl_close($ch);
        } catch (Exception $e) {
            die();
        }

        if (isset($errorMessage)) {
            // TODO - Handle cURL error accordingly
            //echo $errorMessage;
        }

        $this->last_request = ['url' => $url, 'method' => $method, 'data' => $data];
        $this->last_response_raw = $content;
        $this->last_error = '';

        $response = [];
        $response['code'] = $responseCode;

        $data = json_decode($content, true);

        if (is_array($data)) {
            $this->last_response = $data;
            $response['message'] = $content;
        } else {
            try {
                libxml_use_internal_errors(true);
                $xml = simplexml_load_string($content);

                if (false === $xml) {
                    $errors = '';

                    foreach (libxml_get_errors() as $error) {
                        $errors .= $error->message.PHP_EOL;
                    }

                    throw new Exception($errors);
                }

                $json = json_encode($xml);
                $this->last_response = json_decode($json, true);
                if ('' != $this->last_response) {
                    $response['message'] = $this->last_response;
                } else {
                    $response['message'] = $content;
                }
            } catch (Exception $e) {
                $this->last_error = $e->getMessage();
                $response['message'] = $content;
            }
        }

        return $response;
    }
}
