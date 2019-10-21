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

Copyright (c) 2019 Castlegate IT. All rights reserved.

This program is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License along with this program. If not, see <https://www.gnu.org/licenses/>.
