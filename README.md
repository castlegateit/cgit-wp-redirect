# Castlegate IT WP Redirect

Basic URL redirects for WordPress themes and plugins. You can add redirects with the `cgit_redirects` filter:

~~~ php
add_filter('cgit_redirects', function ($redirects) {
    return array_merge($redirects, [
        // no match type specified, will assume exact match
        [
            'from' => 'old/path',
            'to' => 'new/path',
        ],

        // exact match
        [
            'from' => 'old/path',
            'to' => 'new/path',
            'type' => 'exact',
        ],

        // wildcard match
        [
            'from' => 'old/path/*',
            'to' => 'new/path',
            'type' => 'wildcard',
        ],

        // regular expression match
        [
            'from' => 'old/path/(.*)',
            'to' => 'new/path/$1',
            'type' => 'regex',
        ],
    ]);
});
~~~

Note that domains and leading and trailing spaces will be stripped from `to` and `from` definitions, so this plugin is only suitable for redirects within a WordPress site and not redirects to other domains.

## License

Released under the [MIT License](https://opensource.org/licenses/MIT). See [LICENSE](LICENSE) for details.
