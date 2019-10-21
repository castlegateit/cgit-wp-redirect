<?php

namespace Cgit\Redirect;

class Plugin
{
    /**
     * Initial redirect definitions
     *
     * @var array
     */
    private $redirects = [];

    /**
     * Request as parsed by WordPress
     *
     * @var string
     */
    private $request = '';

    /**
     * Valid redirect definitions for this request
     *
     * @var array
     */
    private $validRedirects = [];

    /**
     * Regular expression matches
     *
     * @var array
     */
    private $matches = [];

    /**
     * Construct
     *
     * @return void
     */
    public function __construct()
    {
        add_action('parse_request', [$this, 'redirect']);
    }

    /**
     * Perform redirect
     *
     * @param WP $wp
     * @return void
     */
    public function redirect($wp)
    {
        $this->redirects = apply_filters('cgit_redirects', $this->redirects);
        $this->request = $wp->request;

        $this->sanitizeRedirectDefinitions();
        $this->validateRedirectDefinitions();
        $this->performRedirect();

        // FIXME redirect if match???
    }

    /**
     * Sanitize redirect definitions
     *
     * @return void
     */
    private function sanitizeRedirectDefinitions()
    {
        if (!$this->redirects) {
            return;
        }

        foreach ($this->redirects as $key => $redirect) {
            if (!$this->isValidDefinition($redirect)) {
                unset($this->redirects[$key]);

                continue;
            }

            $this->sanitizeRedirectType($key);
            $this->sanitizeRedirectFrom($key);
            $this->sanitizeRedirectTo($key);
        }
    }

    /**
     * Is this a valid redirect definition?
     *
     * @param array $definition
     * @return boolean
     */
    private function isValidDefinition($definition)
    {
        return is_array($definition) &&
            isset($definition['from']) &&
            isset($definition['to']));
    }

    /**
     * Sanitize redirect from definition
     *
     * @param integer $key
     * @return void
     */
    private function sanitizeRedirectFrom($key)
    {
        $this->sanitizeRedirectRequest($key, 'from');
    }

    /**
     * Sanitize redirect to definition
     *
     * @param integer $key
     * @return void
     */
    private function sanitizeRedirectTo($key)
    {
        $this->sanitizeRedirectRequest($key, 'to');
    }

    /**
     * Sanitize redirect request (from or to string)
     *
     * @param integer $key
     * @param string $property
     * @return void
     */
    private function sanitizeRedirectRequest($key, $property)
    {
        $url = $this->request[$key][$property];

        // Unescape regular expression slashes
        if ($this->redirects[$key]['type'] == 'regex') {
            $url = str_replace('\/', '/', $url);
        }

        // Parse URL
        $components = parse_url($url);

        // Remove scheme and domain name
        if (isset($components['host'])) {
            $prefix = '//' . $components['host'];

            if (isset($components['scheme'])) {
                $prefix = $components['scheme'] . ':' . $prefix;
            }

            if (strpos($url, $prefix) === 0) {
                $url = substr($url, strlen($prefix));
            }
        }

        // Remove leading and trailing slashes to match WP request format
        $url = trim($url, '/');

        // Escape regular expression slashes
        if ($this->redirects[$key]['type'] == 'regex') {
            $url = str_replace('/', '\/', $url);
        }

        // Update property
        $this->redirects[$key][$property] = $url;
    }

    /**
     * Sanitize redirect type
     *
     * @param integer $key
     * @return void
     */
    private function sanitizeRedirectType($key)
    {
        if (isset($this->redirects[$key]['type'])) {
            return;
        }

        $this->redirects[$key]['type'] = 'exact';
    }

    /**
     * Identify valid redirects for this request
     *
     * @return void
     */
    private function validateRedirectDefinitions()
    {
        $this->validRedirects = array_filter($this->redirects, function ($redirect) {
            if ($redirect['type'] == 'exact') {
                return $this->request == $redirect['from'];
            }

            if ($redirect['type'] == 'wildcard') {
                return fnmatch($redirect['from'], $this->request);
            }

            if ($redirect['type'] == 'regex') {
                return preg_match("/{$redirect['from']}/", $this->request, $this->matches);
            }

            return false;
        });
    }

    /**
     * Perform valid redirect
     *
     * @return void
     */
    private function performRedirect()
    {
        if (!$this->validRedirects) {
            return;
        }

        $redirect = array_values($this->validRedirects)[0];
        $destination = $redirect['to'];

        // Regular expression match groups to emulate Apache's RewriteRule
        if ($redirect['type'] == 'regex' && $this->matches) {
            foreach ($matches as $key => $match) {
                if ($key === 0) {
                    continue;
                }

                str_replace(["\\$key", "\$$key", $match, $destination);
            }
        }

        wp_redirect(home_url('/') . $destination);

        exit;
    }
}
