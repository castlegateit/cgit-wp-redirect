<?php

declare(strict_types=1);

namespace Castlegate\Redirect;

use WP;

final class Plugin
{
    /**
     * Initial redirect definitions
     *
     * @var array
     */
    private array $redirects = [];

    /**
     * Request as parsed by WordPress
     *
     * @var string
     */
    private string $request = '';

    /**
     * Valid redirect definitions for this request
     *
     * @var array
     */
    private array $validRedirects = [];

    /**
     * Regular expression matches
     *
     * @var array
     */
    private array $matches = [];

    /**
     * Initialization
     *
     * @return void
     */
    public static function init(): void
    {
        $plugin = new static();

        add_action('parse_request', [$plugin, 'redirect']);
    }

    /**
     * Perform redirect
     *
     * @param WP $wp
     * @return void
     */
    public function redirect(WP $wp): void
    {
        $this->redirects = apply_filters('cgit_redirects', $this->redirects);
        $this->request = $wp->request;

        $this->sanitizeRedirectDefinitions();
        $this->validateRedirectDefinitions();
        $this->performRedirect();
    }

    /**
     * Sanitize redirect definitions
     *
     * @return void
     */
    private function sanitizeRedirectDefinitions(): void
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
     * @return bool
     */
    private function isValidDefinition(array $definition): bool
    {
        return is_array($definition) &&
            isset($definition['from']) &&
            isset($definition['to']);
    }

    /**
     * Sanitize redirect from definition
     *
     * @param int $key
     * @return void
     */
    private function sanitizeRedirectFrom(int $key): void
    {
        $this->sanitizeRedirectRequest($key, 'from');
    }

    /**
     * Sanitize redirect to definition
     *
     * @param int $key
     * @return void
     */
    private function sanitizeRedirectTo(int $key): void
    {
        $this->sanitizeRedirectRequest($key, 'to');
    }

    /**
     * Sanitize redirect request (from or to string)
     *
     * @param int $key
     * @param string $property
     * @return void
     */
    private function sanitizeRedirectRequest(int $key, string $property): void
    {
        $url = $this->redirects[$key][$property];

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
     * @param int $key
     * @return void
     */
    private function sanitizeRedirectType(int $key): void
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
    private function validateRedirectDefinitions(): void
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
    private function performRedirect(): void
    {
        if (!$this->validRedirects) {
            return;
        }

        $redirect = array_values($this->validRedirects)[0];
        $destination = $redirect['to'];

        // Regular expression match groups to emulate Apache's RewriteRule
        if ($redirect['type'] == 'regex' && $this->matches) {
            foreach ($this->matches as $key => $match) {
                if ($key === 0) {
                    continue;
                }

                str_replace(["\\$key", "\$$key"], $match, $destination);
            }
        }

        // Destination matches current URL? Do not redirect.
        if (static::isCurrentUrl($destination)) {
            return;
        }

        // Debug mode? Print destination instead of performing redirect.
        if (defined('CGIT_WP_REDIRECT_DEBUG') && CGIT_WP_REDIRECT_DEBUG) {
            wp_die("Redirect to $destination");
        }

        // Redirect to destination URL.
        wp_redirect(home_url('/') . $destination);

        exit;
    }

    /**
     * URL is current URL
     *
     * @param string $url
     * @return bool
     */
    private static function isCurrentUrl(string $url): bool
    {
        $defaults = ['path' => '', 'query' => ''];

        $src = array_merge($defaults, parse_url(trim($_SERVER['REQUEST_URI'], '/')));
        $dst = array_merge(parse_url(trim($url, '/')));

        if ($src['path'] !== $dst['path']) {
            return false;
        }

        parse_str($src['query'], $src_args);
        parse_str($dst['query'], $dst_args);

        ksort($src_args);
        ksort($dst_args);

        return $src_args === $dst_args;
    }
}
